<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PrefixedUlid;
use App\Services\CatalogManager;
use App\Services\CatalogPricing;
use App\Services\VoucherManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;

class SubscriptionController extends Controller
{
    public function catalog(Request $request, string $productCode, CatalogManager $catalog)
    {
        return $this->publicCatalog($request, $productCode, $catalog);
    }

    public function publicCatalog(Request $request, string $productCode, CatalogManager $catalog)
    {
        return response()->json($catalog->publicCatalog($productCode));
    }

    public function index(Request $request)
    {
        $companyId = $request->attributes->get('active_company_id');
        $subscriptions = DB::table('subscriptions as subscription')->join('products as product', 'product.id', '=', 'subscription.product_id')
            ->where('subscription.company_id', $companyId)
            ->select('subscription.id', 'subscription.status', 'subscription.created_at', 'subscription.billing_cycle', 'subscription.current_period_ends_at', 'subscription.cancel_at', 'product.code as product_code', 'product.name as product_name')
            ->orderByDesc('subscription.created_at')->get();
        foreach ($subscriptions as $subscription) {
            $subscription->items = DB::table('subscription_items')->where('company_id', $companyId)->where('subscription_id', $subscription->id)
                ->select('name_snapshot as name', 'quantity', 'unit_price_snapshot as unit_price', 'conditions_snapshot')->get();
            $subscription->payment = DB::table('payments')->where('company_id', $companyId)->where('subscription_id', $subscription->id)
                ->latest('created_at')->select('status', 'amount', 'currency', 'created_at')->first();
        }
        return response()->json($subscriptions);
    }

