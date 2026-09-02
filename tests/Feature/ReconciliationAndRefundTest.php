<?php

namespace Tests\Feature;

use App\Models\PlatformAdmin;
use App\Models\PlatformRole;
use App\Services\PrefixedUlid;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ReconciliationAndRefundTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Config::set('services.mercado_pago.access_token', 'sandbox-token');
    }

    public function test_reconciliation_is_opened_and_only_superadmin_can_correct_it(): void
    {
        $fixture = $this->fixture('aprovado');
        Http::fake(['https://api.mercadopago.com/preapproval/pre-reconcile' => Http::response(['status' => 'paused'], 200)]);
        $this->artisan('fokus:reconcile-mercado-pago')->assertSuccessful();
        $alert = DB::table('payment_reconciliation_alerts')->first();
        $this->assertNotNull($alert);
        $commercial = $this->admin('administrador_comercial');
        $super = $this->admin('superadministrador');
        $this->actingAs($commercial, 'platform')->patchJson('/api/backoffice/reconciliation/'.$alert->id, ['action' => 'corrigir', 'reason' => 'Revisão'])->assertForbidden();
        $this->actingAs($super, 'platform')->patchJson('/api/backoffice/reconciliation/'.$alert->id, ['action' => 'corrigir', 'reason' => 'Estado remoto confirmado'])->assertOk();
        $this->assertDatabaseHas('subscriptions', ['id' => $fixture['subscription_id'], 'status' => 'suspensa']);
    }

    public function test_refund_requires_approval_and_marks_full_payment_as_refunded(): void
    {
        $fixture = $this->fixture('aprovado');
        $commercial = $this->admin('administrador_comercial');
        $super = $this->admin('superadministrador');
        $response = $this->actingAs($commercial, 'platform')->postJson('/api/backoffice/refunds', ['payment_id' => $fixture['payment_id'], 'amount' => 64.70, 'allowed_case' => 'erro_tecnico', 'reason' => 'Cobrança duplicada identificada.'])->assertCreated();
        $refundId = $response->json('id');
        $this->actingAs($super, 'platform')->patchJson('/api/backoffice/refunds/'.$refundId, ['action' => 'aprovar', 'reason' => 'Aprovado'])->assertOk();
        Http::fake(['https://api.mercadopago.com/v1/payments/pay-refund/refunds' => Http::response(['id' => 'refund-1', 'status' => 'approved'], 201)]);
        $this->actingAs($super, 'platform')->patchJson('/api/backoffice/refunds/'.$refundId, ['action' => 'executar', 'reason' => 'Executado'])->assertOk();
        $this->assertDatabaseHas('refund_requests', ['id' => $refundId, 'status' => 'executado', 'provider_refund_id' => 'refund-1']);
        $this->assertDatabaseHas('payments', ['id' => $fixture['payment_id'], 'status' => 'estornado']);
    }

    private function admin(string $role): PlatformAdmin
    {
        return PlatformAdmin::create(['id' => PrefixedUlid::make('PAD'), 'name' => 'Billing Admin', 'email' => $role.'-'.PlatformAdmin::count().'@example.test', 'password' => Hash::make('Senha!2026'), 'status' => 'ativo', 'platform_role_id' => PlatformRole::where('code', $role)->value('id'), 'email_verified_at' => now()]);
    }

    private function fixture(string $paymentStatus): array
    {
        $userId = PrefixedUlid::make('USR');
        DB::table('users')->insert(['id' => $userId, 'name' => 'Cliente Financeiro', 'cpf' => '52998224725', 'email' => 'cliente-financeiro-'.DB::table('users')->count().'@example.test', 'password' => Hash::make('Senha!2026'), 'status' => 'ativa', 'email_verified_at' => now(), 'created_at' => now(), 'updated_at' => now()]);
        $companyId = PrefixedUlid::make('COM');
        $product = DB::table('products')->where('code', 'law')->first();
        DB::table('companies')->insert(['id' => $companyId, 'document_type' => 'cnpj', 'document_number' => '22345678000100', 'legal_name' => 'Empresa Financeira', 'status' => 'ativa', 'version' => 1, 'created_by' => $userId, 'updated_by' => $userId, 'created_at' => now(), 'updated_at' => now()]);
        $subscriptionId = PrefixedUlid::make('ASS');
        DB::table('subscriptions')->insert(['id' => $subscriptionId, 'company_id' => $companyId, 'product_id' => $product->id, 'status' => 'ativa', 'open_company_product' => $companyId.'-'.$product->id, 'version' => 1, 'billing_cycle' => 'monthly', 'current_period_starts_at' => now(), 'current_period_ends_at' => now()->addMonth(), 'provider_subscription_id' => 'pre-reconcile', 'created_by' => $userId, 'updated_by' => $userId, 'created_at' => now(), 'updated_at' => now()]);
        $paymentId = PrefixedUlid::make('PAG');
        DB::table('payments')->insert(['id' => $paymentId, 'company_id' => $companyId, 'subscription_id' => $subscriptionId, 'provider' => 'mercado_pago', 'provider_payment_id' => 'pay-refund', 'status' => $paymentStatus, 'amount' => 64.70, 'currency' => 'BRL', 'provider_subscription_id' => 'pre-reconcile', 'version' => 1, 'created_at' => now(), 'updated_at' => now()]);
        return ['subscription_id' => $subscriptionId, 'payment_id' => $paymentId];
    }
}
