<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PlatformAdmin;
use App\Services\PlatformAudit;
use App\Services\PrefixedUlid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
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
        return response()->json(DB::table('vouchers')->orderByDesc('created_at')->limit(100)->get());
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
        $data = $request->validate(['code' => ['required', 'string', 'max:64', 'alpha_dash', 'unique:vouchers,code'], 'discount_type' => ['required', Rule::in(['percentage', 'fixed'])], 'discount_value' => ['required', 'numeric', 'gt:0'], 'product_id' => ['nullable', 'string', 'size:30'], 'module_codes' => ['nullable', 'array'], 'redemption_limit' => ['nullable', 'integer', 'min:1'], 'redemption_limit_per_company' => ['nullable', 'integer', 'min:1'], 'starts_at' => ['nullable', 'date'], 'ends_at' => ['nullable', 'date', 'after:starts_at']]);
        if ($data['discount_type'] === 'percentage') abort_if($data['discount_value'] > 100, 422, 'O percentual não pode exceder 100%.');
        $id = PrefixedUlid::make('VCH');
        DB::table('vouchers')->insert([...$data, 'id' => $id, 'code' => strtoupper($data['code']), 'module_codes' => isset($data['module_codes']) ? json_encode($data['module_codes']) : null, 'status' => 'ativa', 'created_by_platform_admin_id' => $request->user()->id, 'created_at' => now(), 'updated_at' => now()]);
        $audit->record($request->user()->id, 'backoffice.voucher_created', 'voucher', $id, reason: 'Criação de voucher', request: $request);
        return response()->json(['id' => $id, 'message' => 'Voucher criado.'], 201);
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