    public function checkout(Request $request, CatalogManager $catalog, VoucherManager $vouchers)
    {
        abort_unless($request->user()->email_verified_at, 403, 'Confirme o e-mail antes de assinar.');
        $accessToken = config('services.mercado_pago.access_token');
        abort_unless($accessToken, 503, 'O checkout ainda não foi configurado.');
        $data = $request->validate([
            'product_code' => ['required', Rule::in(['law', 'lead'])],
            'items' => ['required', 'array', 'min:1'],
            'items.*.module_code' => ['required', 'string', 'max:64'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:1000'],
            'items.*.usage_limit' => ['nullable', 'integer', 'min:1'],
            'cycle' => ['required', Rule::in(['monthly', 'annual'])],
            'selection_mode' => ['required', Rule::in(['modules', 'plan'])],
            'plan_code' => ['nullable', 'string', 'max:64'],
            'voucher_code' => ['nullable', 'string', 'max:64'],
        ]);
        $companyId = $request->attributes->get('active_company_id');
        $product = DB::table('products')->where('code', $data['product_code'])->where('active', true)->first();
        abort_unless($product, 404, 'Produto não encontrado.');
        $quoted = $this->quote($product, $data, $catalog);
        $voucher = ! empty($data['voucher_code'])
            ? $vouchers->findEligible($data['voucher_code'], $product->id, $companyId, array_column($data['items'], 'module_code'), $data['plan_code'] ?? null)
            : null;
        if ($voucher) {
            $quoted['discount_amount'] = $vouchers->discount($voucher, $quoted['amount']);
            $quoted['amount'] = round($quoted['amount'] - $quoted['discount_amount'], 2);
        }
        $subscriptionId = PrefixedUlid::make('ASS');
        $paymentId = PrefixedUlid::make('PAG');
        $reservation = null;

        if ($voucher) {
            $plan = $data['plan_code'] ? DB::table('plans')->where('product_id', $product->id)->where('code', $data['plan_code'])->first() : null;
            $reservationKey = (string) ($request->header('Idempotency-Key') ?: hash('sha256', implode('|', [
                $request->user()->id,
                $companyId,
                $data['product_code'],
                $data['cycle'],
                $data['selection_mode'],
                $data['plan_code'] ?? '',
                $data['voucher_code'],
                json_encode($data['items']),
            ])));
            $reservation = $vouchers->reserve($voucher, $companyId, $reservationKey, [
                'voucher_id' => $voucher->id,
                'code' => $voucher->code,
                'name' => $voucher->name,
                'product_id' => $product->id,
                'product_code' => $product->code,
                'product_name' => $product->name,
                'plan_id' => $plan?->id,
                'plan_code' => $plan?->code,
                'plan_name' => $plan?->name,
                'discount_type' => $voucher->discount_type,
                'discount_value' => (float) $voucher->discount_value,
                'base_amount' => (float) ($voucher->base_amount ?? $quoted['amount']),
                'discount_amount' => $quoted['discount_amount'],
                'final_amount' => $quoted['amount'],
                'billing_cycle' => $data['cycle'],
                'benefit_duration' => $voucher->benefit_duration,
                'benefit_starts_at' => null,
                'benefit_ends_at' => null,
                'company_id' => $companyId,
                'subscription_id' => $subscriptionId,
                'selection_mode' => $data['selection_mode'],
                'module_codes' => array_column($data['items'], 'module_code'),
            ]);
        }

        try {
            $response = Http::withToken($accessToken)->timeout(15)->post('https://api.mercadopago.com/preapproval', [
                'external_reference' => $paymentId,
                'reason' => "Assinatura {$product->name}",
                'payer_email' => $request->user()->email,
                'auto_recurring' => ['frequency' => $data['cycle'] === 'annual' ? 12 : 1, 'frequency_type' => 'months', 'transaction_amount' => $quoted['amount'], 'currency_id' => 'BRL'],
                'back_url' => rtrim(config('app.url'), '/').'/portal/assinaturas',
                'notification_url' => rtrim(config('app.url'), '/').'/api/webhooks/mercado-pago',
            ])->throw()->json();
        } catch (\Throwable) {
            if ($reservation) $vouchers->release($reservation->id);
            return response()->json(['message' => 'Não foi possível iniciar o checkout. Nenhuma assinatura foi criada; tente novamente.'], 502);
        }

        try {
            DB::transaction(function () use ($companyId, $product, $quoted, $request, $subscriptionId, $paymentId, $response, $data) {
            $existing = DB::table('subscriptions')->where('company_id', $companyId)->where('product_id', $product->id)
                ->whereIn('status', ['pendente', 'ativa', 'suspensa'])->lockForUpdate()->first();
            abort_if($existing, 409, 'Já existe uma assinatura não encerrada para este produto.');
            DB::table('subscriptions')->insert([
                'id' => $subscriptionId, 'company_id' => $companyId, 'product_id' => $product->id, 'status' => 'pendente',
                'open_company_product' => $companyId.'-'.$product->id, 'version' => 1, 'billing_cycle' => $data['cycle'],
                'current_period_starts_at' => now(), 'current_period_ends_at' => $data['cycle'] === 'annual' ? now()->addYear() : now()->addMonth(),
                'provider_subscription_id' => $response['id'] ?? null,
                'created_by' => $request->user()->id, 'updated_by' => $request->user()->id, 'created_at' => now(), 'updated_at' => now(),
            ]);
            foreach ($quoted['items'] as $item) {
                DB::table('subscription_items')->insert([
                    'id' => PrefixedUlid::make('ITM'), 'company_id' => $companyId, 'subscription_id' => $subscriptionId,
                    'module_id' => $item['module']->id, 'name_snapshot' => $item['module']->name, 'quantity' => $item['quantity'],
                    'unit_price_snapshot' => $item['unit_price'], 'conditions_snapshot' => json_encode($item['conditions']),
                    'created_by' => $request->user()->id, 'updated_by' => $request->user()->id,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
            DB::table('payments')->insert([
                'id' => $paymentId, 'company_id' => $companyId, 'subscription_id' => $subscriptionId, 'amount' => $quoted['amount'],
                'currency' => 'BRL', 'status' => 'pendente', 'provider_payload' => json_encode(['preapproval_id' => $response['id'] ?? null]),
                'created_by' => $request->user()->id, 'updated_by' => $request->user()->id,
                'created_at' => now(), 'updated_at' => now(),
            ]);
            });
            if ($reservation) $vouchers->attachSubscription($reservation->id, $subscriptionId);
        } catch (\Throwable $exception) {
            if ($reservation) $vouchers->release($reservation->id);
            throw $exception;
        }

        return response()->json(['checkout_url' => $response['init_point'] ?? null, 'subscription_id' => $subscriptionId, 'amount' => $quoted['amount']], 201);
    }

    public function change(Request $request, string $subscription)
    {
        $membership = $request->attributes->get('active_membership');
        abort_unless($membership->role === 'admin', 403, 'Apenas o administrador pode alterar a assinatura.');
        $data = $request->validate([
            'type' => ['required', Rule::in(['upgrade', 'downgrade', 'cancelamento'])],
            'items' => ['nullable', 'array'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);
        $companyId = $request->attributes->get('active_company_id');
        $current = DB::table('subscriptions')->where('id', $subscription)->where('company_id', $companyId)->whereIn('status', ['ativa', 'suspensa'])->first();
        abort_unless($current, 404, 'Assinatura ativa não encontrada.');
        $effectiveAt = $data['type'] === 'upgrade' ? now() : ($current->current_period_ends_at ?: now());
        $status = $data['type'] === 'upgrade' ? 'pendente_pagamento' : 'agendada';
        DB::table('subscription_changes')->insert([
            'id' => PrefixedUlid::make('SCH'), 'company_id' => $companyId, 'subscription_id' => $current->id,
            'type' => $data['type'], 'status' => $status, 'effective_at' => $effectiveAt,
            'items_snapshot' => isset($data['items']) ? json_encode($data['items']) : null, 'reason' => $data['reason'] ?? null,
            'requested_by_user_id' => $request->user()->id, 'created_at' => now(), 'updated_at' => now(),
        ]);
        if ($data['type'] === 'cancelamento') DB::table('subscriptions')->where('id', $current->id)->update(['cancel_at' => $effectiveAt, 'updated_at' => now(), 'version' => DB::raw('version + 1')]);
        return response()->json(['message' => $data['type'] === 'upgrade' ? 'Upgrade criado e aguardando a cobrança proporcional.' : 'Alteração programada para o fim do ciclo.', 'effective_at' => $effectiveAt]);
    }

    public function webhook(Request $request, VoucherManager $vouchers)
    {
        $this->assertWebhookSignature($request);
        if (in_array((string) $request->input('type'), ['subscription_preapproval', 'preapproval'], true)) {
            $providerId = (string) data_get($request->all(), 'data.id');
            if ($providerId) {
                $remote = Http::withToken(config('services.mercado_pago.access_token'))->timeout(15)->get("https://api.mercadopago.com/preapproval/{$providerId}")->throw()->json();
                $status = ($remote['status'] ?? '') === 'authorized' ? 'ativa' : (($remote['status'] ?? '') === 'cancelled' ? 'encerrada' : 'pendente');
                $subscription = DB::table('subscriptions')->where('provider_subscription_id', $providerId)->first();
                if ($subscription) {
                    DB::table('subscriptions')->where('id', $subscription->id)->update(['status' => $status, 'updated_at' => now(), 'version' => DB::raw('version + 1')]);
                    if ($status === 'ativa') $vouchers->confirmForSubscription($subscription->id);
                    if ($status === 'encerrada') $vouchers->releaseForSubscription($subscription->id);
                }
            }
            return response()->noContent();
        }
        $providerPaymentId = data_get($request->all(), 'data.id');
        if (! $providerPaymentId) {
            return response()->noContent();
        }
        $accessToken = config('services.mercado_pago.access_token');
        abort_unless($accessToken, 503, 'Webhook não configurado.');
        $remote = Http::withToken($accessToken)->timeout(15)->get("https://api.mercadopago.com/v1/payments/{$providerPaymentId}")->throw()->json();
        $localId = $remote['external_reference'] ?? null;
        $status = match ($remote['status'] ?? '') {
            'approved' => 'aprovado',
            'rejected' => 'recusado',
            'cancelled' => 'cancelado',
            default => 'pendente',
        };
        $subscriptionId = null;
        DB::transaction(function () use ($localId, $providerPaymentId, $status, $remote, &$subscriptionId) {
            $payment = DB::table('payments')->where('id', $localId)->lockForUpdate()->first();
            if (! $payment || ($payment->provider_payment_id === (string) $providerPaymentId && $payment->status === $status)) {
                return;
            }
            DB::table('payments')->where('id', $payment->id)->update([
                'provider_payment_id' => (string) $providerPaymentId, 'status' => $status,
                'provider_payload' => json_encode($remote), 'updated_at' => now(), 'version' => DB::raw('version + 1'),
            ]);
            $subscriptionId = $payment->subscription_id;
            if ($status === 'aprovado') {
                DB::table('subscriptions')->where('id', $payment->subscription_id)->where('company_id', $payment->company_id)
                    ->update(['status' => 'ativa', 'updated_at' => now(), 'version' => DB::raw('version + 1')]);
            }
        });
        if ($subscriptionId) {
            if ($status === 'aprovado') $vouchers->confirmForSubscription($subscriptionId);
            if (in_array($status, ['recusado', 'cancelado'], true)) $vouchers->releaseForSubscription($subscriptionId);
        }
        return response()->noContent();
    }

    private function quote(object $product, array $data, CatalogManager $catalog): array
    {
        $codes = array_column($data['items'], 'module_code');
        abort_if(count($codes) !== count(array_unique($codes)), 422, 'Um módulo só pode ser informado uma vez.');
        $publishedModules = $catalog->publishedModuleMap($product->code);
        $publishedPlans = $catalog->publishedPlanMap($product->code);
        abort_unless($publishedModules->isNotEmpty(), 422, 'Catálogo publicado indisponível para este produto.');

        $publishedPlan = null;
        if ($data['selection_mode'] === 'plan') {
            $publishedPlan = $publishedPlans[$data['plan_code'] ?? ''] ?? null;
            abort_unless($publishedPlan, 422, 'Plano não encontrado ou indisponível.');
            abort_unless($this->sameModules($codes, $publishedPlan['module_codes'] ?? []), 422, 'Os módulos não correspondem ao plano informado.');
        }
        foreach ($codes as $code) {
            abort_unless($publishedModules->has($code), 422, 'Módulo inválido para este produto.');
        }

        $modules = DB::table('modules')->where('product_id', $product->id)->whereIn('code', $codes)->get()->keyBy('code');
        abort_unless($modules->count() === count($codes), 422, 'Módulo inválido para este produto.');
        $technicalCodes = $modules->pluck('module_code')->filter()->values()->all();
        abort_if(count($technicalCodes) !== count(array_unique($technicalCodes)), 422, 'Escolha apenas uma variante de cada módulo.');
        $lawSegments = $modules->pluck('segment_code')->filter()->unique()->values();
        abort_if($product->code === 'law' && $lawSegments->count() > 1, 422, 'As variantes selecionadas pertencem a segmentos incompatíveis.');
        $monthly = 0.0;
        $items = [];
        foreach ($data['items'] as $requested) {
            $module = $modules[$requested['module_code']];
            $publishedModule = $publishedModules[$requested['module_code']];
            $usage = $publishedModule['usage'] ?? null;
            $usageLimit = $requested['usage_limit'] ?? ($usage['options'][0] ?? null);
            if ($usage) {
                abort_unless(in_array($usageLimit, $usage['options'], true), 422, 'Limite de utilização inválido.');
            } else {
                abort_if(isset($requested['usage_limit']), 422, 'Este módulo não possui limite configurável.');
            }
            $unit = (float) $publishedModule['monthly_amount'] + ($usage ? array_search($usageLimit, $usage['options'], true) * ($usage['step'] ?? 0) : 0);
            $monthly += $unit * $requested['quantity'];
            $items[] = ['module' => $module, 'quantity' => $requested['quantity'], 'unit_price' => $data['cycle'] === 'annual' ? $unit * 10 : $unit, 'conditions' => ['cycle' => $data['cycle'], 'usage_limit' => $usageLimit, 'selection_mode' => $data['selection_mode'], 'plan_code' => $data['plan_code'] ?? null, 'module_code' => $module->module_code, 'segment_code' => $module->segment_code, 'context_code' => $module->context_code, 'variant_code' => $module->variant_code]];
        }
        if ($data['selection_mode'] === 'plan') {
            $monthly = (float) $publishedPlan['monthly_amount'];
            foreach ($items as &$item) {
                $item['unit_price'] *= 0.9;
            }
        }
        $amount = $data['cycle'] === 'annual' ? CatalogPricing::annualFromMonthly($monthly) : round($monthly, 2);
        return ['items' => $items, 'amount' => $amount];
    }

    private function voucherFor(string $code, string $productId, string $companyId, array $moduleCodes): ?object
    {
        $voucher = DB::table('vouchers')->where('code', strtoupper($code))->where('status', 'ativa')->first();
        abort_unless($voucher, 422, 'Voucher inválido ou indisponível.');
        abort_if(($voucher->starts_at && now()->lt($voucher->starts_at)) || ($voucher->ends_at && now()->gt($voucher->ends_at)), 422, 'Voucher fora do período de validade.');
        abort_if($voucher->product_id && $voucher->product_id !== $productId, 422, 'Voucher não é elegível para este produto.');
        $eligibleModules = $voucher->module_codes ? json_decode($voucher->module_codes, true) : [];
        abort_if($eligibleModules && ! array_intersect($eligibleModules, $moduleCodes), 422, 'Voucher não é elegível para os módulos selecionados.');
        $total = DB::table('voucher_redemptions')->where('voucher_id', $voucher->id)->count();
        $companyTotal = DB::table('voucher_redemptions')->where('voucher_id', $voucher->id)->where('company_id', $companyId)->count();
        abort_if($voucher->redemption_limit && $total >= $voucher->redemption_limit, 422, 'Voucher atingiu o limite de uso.');
        abort_if($voucher->redemption_limit_per_company && $companyTotal >= $voucher->redemption_limit_per_company, 422, 'Voucher já atingiu o limite para esta empresa.');
        return $voucher;
    }

    private function sameModules(array $actual, array $expected): bool
    {
        sort($actual);
        sort($expected);
        return $actual === $expected;
    }

    private function jsonArray(?string $value): array
    {
        $decoded = $value ? json_decode($value, true) : [];
        return is_array($decoded) ? $decoded : [];
    }

    private function assertWebhookSignature(Request $request): void
    {
        $secret = config('services.mercado_pago.webhook_secret');
        abort_unless($secret, 503, 'Assinatura do webhook não configurada.');
        $signature = (string) $request->header('x-signature');
        $requestId = (string) $request->header('x-request-id');
        preg_match('/(?:^|,)\s*ts=([^,]+)/', $signature, $timestamp);
        preg_match('/(?:^|,)\s*v1=([^,]+)/', $signature, $digest);
        $dataId = (string) data_get($request->all(), 'data.id');
        abort_unless($dataId && ! empty($timestamp[1]) && ! empty($digest[1]), 401, 'Assinatura de webhook inválida.');
        $manifest = "id:{$dataId};request-id:{$requestId};ts:{$timestamp[1]};";
        $expected = hash_hmac('sha256', $manifest, $secret);
        abort_unless(hash_equals($expected, $digest[1]), 401, 'Assinatura de webhook inválida.');
    }
}
