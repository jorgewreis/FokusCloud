<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PlatformAdmin;
use App\Services\PlatformAudit;
use App\Services\CatalogManager;
use App\Services\CatalogPricing;
use App\Services\PrefixedUlid;
use App\Services\VoucherManager;
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

    public function catalog(Request $request, CatalogManager $catalog)
    {
        return response()->json($catalog->adminCatalog());
    }

    public function plans(Request $request, CatalogManager $catalog)
    {
        return response()->json($catalog->managementPlans()->values());
    }

    public function createProduct(Request $request, CatalogManager $catalog, PlatformAudit $audit)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:64'],
            'name' => ['required', 'string', 'max:120'],
            'technical_description' => ['nullable', 'string', 'max:2000'],
            'commercial_content' => ['nullable', 'string', 'max:20000'],
            'status' => ['nullable', Rule::in(['ativo', 'inativo'])],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'featured' => ['nullable', 'boolean'],
        ]);
        abort_if(DB::table('products')->where('code', Str::slug($data['code']))->exists(), 422, 'Já existe um sistema com este código.');

        $id = $catalog->createProduct($data);
        $audit->record($request->user()->id, 'backoffice.catalog_product_created', 'product', $id, reason: 'Criação de sistema comercial', after: $data, request: $request);

        return response()->json(['id' => $id, 'message' => 'Sistema criado.'], 201);
    }

    public function updateProduct(Request $request, string $product, CatalogManager $catalog, PlatformAudit $audit)
    {
        $data = $request->validate([
            'code' => ['nullable', 'string', 'max:64'],
            'name' => ['nullable', 'string', 'max:120'],
            'technical_description' => ['nullable', 'string', 'max:2000'],
            'commercial_content' => ['nullable', 'string', 'max:20000'],
            'status' => ['nullable', Rule::in(['ativo', 'inativo'])],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'featured' => ['nullable', 'boolean'],
        ]);
        $current = DB::table('products')->where('id', $product)->first();
        abort_unless($current, 404, 'Sistema não encontrado.');
        if (isset($data['code'])) {
            abort_if(DB::table('products')->where('code', Str::slug($data['code']))->where('id', '!=', $product)->exists(), 422, 'Já existe um sistema com este código.');
        }

        $catalog->updateProduct($product, $data);
        $audit->record($request->user()->id, 'backoffice.catalog_product_updated', 'product', $product, reason: 'Atualização de sistema comercial', before: (array) $current, after: $data, request: $request);

        return response()->json(['message' => 'Sistema atualizado.']);
    }

    public function createModule(Request $request, CatalogManager $catalog, PlatformAudit $audit)
    {
        $data = $this->validateModule($request);
        abort_unless(DB::table('products')->where('id', $data['product_id'])->exists(), 422, 'Sistema não encontrado.');
        abort_if(DB::table('modules')->where('product_id', $data['product_id'])->where('code', Str::slug($data['code']))->exists(), 422, 'Já existe uma funcionalidade com este código no sistema.');

        $id = $catalog->createModule($data);
        $audit->record($request->user()->id, 'backoffice.catalog_module_created', 'module', $id, reason: 'Criação de funcionalidade comercial', after: $data, request: $request);

        return response()->json(['id' => $id, 'message' => 'Funcionalidade criada.'], 201);
    }

    public function updateModule(Request $request, string $module, CatalogManager $catalog, PlatformAudit $audit)
    {
        $data = $this->validateModule($request, true);
        $current = DB::table('modules')->where('id', $module)->first();
        abort_unless($current, 404, 'Funcionalidade não encontrada.');
        $productId = $data['product_id'] ?? $current->product_id;
        if (isset($data['code'])) {
            abort_if(DB::table('modules')->where('product_id', $productId)->where('code', Str::slug($data['code']))->where('id', '!=', $module)->exists(), 422, 'Já existe uma funcionalidade com este código no sistema.');
        }

        $catalog->updateModule($module, $data);
        $audit->record($request->user()->id, 'backoffice.catalog_module_updated', 'module', $module, reason: 'Atualização de funcionalidade comercial', before: (array) $current, after: $data, request: $request);

        return response()->json(['message' => 'Funcionalidade atualizada.']);
    }

    public function createPlan(Request $request, CatalogManager $catalog, PlatformAudit $audit)
    {
        if (! $request->filled('name') && $request->filled('base_name')) {
            $request->merge(['name' => $request->input('base_name')]);
        }
        $data = $request->validate([
            'product_id' => ['nullable', 'string', 'size:30'],
            'system' => ['nullable', 'string', 'max:120'],
            'code' => ['required', 'string', 'max:64'],
            'name' => ['required', 'string', 'max:120'],
            'base_name' => ['nullable', 'string', 'max:120'],
            'technical_description' => ['nullable', 'string', 'max:2000'],
            'commercial_content' => ['nullable', 'string', 'max:20000'],
            'segment' => ['nullable', 'string', 'max:16'],
            'monthly_amount' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', Rule::in(['ativo', 'inativo'])],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'featured' => ['nullable', 'boolean'],
            'module_ids' => ['nullable', 'array'],
            'module_ids.*' => ['string', 'size:30'],
        ]);
        $data = $this->resolvePlanProductData($data);
        abort_if(DB::table('plans')->where('product_id', $data['product_id'])->where('code', Str::slug($data['code']))->exists(), 422, 'Já existe um plano com este código no sistema selecionado.');

        $id = $catalog->createPlan([...$data, 'status' => $data['status'] ?? 'inativo']);
        $audit->record($request->user()->id, 'backoffice.plan_created', 'plan', $id, reason: 'Criação de plano', after: $data, request: $request);

        return response()->json(['id' => $id, 'message' => 'Plano criado.'], 201);
    }

    public function updatePlan(Request $request, string $plan, CatalogManager $catalog, PlatformAudit $audit)
    {
        if (! $request->filled('name') && $request->filled('base_name')) {
            $request->merge(['name' => $request->input('base_name')]);
        }
        $data = $request->validate([
            'product_id' => ['nullable', 'string', 'size:30'],
            'system' => ['nullable', 'string', 'max:120'],
            'code' => ['nullable', 'string', 'max:64'],
            'name' => ['nullable', 'string', 'max:120'],
            'base_name' => ['nullable', 'string', 'max:120'],
            'technical_description' => ['nullable', 'string', 'max:2000'],
            'commercial_content' => ['nullable', 'string', 'max:20000'],
            'segment' => ['nullable', 'string', 'max:16'],
            'monthly_amount' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', Rule::in(['ativo', 'inativo'])],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'featured' => ['nullable', 'boolean'],
            'module_ids' => ['nullable', 'array'],
            'module_ids.*' => ['string', 'size:30'],
        ]);
        $current = DB::table('plans')->where('id', $plan)->first();
        abort_unless($current, 404, 'Plano não encontrado.');
        $data = $this->resolvePlanProductData($data, $current);
        if (isset($data['code'])) {
            abort_if(DB::table('plans')->where('product_id', $data['product_id'])->where('code', Str::slug($data['code']))->where('id', '!=', $plan)->exists(), 422, 'Já existe um plano com este código no sistema selecionado.');
        }

        $catalog->updatePlan($plan, $data);
        $audit->record($request->user()->id, 'backoffice.plan_updated', 'plan', $plan, reason: 'Atualização de plano', before: (array) $current, after: $data, request: $request);

        return response()->json(['message' => 'Plano atualizado.']);
    }

    public function syncPlanModules(Request $request, string $plan, CatalogManager $catalog, PlatformAudit $audit)
    {
        $data = $request->validate([
            'module_ids' => ['required', 'array', 'min:1'],
            'module_ids.*' => ['required', 'string', 'size:30'],
        ]);
        $current = DB::table('plan_modules')->where('plan_id', $plan)->pluck('module_id')->all();
        $catalog->syncPlanModules($plan, $data['module_ids']);
        $audit->record($request->user()->id, 'backoffice.plan_modules_updated', 'plan', $plan, reason: 'Atualização da composição do plano', before: ['module_ids' => $current], after: $data, request: $request);

        return response()->json(['message' => 'Composição atualizada.']);
    }

    public function publishCatalog(Request $request, string $product, CatalogManager $catalog, PlatformAudit $audit)
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:1000']]);
        $publication = $catalog->publish($product, $request->user()->id, $data['reason']);
        $audit->record($request->user()->id, 'backoffice.catalog_published', 'product', $product, reason: $data['reason'], metadata: ['version' => $publication['version']], request: $request);

        return response()->json(['message' => 'Catálogo publicado.', 'version' => $publication['version']]);
    }

    public function deleteCatalogPublication(Request $request, string $publication, CatalogManager $catalog, PlatformAudit $audit)
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:1000']]);
        $before = $catalog->deletePublication($publication);
        $audit->record(
            $request->user()->id,
            'backoffice.catalog_publication_deleted',
            'catalog_publication',
            $publication,
            $before['product_id'] ?? null,
            reason: $data['reason'],
            before: $before,
            request: $request,
        );

        return response()->json(['message' => 'Publicação excluída.']);
    }

    public function pauseCatalogItem(Request $request, string $type, string $id, CatalogManager $catalog, PlatformAudit $audit)
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:1000']]);
        [$before, $after] = $catalog->pauseOrArchive($type, $id, 'pausado');
        $audit->record($request->user()->id, 'backoffice.catalog_item_paused', $type, $id, reason: $data['reason'], before: $before, after: $after, request: $request);

        return response()->json(['message' => 'Item pausado.']);
    }

    public function archiveCatalogItem(Request $request, string $type, string $id, CatalogManager $catalog, PlatformAudit $audit)
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:1000']]);
        [$before, $after] = $catalog->pauseOrArchive($type, $id, 'arquivado');
        $audit->record($request->user()->id, 'backoffice.catalog_item_archived', $type, $id, reason: $data['reason'], before: $before, after: $after, request: $request);

        return response()->json(['message' => 'Item arquivado.']);
    }

    public function deleteModule(Request $request, string $module, CatalogManager $catalog, PlatformAudit $audit)
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:1000']]);
        $before = $catalog->deleteCatalogItem('module', $module);
        $audit->record($request->user()->id, 'backoffice.catalog_module_deleted', 'module', $module, reason: $data['reason'], before: $before, request: $request);

        return response()->json(['message' => 'Funcionalidade excluída.']);
    }

    public function deletePlan(Request $request, string $plan, CatalogManager $catalog, PlatformAudit $audit)
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:1000']]);
        $before = $catalog->deleteCatalogItem('plan', $plan);
        $audit->record($request->user()->id, 'backoffice.plan_deleted', 'plan', $plan, reason: $data['reason'], before: $before, request: $request);

        return response()->json(['message' => 'Plano excluído.']);
    }

    private function validateModule(Request $request, bool $partial = false): array
    {
        $required = $partial ? 'nullable' : 'required';

        return $request->validate([
            'product_id' => [$required, 'string', 'size:30'],
            'code' => [$required, 'string', 'max:64'],
            'module_code' => ['nullable', 'string', 'max:64'],
            'name' => [$required, 'string', 'max:120'],
            'technical_description' => ['nullable', 'string', 'max:2000'],
            'commercial_content' => ['nullable', 'string', 'max:20000'],
            'monthly_price' => [$required, 'numeric', 'min:0'],
            'segment_code' => ['nullable', 'string', 'max:32'],
            'context_code' => ['nullable', 'string', 'max:64'],
            'variant_code' => ['nullable', 'string', 'max:64'],
            'capabilities' => ['nullable', 'array'],
            'dependencies' => ['nullable', 'array'],
            'incompatibilities' => ['nullable', 'array'],
            'status' => ['nullable', Rule::in(['ativo', 'rascunho', 'pausado', 'arquivado'])],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'featured' => ['nullable', 'boolean'],
            'capacity_unit' => ['nullable', 'string', 'max:64'],
            'default_capacity' => ['nullable', 'integer', 'min:1'],
            'capacity_options' => ['nullable', 'array'],
            'capacity_options.*' => ['integer', 'min:1'],
            'available_standalone' => ['nullable', 'boolean'],
            'price_is_estimate' => ['nullable', 'boolean'],
        ]);
    }

    private function resolvePlanProductData(array $data, ?object $current = null): array
    {
        $productId = $data['product_id'] ?? $current?->product_id ?? null;
        $systemSegment = null;

        if (! empty($data['system'])) {
            [$resolvedProductId, $systemSegment] = $this->resolvePlanSystem($data['system']);
            $productId = $resolvedProductId ?: $productId;
        }

        abort_unless($productId, 422, 'O sistema selecionado não está disponível no catálogo.');
        abort_unless(DB::table('products')->where('id', $productId)->exists(), 422, 'O sistema selecionado não está disponível no catálogo.');

        $data['product_id'] = $productId;
        if (! array_key_exists('segment', $data) && $systemSegment) {
            $data['segment'] = $systemSegment;
        }
        unset($data['system']);

        return $data;
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

    public function voucher(Request $request, string $voucher)
    {
        $current = DB::table('vouchers as voucher')
            ->leftJoin('products as product', 'product.id', '=', 'voucher.product_id')
            ->leftJoin('plans as plan', 'plan.id', '=', 'voucher.plan_id')
            ->where('voucher.id', $voucher)
            ->select('voucher.*', 'product.name as product_name', 'plan.name as plan_name')
            ->first();
        abort_unless($current, 404, 'Voucher não encontrado.');

        return response()->json([
            'voucher' => $current,
            'redemptions' => DB::table('voucher_redemptions')->where('voucher_id', $voucher)->orderByDesc('created_at')->get(),
            'reservations' => DB::table('voucher_redemption_reservations')->where('voucher_id', $voucher)->orderByDesc('created_at')->get(),
        ]);
    }

    private function catalogPlanRows()
    {
        $plans = DB::table('plans as plan')
            ->join('products as product', 'product.id', '=', 'plan.product_id')
            ->leftJoin('plan_modules as plan_module', 'plan_module.plan_id', '=', 'plan.id')
            ->leftJoin('modules as module', 'module.id', '=', 'plan_module.module_id')
            ->where('product.active', true)
            ->groupBy('plan.id', 'plan.product_id', 'plan.code', 'plan.name', 'plan.monthly_amount', 'plan.segment', 'plan.status', 'plan.publication_state', 'plan.display_order', 'plan.featured', 'product.name')
            ->select('plan.id', 'plan.product_id', 'plan.code', 'plan.name', 'plan.monthly_amount as configured_monthly_amount', 'plan.segment', 'plan.status', 'plan.publication_state', 'plan.display_order', 'plan.featured', 'product.name as product_name', DB::raw('round(coalesce(sum(module.monthly_price), 0), 2) as module_monthly_amount'))
            ->orderBy('plan.product_id')
            ->orderBy('plan.display_order')
            ->orderBy('plan.name')
            ->get();

        return $plans->map(function ($plan) {
            $monthlyAmount = $plan->configured_monthly_amount === null
                ? CatalogPricing::suggestedMonthly((float) $plan->module_monthly_amount)
                : (float) $plan->configured_monthly_amount;

            return (object) [
                'id' => $plan->id,
                'product_id' => $plan->product_id,
                'product_name' => $plan->product_name,
                'code' => $plan->code,
                'name' => $plan->name,
                'full_name' => $plan->product_name.($plan->segment === 'one' ? ' One' : ($plan->segment === 'team' ? ' Team' : '')).' - '.$plan->name,
                'segment' => $plan->segment,
                'status' => $plan->status,
                'publication_state' => $plan->publication_state,
                'display_order' => $plan->display_order,
                'featured' => (bool) $plan->featured,
                'monthly_amount' => $monthlyAmount,
                'annual_amount' => CatalogPricing::annualFromMonthly($monthlyAmount),
            ];
        });
    }

    private function managementPlanRows()
    {
        return $this->catalogPlanRows()->values()->map(fn ($plan) => [
            'id' => $plan->id,
            'product_id' => $plan->product_id,
            'code' => $plan->code,
            'name' => $plan->name,
            'base_name' => $plan->name,
            'system' => $this->planSystemLabel($plan->product_name, $plan->segment),
            'full_name' => $plan->full_name,
            'segment' => $plan->segment,
            'status' => $plan->status,
            'publication_state' => $plan->publication_state,
            'display_order' => $plan->display_order,
            'featured' => (bool) $plan->featured,
            'monthly_amount' => $plan->monthly_amount,
            'annual_amount' => $plan->annual_amount,
        ]);
    }

    private function normalizePlanOperationalStatus(?string $value): string
    {
        $status = Str::lower(trim((string) ($value ?? '')));

        return match ($status) {
            'ativo', 'active' => 'ativo',
            default => 'inativo',
        };
    }

    private function normalizePlanPublicationState(?string $value): string
    {
        $state = Str::lower(trim((string) ($value ?? '')));

        return match ($state) {
            'publicado', 'published' => 'publicado',
            'pausado', 'paused' => 'pausado',
            'arquivado', 'archived' => 'arquivado',
            default => 'rascunho',
        };
    }

    private function resolvePlanSystem(string $system): array
    {
        $system = trim($system);
        $leadLines = [
            'Fokus Cloud Lead One' => 'one',
            'Fokus Cloud Lead Team' => 'team',
        ];

        if (isset($leadLines[$system])) {
            return [DB::table('products')->where('name', 'Fokus Cloud Lead')->value('id'), $leadLines[$system]];
        }

        return [DB::table('products')->where('id', $system)->orWhere('name', $system)->value('id'), null];
    }

    private function planSystemLabel(string $productName, ?string $segment): string
    {
        if ($productName !== 'Fokus Cloud Lead') {
            return $productName;
        }

        return match ($segment) {
            'one' => 'Fokus Cloud Lead One',
            'team' => 'Fokus Cloud Lead Team',
            default => $productName,
        };
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
            'discount_type' => ['required', Rule::in(['trial_free', 'percentage', 'fixed', 'commercial_credit'])],
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
        $data['code'] = ($data['code'] ?? null) ?: $this->generateVoucherCode($data['name'] ?? 'VOUCHER');
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
            abort_if($data['discount_type'] !== 'percentage', 422, 'Este tipo de voucher exige um plano específico.');
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
        $configuredMonthlyAmount = DB::table('plans')->where('id', $planId)->value('monthly_amount');
        $moduleMonthlyAmount = (float) DB::table('plan_modules as plan_module')
            ->join('modules as module', 'module.id', '=', 'plan_module.module_id')
            ->where('plan_module.plan_id', $planId)
            ->sum('module.monthly_price');
        $monthly = $configuredMonthlyAmount === null
            ? CatalogPricing::suggestedMonthly($moduleMonthlyAmount)
            : (float) $configuredMonthlyAmount;

        return match ($duration) {
            'd7' => round($monthly / 30 * 7, 2),
            'm1' => $monthly,
            'm3' => round($monthly * 3, 2),
            'm6' => round($monthly * 6, 2),
            'a1' => CatalogPricing::annualFromMonthly($monthly),
            default => $monthly,
        };
    }

    public function updateVoucher(Request $request, string $voucher, PlatformAudit $audit)
    {
        $current = DB::table('vouchers')->where('id', $voucher)->first();
        abort_unless($current, 404, 'Voucher não encontrado.');

        $data = $request->validate([
            'name' => ['sometimes', 'nullable', 'string', 'max:120'],
            'code' => ['sometimes', 'nullable', 'string', 'max:64', 'alpha_num', Rule::unique('vouchers', 'code')->ignore($voucher, 'id')],
            'discount_type' => ['sometimes', Rule::in(['trial_free', 'percentage', 'fixed', 'commercial_credit'])],
            'discount_value' => ['sometimes', 'numeric', 'gt:0'],
            'product_id' => ['sometimes', 'nullable', 'string', 'size:30'],
            'plan_id' => ['sometimes', 'nullable', 'string', 'size:30'],
            'benefit_duration' => ['sometimes', 'nullable', Rule::in(['d7', 'm1', 'm3', 'm6', 'a1'])],
            'redemption_limit' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'redemption_limit_per_company' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'starts_at' => ['sometimes', 'nullable', 'date'],
            'ends_at' => ['sometimes', 'nullable', 'date', 'after:starts_at'],
            'status' => ['sometimes', Rule::in(['ativa', 'suspensa'])],
            'origin' => ['sometimes', 'nullable', 'string', 'max:120'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:2000'],
        ]);
        $redemptions = DB::table('voucher_redemptions')->where('voucher_id', $voucher)->count();
        $editableFields = array_diff(array_keys($data), ['status']);
        abort_if($redemptions && $editableFields !== [], 422, 'Voucher com resgate não pode ter suas regras comerciais alteradas.');

        if ($editableFields !== []) {
            $merged = [...(array) $current, ...$data];
            if (($merged['discount_type'] ?? null) === 'percentage') abort_if((float) $merged['discount_value'] > 100, 422, 'O percentual não pode exceder 100%.');
            if (! empty($merged['product_id'])) abort_unless(DB::table('products')->where('id', $merged['product_id'])->where('active', true)->exists(), 422, 'O sistema selecionado não está disponível no catálogo.');
            if (! empty($merged['plan_id'])) abort_unless(DB::table('plans')->where('id', $merged['plan_id'])->exists(), 422, 'O plano selecionado não existe no catálogo.');
            if (! empty($merged['plan_id']) && ! empty($merged['product_id'])) abort_unless(DB::table('plans')->where('id', $merged['plan_id'])->where('product_id', $merged['product_id'])->exists(), 422, 'O plano selecionado não pertence ao sistema informado.');
            if (($merged['plan_id'] ?? null) === null) abort_if(($merged['discount_type'] ?? null) !== 'percentage', 422, 'Este tipo de voucher exige um plano específico.');
            $data['base_amount'] = $this->voucherBaseAmount($merged['plan_id'], $merged['benefit_duration'] ?? null);
            if (isset($data['code'])) $data['code'] = strtoupper($data['code']);
        }

        DB::table('vouchers')->where('id', $voucher)->update([...$data, 'updated_at' => now()]);

        $audit->record(
            $request->user()->id,
            $editableFields === [] ? 'backoffice.voucher_' . ($data['status'] === 'ativa' ? 'reactivated' : 'paused') : 'backoffice.voucher_updated',
            'voucher',
            $voucher,
            reason: $editableFields === [] ? ($data['status'] === 'ativa' ? 'Reativação de voucher' : 'Pausa de voucher') : 'Atualização de voucher',
            before: (array) $current,
            after: $data,
            request: $request,
        );

        return response()->json(['message' => $editableFields === [] ? 'Status do voucher atualizado.' : 'Voucher atualizado.']);
    }

    public function archiveVoucher(Request $request, string $voucher, PlatformAudit $audit)
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:1000']]);
        $current = DB::table('vouchers')->where('id', $voucher)->first();
        abort_unless($current, 404, 'Voucher não encontrado.');
        DB::table('vouchers')->where('id', $voucher)->update(['status' => 'encerrada', 'updated_at' => now()]);
        $audit->record($request->user()->id, 'backoffice.voucher_archived', 'voucher', $voucher, reason: $data['reason'], before: (array) $current, request: $request);

        return response()->json(['message' => 'Voucher arquivado.']);
    }

    public function deleteVoucher(Request $request, string $voucher, PlatformAudit $audit)
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:1000']]);
        $current = DB::table('vouchers')->where('id', $voucher)->first();
        abort_unless($current, 404, 'Voucher não encontrado.');
        abort_if(
            DB::table('voucher_redemptions')->where('voucher_id', $voucher)->exists(),
            422,
            'Vouchers com resgates não podem ser excluídos. Pause o voucher para impedir novos usos.',
        );
        abort_if(DB::table('voucher_redemption_reservations')->where('voucher_id', $voucher)->where('status', 'pending')->exists(), 422, 'Voucher possui uma reserva de checkout pendente. Aguarde a expiração ou arquive-o.');

        DB::table('vouchers')->where('id', $voucher)->delete();
        $audit->record($request->user()->id, 'backoffice.voucher_deleted', 'voucher', $voucher, reason: $data['reason'], request: $request);

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
        $query = DB::table('platform_audit_events')->orderByDesc('created_at')->limit(200);
        if (! $request->user()->hasPermission('platform.audit.view_all')) {
            $query->where(function ($commercial) {
                foreach (['backoffice.dashboard_%', 'backoffice.compan%', 'backoffice.plan_%', 'backoffice.subscription_%', 'backoffice.voucher_%'] as $action) {
                    $commercial->orWhere('action', 'like', $action);
                }
            });
        }

        return response()->json($query->get());
    }
}
