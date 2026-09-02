<?php

namespace Tests\Feature;

use App\Models\PlatformAdmin;
use App\Models\PlatformRole;
use App\Models\User;
use App\Services\PrefixedUlid;
use App\Services\VoucherManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class VoucherRedemptionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Mail::fake();
        Config::set('services.mercado_pago.access_token', 'test-token');
        Config::set('services.mercado_pago.webhook_secret', 'test-secret');
    }

    public function test_checkout_reserves_voucher_and_webhook_confirms_full_snapshot(): void
    {
        [$user, $companyId] = $this->customerContext();
        $admin = $this->platformAdmin();
        $product = DB::table('products')->where('code', 'law')->first();
        $plan = DB::table('plans')->where('product_id', $product->id)->where('code', 'law-advocacia')->first();
        $voucherId = PrefixedUlid::make('VCH');
        DB::table('vouchers')->insert([
            'id' => $voucherId,
            'code' => 'CREDITO500',
            'name' => 'Crédito comercial',
            'discount_type' => 'commercial_credit',
            'discount_value' => 500,
            'product_id' => $product->id,
            'plan_id' => $plan->id,
            'base_amount' => 64.70,
            'benefit_duration' => 'm1',
            'redemption_limit' => 1,
            'redemption_limit_per_company' => 1,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addMonth(),
            'status' => 'ativa',
            'created_by_platform_admin_id' => $admin->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Http::fake([
            'https://api.mercadopago.com/preapproval' => Http::response(['id' => 'preapproval-test', 'init_point' => 'https://mercadopago.test/checkout'], 201),
            'https://api.mercadopago.com/preapproval/preapproval-test' => Http::response(['status' => 'authorized'], 200),
        ]);

        $response = $this->actingAs($user)->withSession(['active_company_id' => $companyId])->postJson('/api/subscriptions/checkout', [
            'product_code' => 'law',
            'items' => [
                ['module_code' => 'processos-advocacia', 'quantity' => 1],
                ['module_code' => 'contatos-advocacia', 'quantity' => 1],
                ['module_code' => 'tarefas-advocacia', 'quantity' => 1],
            ],
            'cycle' => 'monthly',
            'selection_mode' => 'plan',
            'plan_code' => 'law-advocacia',
            'voucher_code' => 'CREDITO500',
        ])->assertCreated();

        $subscriptionId = $response->json('subscription_id');
        $this->assertSame(0, DB::table('voucher_redemptions')->count());
        $this->assertDatabaseHas('voucher_redemption_reservations', [
            'voucher_id' => $voucherId,
            'subscription_id' => $subscriptionId,
            'status' => 'pending',
        ]);

        $timestamp = (string) now()->timestamp;
        $signature = 'ts='.$timestamp.',v1='.hash_hmac('sha256', 'id:preapproval-test;request-id:req-1;ts:'.$timestamp.';', 'test-secret');
        $this->withHeaders(['x-signature' => $signature, 'x-request-id' => 'req-1'])
            ->postJson('/api/webhooks/mercado-pago', ['type' => 'preapproval', 'data' => ['id' => 'preapproval-test']])
            ->assertNoContent();

        $this->assertDatabaseHas('voucher_redemption_reservations', ['subscription_id' => $subscriptionId, 'status' => 'confirmed']);
        $this->assertDatabaseHas('voucher_redemptions', ['voucher_id' => $voucherId, 'subscription_id' => $subscriptionId]);
        $snapshot = json_decode((string) DB::table('voucher_redemptions')->where('subscription_id', $subscriptionId)->value('snapshot'), true);
        $this->assertSame('commercial_credit', $snapshot['discount_type']);
        $this->assertSame('CREDITO500', $snapshot['code']);
        $this->assertSame('law-advocacia', $snapshot['plan_code']);
        $this->assertSame($companyId, $snapshot['company_id']);
        $this->assertNotNull($snapshot['benefit_starts_at']);
        $this->assertNotNull($snapshot['benefit_ends_at']);
    }

    public function test_voucher_bound_to_plan_cannot_be_used_on_another_plan(): void
    {
        [$user, $companyId] = $this->customerContext();
        $admin = $this->platformAdmin();
        $product = DB::table('products')->where('code', 'law')->first();
        $plan = DB::table('plans')->where('product_id', $product->id)->where('code', 'law-advocacia')->first();
        DB::table('vouchers')->insert([
            'id' => PrefixedUlid::make('VCH'), 'code' => 'PLANONLY', 'name' => 'Plano específico',
            'discount_type' => 'percentage', 'discount_value' => 10, 'product_id' => $product->id, 'plan_id' => $plan->id,
            'status' => 'ativa', 'created_by_platform_admin_id' => $admin->id, 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAs($user)->withSession(['active_company_id' => $companyId])->postJson('/api/subscriptions/checkout', [
            'product_code' => 'law',
            'items' => [['module_code' => 'processos-advocacia', 'quantity' => 1]],
            'cycle' => 'monthly', 'selection_mode' => 'modules', 'voucher_code' => 'PLANONLY',
        ])->assertUnprocessable()->assertJsonPath('message', 'Voucher não é elegível para este plano.');
    }

    public function test_all_voucher_discount_types_are_supported_and_capped(): void
    {
        $manager = new VoucherManager();
        $this->assertSame(100.0, $manager->discount((object) ['discount_type' => 'trial_free', 'discount_value' => 100], 100));
        $this->assertSame(25.0, $manager->discount((object) ['discount_type' => 'percentage', 'discount_value' => 25], 100));
        $this->assertSame(40.0, $manager->discount((object) ['discount_type' => 'fixed', 'discount_value' => 40], 100));
        $this->assertSame(100.0, $manager->discount((object) ['discount_type' => 'commercial_credit', 'discount_value' => 150], 100));
    }

    private function customerContext(): array
    {
        $user = User::create([
            'id' => PrefixedUlid::make('USR'), 'name' => 'Cliente Teste', 'cpf' => '11144477735',
            'email' => 'cliente-'.User::count().'@example.test', 'password' => Hash::make('SenhaSegura!2026'),
            'status' => 'ativa', 'email_verified_at' => now(),
        ]);
        $companyId = PrefixedUlid::make('EMP');
        DB::table('companies')->insert([
            'id' => $companyId, 'document_type' => 'cnpj', 'document_number' => '11222333000181',
            'legal_name' => 'Empresa de Teste', 'status' => 'ativa', 'version' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $roleId = DB::table('roles')->where('code', 'admin')->value('id');
        DB::table('company_memberships')->insert([
            'id' => PrefixedUlid::make('MBR'), 'company_id' => $companyId, 'user_id' => $user->id,
            'role_id' => $roleId, 'status' => 'ativo', 'version' => 1, 'created_by' => $user->id,
            'updated_by' => $user->id, 'created_at' => now(), 'updated_at' => now(),
        ]);

        return [$user, $companyId];
    }

    private function platformAdmin(): PlatformAdmin
    {
        return PlatformAdmin::create([
            'id' => PrefixedUlid::make('PAD'), 'name' => 'Equipe Fokus',
            'email' => 'plataforma-'.PlatformAdmin::count().'@example.test',
            'password' => Hash::make('SenhaInterna!2026'), 'status' => 'ativo',
            'platform_role_id' => PlatformRole::where('code', 'superadministrador')->value('id'),
            'email_verified_at' => now(),
        ]);
    }
}
