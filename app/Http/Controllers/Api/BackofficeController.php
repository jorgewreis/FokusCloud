<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PlatformAdmin;
use App\Services\PlatformAudit;
use App\Services\CatalogPricing;
use App\Services\PrefixedUlid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BackofficeController extends Controller
{
    public function dashboard(Request $request, PlatformAudit $audit)
    {
        $audit->record($request->user()->id, 'backoffice.dashboard_viewed', request: $request);
        return response()->json([
            'companies' => DB::table('companies')->whereNull('deleted_at')->count(),
            'active_subscriptions' => DB::table('subscriptions')->where('status', 'ativa')->count(),
            'scheduled_changes' => DB::table('subscription_changes')->where('status', 'agendada')->count(),
            'recent_usage' => DB::table('usage_snapshots')->where('reported_on', '>=', now()->subDays(7)->toDateString())->count(),
        ]);
    }

    public function companies(Request $request, PlatformAudit $audit)
    {
        $query = trim((string) $request->query('q', ''));
        $rows = DB::table('companies as company')
            ->leftJoin('company_memberships as membership', fn ($join) => $join->on('membership.company_id', '=', 'company.id')->whereNotNull('membership.active_admin_company_id'))
            ->leftJoin('users as admin', 'admin.id', '=', 'membership.user_id')
            ->leftJoin('subscriptions as subscription', fn ($join) => $join->on('subscription.company_id', '=', 'company.id')->where('subscription.status', 'ativa'))
            ->whereNull('company.deleted_at')
            ->when($query, fn ($builder) => $builder->where(fn ($filter) => $filter->where('company.legal_name', 'like', "%{$query}%")->orWhere('company.document_number', 'like', "%{$query}%")))
            ->groupBy('company.id', 'company.legal_name', 'company.document_number', 'company.status', 'admin.name', 'admin.email')
            ->select('company.id', 'company.legal_name', 'company.document_number', 'company.status', 'admin.name as admin_name', 'admin.email as admin_email', DB::raw('count(distinct subscription.id) as active_subscriptions'))
            ->orderBy('company.legal_name')->limit(100)->get();
        $audit->record($request->user()->id, 'backoffice.companies_viewed', request: $request);
        return response()->json($rows);
    }

    public function company(Request $request, string $company, PlatformAudit $audit)
    {
        $entity = DB::table('companies')->where('id', $company)->first();
        abort_unless($entity, 404, 'Empresa não encontrada.');
        $audit->record($request->user()->id, 'backoffice.company_viewed', 'company', $company, $company, request: $request);
        return response()->json([
            'company' => $entity,
            'subscriptions' => DB::table('subscriptions as subscription')->join('products as product', 'product.id', '=', 'subscription.product_id')->where('subscription.company_id', $company)->select('subscription.*', 'product.name as product_name')->get(),
            'usage' => DB::table('usage_snapshots as usage')->join('products as product', 'product.id', '=', 'usage.product_id')->where('usage.company_id', $company)->select('usage.*', 'product.name as product_name')->latest('reported_on')->limit(30)->get(),
        ]);
    }

    public function catalog(Request $request)
    {
        $products = DB::table('products')->where('active', true)->orderBy('name')->get();
        $plans = DB::table('plans as plan')
            ->join('products as product', 'product.id', '=', 'plan.product_id')
            ->leftJoin('plan_modules as plan_module', 'plan_module.plan_id', '=', 'plan.id')
            ->leftJoin('modules as module', 'module.id', '=', 'plan_module.module_id')
            ->where('product.active', true)
            ->groupBy('plan.id', 'plan.product_id', 'plan.code', 'plan.name', 'plan.segment', 'plan.status', 'plan.display_order', 'plan.featured')
            ->select('plan.id', 'plan.product_id', 'plan.code', 'plan.name', 'plan.segment', 'plan.status', 'plan.display_order', 'plan.featured', DB::raw('round(coalesce(sum(module.monthly_price), 0), 2) as monthly_amount'))
            ->orderBy('plan.product_id')
            ->orderBy('plan.display_order')
            ->get();
        $modules = DB::table('modules')->whereIn('product_id', $products->pluck('id'))->orderBy('name')->get();

        return response()->json([
            'products' => $products->map(fn ($product) => [
                'id' => $product->id,
                'code' => $product->code,
                'name' => $product->name,
                'plans' => $plans->where('product_id', $product->id)->values()->map(fn ($plan) => [
                    'id' => $plan->id,
                    'code' => $plan->code,
                    'name' => $plan->name,
                    'full_name' => $product->name.($plan->segment === 'one' ? ' One' : ($plan->segment === 'team' ? ' Team' : '')).' - '.$plan->name,
                    'segment' => $plan->segment,
                    'status' => $plan->status,
                    'display_order' => $plan->display_order,
                    'featured' => (bool) $plan->featured,
                    'monthly_amount' => CatalogPricing::suggestedMonthly((float) $plan->monthly_amount),
                    'annual_amount' => CatalogPricing::annualFromMonthly(CatalogPricing::suggestedMonthly((float) $plan->monthly_amount)),
                    'modules' => DB::table('plan_modules as plan_module')
                        ->join('modules as module', 'module.id', '=', 'plan_module.module_id')
                        ->where('plan_module.plan_id', $plan->id)
                        ->orderBy('module.name')
                        ->get(['module.id', 'module.code', 'module.name', 'module.context_code', 'module.variant_code', 'module.status', 'module.price_is_estimate'])
                        ->map(fn ($module) => [...(array) $module, 'price_is_estimate' => (bool) $module->price_is_estimate]),
                ]),
            ])->values(),
        ]);
    }

    public function changeSubscription(Request $request, string $subscription, PlatformAudit $audit)
    {
        $data = $request->validate(['action' => ['required', Rule::in(['suspensao', 'reativacao', 'cancelamento'])], 'reason' => ['required', 'string', 'max:1000']]);
        $current = DB::table('subscriptions')->where('id', $subscription)->first();
        abort_unless($current, 404, 'Assinatura não encontrada.');
        $effective = $data['action'] === 'cancelamento' ? ($current->current_period_ends_at ?: now()) : now();
        $status = $data['action'] === 'suspensao' ? 'suspensa' : ($data['action'] === 'reativacao' ? 'ativa' : $current->status);
        DB::transaction(function () use ($current, $data, $effective, $status, $request) {
            DB::table('subscription_changes')->insert(['id' => PrefixedUlid::make('SCH'), 'company_id' => $current->company_id, 'subscription_id' => $current->id, 'type' => $data['action'], 'status' => $data['action'] === 'cancelamento' ? 'agendada' : 'aplicada', 'effective_at' => $effective, 'reason' => $data['reason'], 'requested_by_platform_admin_id' => $request->user()->id, 'created_at' => now(), 'updated_at' => now()]);
            DB::table('subscriptions')->where('id', $current->id)->update(['status' => $status, 'cancel_at' => $data['action'] === 'cancelamento' ? $effective : null, 'updated_at' => now(), 'version' => DB::raw('version + 1')]);
        });
        $audit->record($request->user()->id, 'backoffice.subscription_'.$data['action'], 'subscription', $subscription, $current->company_id, $data['reason'], request: $request);
        return response()->json(['message' => 'Alteração comercial registrada.']);
    }

    public function vouchers(Request $request)
    {
        $rows = DB::table('vouchers as voucher')
            ->leftJoin('products as product', 'product.id', '=', 'voucher.product_id')
            ->leftJoin('plans as plan', 'plan.id', '=', 'voucher.plan_id')
            ->leftJoin('voucher_redemptions as redemption', 'redemption.voucher_id', '=', 'voucher.id')
            ->groupBy('voucher.id', 'voucher.code', 'voucher.name', 'voucher.discount_type', 'voucher.discount_value', 'voucher.product_id', 'voucher.plan_id', 'voucher.base_amount', 'voucher.benefit_duration', 'voucher.module_codes', 'voucher.redemption_limit', 'voucher.redemption_limit_per_company', 'voucher.starts_at', 'voucher.ends_at', 'voucher.status', 'voucher.origin', 'voucher.notes', 'voucher.created_by_platform_admin_id', 'voucher.created_at', 'voucher.updated_at', 'product.name', 'plan.name')
            ->select('voucher.*', 'product.name as product_name', 'plan.name as plan_name', DB::raw('count(redemption.id) as redemptions_count'))
            ->orderByDesc('voucher.created_at')
            ->limit(100)
            ->get();

        return response()->json($rows->map(function ($voucher) {
            $voucher->computed_status = $voucher->ends_at && now()->gt(\Illuminate\Support\Carbon::parse($voucher->ends_at)) ? 'expirada' : $voucher->status;
            return $voucher;
        }));
    }

    public function createAdmin(Request $request, PlatformAudit $audit)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:255'], 'email' => ['required', 'email:rfc', 'max:255', 'unique:platform_admins,email'], 'password' => ['required', 'string', 'min:12', 'confirmed']]);
        $admin = PlatformAdmin::create(['id' => PrefixedUlid::make('PAD'), 'name' => $data['name'], 'email' => strtolower($data['email']), 'password' => Hash::make($data['password']), 'status' => 'ativo', 'email_verified_at' => now()]);
        Mail::raw('Uma conta de superadministrador do backoffice Fokus Cloud foi criada para você. Use a senha entregue por canal seguro e o código enviado por e-mail para entrar.', fn ($mail) => $mail->to($admin->email)->subject('Fokus Cloud: acesso ao backoffice criado'));
        $audit->record($request->user()->id, 'backoffice.admin_created', 'platform_admin', $admin->id, reason: 'Criação de superadministrador', request: $request);
        return response()->json(['id' => $admin->id, 'message' => 'Superadministrador criado.'], 201);
    }

    public function createVoucher(Request $request, PlatformAudit $audit)
    {
        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:120'],
            'code' => ['nullable', 'string', 'max:64', 'alpha_num', 'unique:vouchers,code'],
            'discount_type' => ['required', Rule::in(['percentage', 'fixed'])],
            'discount_value' => ['required', 'numeric', 'gt:0'],
            'product_id' => ['nullable', 'string', 'size:30'],
            'plan_id' => ['nullable', 'string', 'size:30'],
            'base_amount' => ['nullable', 'numeric', 'min:0'],
            'benefit_duration' => ['nullable', Rule::in(['d7', 'm1', 'm3', 'm6', 'a1'])],
            'module_codes' => ['nullable', 'array'],
            'redemption_limit' => ['nullable', 'integer', 'min:1'],
            'redemption_limit_per_company' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date', 'after_or_equal:today'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'status' => ['nullable', Rule::in(['ativa', 'suspensa', 'encerrada'])],
            'origin' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
        $data['code'] = $data['code'] ?: $this->generateVoucherCode($data['name'] ?? 'VOUCHER');
        if ($data['discount_type'] === 'percentage') abort_if($data['discount_value'] > 100, 422, 'O percentual não pode exceder 100%.');
        if (! empty($data['product_id'])) {
            abort_unless(DB::table('products')->where('id', $data['product_id'])->where('active', true)->exists(), 422, 'O sistema selecionado não está disponível no catálogo.');
        }
        if (! empty($data['plan_id'])) {
            abort_unless(DB::table('plans')->where('id', $data['plan_id'])->exists(), 422, 'O plano selecionado não existe no catálogo.');
        }
        if (! empty($data['plan_id']) && ! empty($data['product_id'])) {
            abort_unless(DB::table('plans')->where('id', $data['plan_id'])->where('product_id', $data['product_id'])->exists(), 422, 'O plano selecionado não pertence ao sistema informado.');
        }
        if (! empty($data['plan_id'])) {
            $data['base_amount'] = $this->voucherBaseAmount($data['plan_id'], $data['benefit_duration'] ?? null);
        } else {
            $data['base_amount'] = null;
        }
        $id = PrefixedUlid::make('VCH');
        DB::table('vouchers')->insert([...$data, 'id' => $id, 'code' => strtoupper($data['code']), 'module_codes' => isset($data['module_codes']) ? json_encode($data['module_codes']) : null, 'status' => $data['status'] ?? 'ativa', 'created_by_platform_admin_id' => $request->user()->id, 'created_at' => now(), 'updated_at' => now()]);
        $audit->record($request->user()->id, 'backoffice.voucher_created', 'voucher', $id, reason: 'Criação de voucher', request: $request);
        return response()->json(['id' => $id, 'code' => $data['code'], 'message' => 'Voucher criado.'], 201);
    }

    private function generateVoucherCode(string $name): string
    {
        $normalized = Str::upper(Str::ascii($name));
        $words = preg_split('/[^A-Z0-9]+/', $normalized, -1, PREG_SPLIT_NO_EMPTY) ?: ['VOUCHER'];
        $prefix = '';

        foreach ($words as $word) {
            $prefix .= substr($word, 0, min(3, 6 - strlen($prefix)));
            if (strlen($prefix) >= 6) break;
        }

        $prefix = substr($prefix . preg_replace('/[^A-Z0-9]/', '', $normalized), 0, 6);
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

        do {
            $suffix = '';
            for ($index = 0; $index < 2; $index++) {
                $suffix .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }
            $code = str_pad($prefix, 6, 'X') . $suffix;
        } while (DB::table('vouchers')->where('code', $code)->exists());

        return $code;
    }

    private function voucherBaseAmount(string $planId, ?string $duration): float
    {
        $monthly = (float) DB::table('plan_modules as plan_module')
            ->join('modules as module', 'module.id', '=', 'plan_module.module_id')
            ->where('plan_module.plan_id', $planId)
            ->sum('module.monthly_price');
        $monthly = CatalogPricing::suggestedMonthly($monthly);

        return match ($duration) {
            'm3' => round($monthly * 3, 2),
            'm6' => round($monthly * 6, 2),
            'a1' => CatalogPricing::annualFromMonthly($monthly),
            default => $monthly,
        };
    }

    public function updateVoucherStatus(Request $request, string $voucher, PlatformAudit $audit)
    {
        $data = $request->validate(['status' => ['required', Rule::in(['ativa', 'suspensa'])]]);
        $current = DB::table('vouchers')->where('id', $voucher)->first();
        abort_unless($current, 404, 'Voucher não encontrado.');

        DB::table('vouchers')->where('id', $voucher)->update([
            'status' => $data['status'],
            'updated_at' => now(),
        ]);

        $audit->record(
            $request->user()->id,
            'backoffice.voucher_' . ($data['status'] === 'ativa' ? 'reactivated' : 'paused'),
            'voucher',
            $voucher,
            reason: $data['status'] === 'ativa' ? 'Reativação de voucher' : 'Pausa de voucher',
            request: $request,
        );

        return response()->json(['message' => 'Status do voucher atualizado.']);
    }

    public function deleteVoucher(Request $request, string $voucher, PlatformAudit $audit)
    {
        $current = DB::table('vouchers')->where('id', $voucher)->first();
        abort_unless($current, 404, 'Voucher não encontrado.');
        abort_if(
            DB::table('voucher_redemptions')->where('voucher_id', $voucher)->exists(),
            422,
            'Vouchers com resgates não podem ser excluídos. Pause o voucher para impedir novos usos.',
        );

        DB::table('vouchers')->where('id', $voucher)->delete();
        $audit->record($request->user()->id, 'backoffice.voucher_deleted', 'voucher', $voucher, reason: 'Exclusão de voucher', request: $request);

        return response()->noContent();
    }

    public function forcePasswordReset(Request $request, string $user, AuthController $auth, PlatformAudit $audit)
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:1000'], 'support_ticket' => ['required', 'string', 'max:100']]);
        $target = User::findOrFail($user);
        $auth->sendToken($target, 'password_reset', '/criar-senha', ['forced_by_support' => true, 'support_ticket' => $data['support_ticket']]);
        Mail::raw('Uma redefinição de senha foi solicitada pelo suporte Fokus Cloud. Use apenas o link enviado para criar uma nova senha.', fn ($mail) => $mail->to($target->email)->subject('Fokus Cloud: redefinição de senha solicitada'));
        $audit->record($request->user()->id, 'backoffice.password_reset_requested', 'user', $target->id, reason: $data['reason'], ticket: $data['support_ticket'], request: $request);
        return response()->json(['message' => 'Link de redefinição enviado ao usuário.']);
    }

    public function audit(Request $request)
    {
        return response()->json(DB::table('platform_audit_events')->orderByDesc('created_at')->limit(200)->get());
    }
}
