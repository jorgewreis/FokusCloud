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

class BillingSandboxTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Config::set('services.mercado_pago.access_token', 'sandbox-token');
        Config::set('services.mercado_pago.webhook_secret', 'test-secret');
    }

    public function test_valid_signed_payment_webhook_is_idempotent_and_activates_subscription(): void
    {
        $fixture = $this->fixture();
        Http::fake(['https://api.mercadopago.com/v1/payments/pay-sandbox' => Http::response([
            'id' => 'pay-sandbox', 'status' => 'approved', 'external_reference' => $fixture['payment_id'], 'preapproval_id' => 'pre-sandbox', 'transaction_amount' => 64.70,
        ], 200)]);
        $timestamp = (string) now()->timestamp;
        $signature = 'ts='.$timestamp.',v1='.hash_hmac('sha256', 'id:pay-sandbox;request-id:req-sandbox;ts:'.$timestamp.';', 'test-secret');
        $headers = ['x-signature' => $signature, 'x-request-id' => 'req-sandbox'];

        $this->withHeaders($headers)->postJson('/api/webhooks/mercado-pago?data.id=pay-sandbox', ['type' => 'payment'])->assertOk();
        $this->withHeaders($headers)->postJson('/api/webhooks/mercado-pago?data.id=pay-sandbox', ['type' => 'payment'])->assertOk();

        $this->assertDatabaseHas('payments', ['id' => $fixture['payment_id'], 'status' => 'aprovado', 'provider_payment_id' => 'pay-sandbox']);
        $this->assertDatabaseHas('subscriptions', ['id' => $fixture['subscription_id'], 'status' => 'ativa']);
        $this->assertDatabaseCount('billing_provider_events', 1);
    }

    public function test_invalid_signature_does_not_call_gateway_or_change_data(): void
    {
        $fixture = $this->fixture();
        Http::fake();
        $this->withHeaders(['x-signature' => 'ts='.now()->timestamp.',v1=invalid', 'x-request-id' => 'req-invalid'])
            ->postJson('/api/webhooks/mercado-pago?data.id=pay-invalid', ['type' => 'payment'])->assertUnauthorized();
        Http::assertNothingSent();
        $this->assertDatabaseHas('payments', ['id' => $fixture['payment_id'], 'status' => 'aguardando_pagamento']);
        $this->assertDatabaseCount('billing_provider_events', 0);
    }

    private function fixture(): array
    {
        $admin = PlatformAdmin::create(['id' => PrefixedUlid::make('PAD'), 'name' => 'Billing Admin', 'email' => 'billing-'.PlatformAdmin::count().'@example.test', 'password' => Hash::make('Senha!2026'), 'status' => 'ativo', 'platform_role_id' => PlatformRole::where('code', 'superadministrador')->value('id'), 'email_verified_at' => now()]);
        $userId = PrefixedUlid::make('USR');
        DB::table('users')->insert(['id' => $userId, 'name' => 'Cliente Billing', 'cpf' => '52998224725', 'email' => 'cliente-billing@example.test', 'password' => Hash::make('Senha!2026'), 'status' => 'ativa', 'email_verified_at' => now(), 'created_at' => now(), 'updated_at' => now()]);
        $companyId = PrefixedUlid::make('COM');
        $product = DB::table('products')->where('code', 'law')->first();
        DB::table('companies')->insert(['id' => $companyId, 'document_type' => 'cnpj', 'document_number' => '12345678000100', 'legal_name' => 'Empresa Billing', 'status' => 'ativa', 'version' => 1, 'created_by' => $userId, 'updated_by' => $userId, 'created_at' => now(), 'updated_at' => now()]);
        $subscriptionId = PrefixedUlid::make('ASS');
        DB::table('subscriptions')->insert(['id' => $subscriptionId, 'company_id' => $companyId, 'product_id' => $product->id, 'status' => 'aguardando_pagamento', 'open_company_product' => $companyId.'-'.$product->id, 'version' => 1, 'billing_cycle' => 'monthly', 'current_period_starts_at' => now(), 'current_period_ends_at' => now()->addMonth(), 'provider_subscription_id' => 'pre-sandbox', 'created_by' => $userId, 'updated_by' => $userId, 'created_at' => now(), 'updated_at' => now()]);
        $paymentId = PrefixedUlid::make('PAG');
        DB::table('payments')->insert(['id' => $paymentId, 'company_id' => $companyId, 'subscription_id' => $subscriptionId, 'provider' => 'mercado_pago', 'status' => 'aguardando_pagamento', 'amount' => 64.70, 'currency' => 'BRL', 'provider_subscription_id' => 'pre-sandbox', 'version' => 1, 'created_at' => now(), 'updated_at' => now()]);
        return ['admin' => $admin, 'subscription_id' => $subscriptionId, 'payment_id' => $paymentId];
    }
}
