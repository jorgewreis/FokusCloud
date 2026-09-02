<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VoucherManager
{
    public const RESERVATION_MINUTES = 30;

    public function expireReservations(): int
    {
        return DB::table('voucher_redemption_reservations')
            ->where('status', 'pending')
            ->where('expires_at', '<=', now())
            ->update([
                'status' => 'expired',
                'released_at' => now(),
                'updated_at' => now(),
            ]);
    }

    public function findEligible(string $code, string $productId, string $companyId, array $moduleCodes = [], ?string $planCode = null): object
    {
        $this->expireReservations();

        $voucher = DB::table('vouchers')->where('code', strtoupper(trim($code)))->where('status', 'ativa')->first();
        abort_unless($voucher, 422, 'Voucher inválido ou indisponível.');
        abort_if(($voucher->starts_at && now()->lt($voucher->starts_at)) || ($voucher->ends_at && now()->gt($voucher->ends_at)), 422, 'Voucher fora do período de validade.');
        abort_if($voucher->product_id && $voucher->product_id !== $productId, 422, 'Voucher não é elegível para este produto.');

        if ($voucher->plan_id) {
            $planMatches = DB::table('plans')->where('id', $voucher->plan_id)->where('product_id', $productId)->where('code', $planCode)->exists();
            abort_unless($planMatches, 422, 'Voucher não é elegível para este plano.');
        }

        $eligibleModules = $voucher->module_codes ? json_decode($voucher->module_codes, true) : [];
        abort_if($eligibleModules && ! array_intersect($eligibleModules, $moduleCodes), 422, 'Voucher não é elegível para os módulos selecionados.');

        $total = DB::table('voucher_redemptions')->where('voucher_id', $voucher->id)->count()
            + DB::table('voucher_redemption_reservations')->where('voucher_id', $voucher->id)->where('status', 'pending')->count();
        $companyTotal = DB::table('voucher_redemptions')->where('voucher_id', $voucher->id)->where('company_id', $companyId)->count()
            + DB::table('voucher_redemption_reservations')->where('voucher_id', $voucher->id)->where('company_id', $companyId)->where('status', 'pending')->count();
        abort_if($voucher->redemption_limit && $total >= $voucher->redemption_limit, 422, 'Voucher atingiu o limite de uso.');
        abort_if($voucher->redemption_limit_per_company && $companyTotal >= $voucher->redemption_limit_per_company, 422, 'Voucher já atingiu o limite para esta empresa.');

        return $voucher;
    }

    public function discount(object $voucher, float $amount): float
    {
        return match ($voucher->discount_type) {
            'trial_free', 'commercial_credit' => round(min($amount, (float) ($voucher->discount_type === 'trial_free' ? $amount : $voucher->discount_value)), 2),
            'percentage' => round(min($amount, $amount * ((float) $voucher->discount_value / 100)), 2),
            default => round(min($amount, (float) $voucher->discount_value), 2),
        };
    }

    public function reserve(object $voucher, string $companyId, string $requestKey, array $snapshot): object
    {
        return DB::transaction(function () use ($voucher, $companyId, $requestKey, $snapshot): object {
            $existing = DB::table('voucher_redemption_reservations')->where('request_key', $requestKey)->first();
            if ($existing) {
                return $existing;
            }

            $lockedVoucher = DB::table('vouchers')->where('id', $voucher->id)->lockForUpdate()->first();
            abort_unless($lockedVoucher && $lockedVoucher->status === 'ativa', 422, 'Voucher inválido ou indisponível.');

            $total = DB::table('voucher_redemptions')->where('voucher_id', $lockedVoucher->id)->count()
                + DB::table('voucher_redemption_reservations')->where('voucher_id', $lockedVoucher->id)->where('status', 'pending')->where('expires_at', '>', now())->count();
            $companyTotal = DB::table('voucher_redemptions')->where('voucher_id', $lockedVoucher->id)->where('company_id', $companyId)->count()
                + DB::table('voucher_redemption_reservations')->where('voucher_id', $lockedVoucher->id)->where('company_id', $companyId)->where('status', 'pending')->where('expires_at', '>', now())->count();
            abort_if($lockedVoucher->redemption_limit && $total >= $lockedVoucher->redemption_limit, 422, 'Voucher atingiu o limite de uso.');
            abort_if($lockedVoucher->redemption_limit_per_company && $companyTotal >= $lockedVoucher->redemption_limit_per_company, 422, 'Voucher já atingiu o limite para esta empresa.');

            $id = PrefixedUlid::make('VRS');
            DB::table('voucher_redemption_reservations')->insert([
                'id' => $id,
                'voucher_id' => $lockedVoucher->id,
                'company_id' => $companyId,
                'request_key' => Str::limit($requestKey, 128, ''),
                'status' => 'pending',
                'snapshot' => json_encode($snapshot),
                'reserved_at' => now(),
                'expires_at' => now()->addMinutes(self::RESERVATION_MINUTES),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return DB::table('voucher_redemption_reservations')->where('id', $id)->first();
        });
    }

    public function attachSubscription(string $reservationId, string $subscriptionId): void
    {
        DB::table('voucher_redemption_reservations')->where('id', $reservationId)->where('status', 'pending')->update([
            'subscription_id' => $subscriptionId,
            'updated_at' => now(),
        ]);
    }

    public function release(string $reservationId, string $status = 'released'): void
    {
        DB::table('voucher_redemption_reservations')->where('id', $reservationId)->where('status', 'pending')->update([
            'status' => $status,
            'released_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function confirmForSubscription(string $subscriptionId): void
    {
        DB::transaction(function () use ($subscriptionId): void {
            $reservation = DB::table('voucher_redemption_reservations')->where('subscription_id', $subscriptionId)->where('status', 'pending')->lockForUpdate()->first();
            if (! $reservation || ($reservation->expires_at && now()->gt($reservation->expires_at))) {
                if ($reservation) {
                    DB::table('voucher_redemption_reservations')->where('id', $reservation->id)->update(['status' => 'expired', 'released_at' => now(), 'updated_at' => now()]);
                }
                return;
            }

            $snapshot = json_decode($reservation->snapshot, true) ?: [];
            $startsAt = now();
            $endsAt = match ($snapshot['benefit_duration'] ?? null) {
                'd7' => $startsAt->copy()->addDays(7),
                'm1' => $startsAt->copy()->addMonth(),
                'm3' => $startsAt->copy()->addMonths(3),
                'm6' => $startsAt->copy()->addMonths(6),
                'a1' => $startsAt->copy()->addYear(),
                default => null,
            };
            $snapshot['benefit_starts_at'] = $startsAt->toISOString();
            $snapshot['benefit_ends_at'] = $endsAt?->toISOString();
            $redemptionId = PrefixedUlid::make('VRD');
            DB::table('voucher_redemptions')->insert([
                'id' => $redemptionId,
                'voucher_id' => $reservation->voucher_id,
                'company_id' => $reservation->company_id,
                'subscription_id' => $subscriptionId,
                'discount_amount' => (float) ($snapshot['discount_amount'] ?? 0),
                'benefit_starts_at' => $startsAt,
                'benefit_ends_at' => $endsAt,
                'snapshot' => json_encode([...$snapshot, 'redemption_id' => $redemptionId]),
                'created_at' => now(),
            ]);
            DB::table('voucher_redemption_reservations')->where('id', $reservation->id)->update(['status' => 'confirmed', 'confirmed_at' => now(), 'updated_at' => now()]);
        });
    }

    public function releaseForSubscription(string $subscriptionId): void
    {
        DB::table('voucher_redemption_reservations')->where('subscription_id', $subscriptionId)->where('status', 'pending')->update([
            'status' => 'released',
            'released_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
