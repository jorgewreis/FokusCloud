<?php

namespace Tests\Feature;

use App\Models\PlatformAdmin;
use App\Models\PlatformRole;
use App\Services\PrefixedUlid;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class BackofficeSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Mail::fake();
    }

    public function test_customer_session_cannot_access_backoffice(): void
    {
        $this->getJson('/api/backoffice/dashboard')->assertUnauthorized();
    }

    public function test_backoffice_login_requires_email_mfa_after_password(): void
    {
        $admin = $this->admin();
        $this->postJson('/api/backoffice/auth/login', ['email' => $admin->email, 'password' => 'SenhaInterna!2026'])
            ->assertOk()->assertJsonPath('mfa_required', true);
        $this->assertDatabaseHas('platform_login_challenges', ['platform_admin_id' => $admin->id]);
    }

    public function test_backoffice_access_uses_a_dedicated_internal_login_page(): void
    {
        $this->get('/acesso')->assertRedirect('/?acesso=cliente');
        $this->get('/backoffice/acesso')->assertOk();
    }

    public function test_usage_integration_requires_shared_secret(): void
    {
        $this->postJson('/api/integrations/usage', [])->assertUnauthorized();
    }

    public function test_superadmin_can_create_a_voucher(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin, 'platform')->postJson('/api/backoffice/vouchers', ['code' => 'BEMVINDO10', 'discount_type' => 'percentage', 'discount_value' => 10])
            ->assertCreated();
        $this->assertDatabaseHas('vouchers', ['code' => 'BEMVINDO10']);
        $this->assertDatabaseHas('platform_audit_events', ['action' => 'backoffice.voucher_created']);
    }

    public function test_superadmin_can_create_a_complete_plan_voucher(): void
    {
        $admin = $this->admin();
        $catalog = $this->actingAs($admin, 'platform')->getJson('/api/backoffice/catalog')
            ->assertOk()
            ->json('products');

        $product = collect($catalog)->firstWhere('code', 'law');
        $plan = collect($product['plans'])->firstWhere('code', 'law-cartorio-criminal');

        $this->actingAs($admin, 'platform')->postJson('/api/backoffice/vouchers', [
            'name' => 'Campanha anual FokusLaw',
            'code' => 'LAW20',
            'discount_type' => 'percentage',
            'discount_value' => 20,
            'product_id' => $product['id'],
            'plan_id' => $plan['id'],
            'base_amount' => $plan['monthly_amount'],
            'benefit_duration' => 'm3',
            'redemption_limit' => 100,
            'redemption_limit_per_company' => 1,
            'starts_at' => now()->toDateString(),
            'ends_at' => now()->addMonths(3)->toDateString(),
            'status' => 'ativa',
            'origin' => 'Lançamento',
            'notes' => 'Voucher promocional inicial.',
        ])->assertCreated();

        $this->assertDatabaseHas('vouchers', [
            'code' => 'LAW20',
            'name' => 'Campanha anual FokusLaw',
            'plan_id' => $plan['id'],
            'benefit_duration' => 'm3',
            'origin' => 'Lançamento',
        ]);
    }

    private function admin(): PlatformAdmin
    {
        return PlatformAdmin::create(['id' => PrefixedUlid::make('PAD'), 'name' => 'Equipe Fokus', 'email' => 'interno@example.test', 'password' => Hash::make('SenhaInterna!2026'), 'status' => 'ativo', 'platform_role_id' => PlatformRole::where('code', 'superadministrador')->value('id'), 'email_verified_at' => now()]);
    }
}
