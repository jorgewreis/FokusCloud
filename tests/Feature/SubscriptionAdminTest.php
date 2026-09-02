<?php

namespace Tests\Feature;

use App\Models\PlatformAdmin;
use App\Models\PlatformRole;
use App\Models\User;
use App\Services\PrefixedUlid;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SubscriptionAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_internal_admin_can_list_masked_companies_and_subscription_detail(): void
    {
        $admin = $this->platformAdmin();
        $fixture = $this->subscriptionFixture();

        $this->actingAs($admin, 'platform')->getJson('/api/backoffice/companies')
            ->assertOk()
            ->assertJsonPath('data.0.legal_name', 'Empresa Alpha')
            ->assertJsonPath('data.0.document_masked', '**.***.***/****-00')
            ->assertJsonMissing(['document_number' => '12345678000100']);

        $this->actingAs($admin, 'platform')->getJson('/api/backoffice/subscriptions/'.$fixture['subscription_id'])
            ->assertOk()
            ->assertJsonPath('company_name', 'Empresa Alpha')
            ->assertJsonPath('items.0.name', 'Gestão de Processos para Advogados')
            ->assertJsonPath('payments.0.status', 'aguardando_pagamento')
            ->assertJsonPath('history', []);
    }

    public function test_admin_can_pause_reactivate_and_schedule_cancellation_with_audit(): void
    {
        $admin = $this->platformAdmin();
        $fixture = $this->subscriptionFixture();

        $this->actingAs($admin, 'platform')->patchJson('/api/backoffice/subscriptions/'.$fixture['subscription_id'], [
            'action' => 'suspensao',
            'reason' => 'Revisão comercial solicitada.',
        ])->assertOk()->assertJsonPath('status', 'aplicada');
        $this->assertDatabaseHas('subscriptions', ['id' => $fixture['subscription_id'], 'status' => 'suspensa']);

        $this->actingAs($admin, 'platform')->patchJson('/api/backoffice/subscriptions/'.$fixture['subscription_id'], [
            'action' => 'reativacao',
            'reason' => 'Revisão concluída.',
        ])->assertOk();
        $this->assertDatabaseHas('subscriptions', ['id' => $fixture['subscription_id'], 'status' => 'ativa']);

        $this->actingAs($admin, 'platform')->patchJson('/api/backoffice/subscriptions/'.$fixture['subscription_id'], [
            'action' => 'cancelamento',
            'reason' => 'Encerramento solicitado pelo cliente.',
        ])->assertOk()->assertJsonPath('status', 'aplicada');
        $this->assertDatabaseHas('subscriptions', ['id' => $fixture['subscription_id'], 'status' => 'cancelamento_agendado']);
        $this->assertDatabaseCount('subscription_changes', 3);
        $this->assertDatabaseHas('platform_audit_events', ['action' => 'backoffice.subscription_cancelamento']);
    }

    public function test_upgrade_uses_published_plan_and_waits_for_payment(): void
    {
        $admin = $this->platformAdmin();
        $fixture = $this->subscriptionFixture();
        $targetPlanId = DB::table('plans')->where('code', 'law-cartorio-criminal')->value('id');

        $this->actingAs($admin, 'platform')->patchJson('/api/backoffice/subscriptions/'.$fixture['subscription_id'], [
            'action' => 'upgrade',
            'reason' => 'Ampliação contratada.',
            'target_plan_id' => $targetPlanId,
            'billing_cycle' => 'annual',
            'amount' => 0,
        ])->assertOk()->assertJsonPath('status', 'aguardando_pagamento');

        $this->assertDatabaseHas('subscriptions', ['id' => $fixture['subscription_id'], 'status' => 'ativa']);
        $this->assertDatabaseHas('subscription_changes', ['subscription_id' => $fixture['subscription_id'], 'status' => 'aguardando_pagamento']);
        $snapshot = json_decode((string) DB::table('subscription_changes')->where('subscription_id', $fixture['subscription_id'])->value('after_snapshot'), true);
        $this->assertSame('law-cartorio-criminal', $snapshot['plan_code']);
        $this->assertSame('annual', $snapshot['billing_cycle']);
    }

    public function test_downgrade_is_scheduled_and_command_applies_it_at_period_end(): void
    {
        $admin = $this->platformAdmin();
        $fixture = $this->subscriptionFixture(['period_ends_at' => now()->subMinute()]);
        $targetPlanId = DB::table('plans')->where('code', 'law-cartorio-criminal')->value('id');

        $this->actingAs($admin, 'platform')->patchJson('/api/backoffice/subscriptions/'.$fixture['subscription_id'], [
            'action' => 'downgrade',
            'reason' => 'Redução de escopo.',
            'target_plan_id' => $targetPlanId,
        ])->assertOk()->assertJsonPath('status', 'agendada');

        $this->artisan('fokus:apply-subscription-changes')->expectsOutput('Alterações aplicadas: 1')->assertSuccessful();
        $this->assertDatabaseHas('subscriptions', ['id' => $fixture['subscription_id'], 'status' => 'ativa']);
        $this->assertDatabaseHas('subscription_changes', ['subscription_id' => $fixture['subscription_id'], 'status' => 'aplicada']);
        $this->assertDatabaseHas('subscription_items', ['subscription_id' => $fixture['subscription_id'], 'name_snapshot' => 'Gestão de Processos para Varas Criminais', 'deleted_at' => null]);
    }

    public function test_override_is_restricted_to_superadmin_and_records_before_after_snapshots(): void
    {
        $commercial = $this->platformAdmin('administrador_comercial');
        $superadmin = $this->platformAdmin();
        $fixture = $this->subscriptionFixture();
        $payload = ['action' => 'override', 'reason' => 'Acordo comercial aprovado.', 'override' => ['monthly_amount' => 499.90, 'billing_cycle' => 'monthly']];

        $this->actingAs($commercial, 'platform')->patchJson('/api/backoffice/subscriptions/'.$fixture['subscription_id'], $payload)->assertForbidden();
        $this->actingAs($superadmin, 'platform')->patchJson('/api/backoffice/subscriptions/'.$fixture['subscription_id'], $payload)->assertOk();

        $change = DB::table('subscription_changes')->where('subscription_id', $fixture['subscription_id'])->where('type', 'override')->first();
        $this->assertNotNull($change);
        $this->assertSame(499.90, (float) json_decode($change->after_snapshot, true)['monthly_amount']);
        $this->assertNotSame($change->before_snapshot, $change->after_snapshot);
        $this->assertDatabaseHas('subscriptions', ['id' => $fixture['subscription_id'], 'status' => 'ativa']);
    }

    private function platformAdmin(string $role = 'superadministrador'): PlatformAdmin
    {
        return PlatformAdmin::create([
            'id' => PrefixedUlid::make('PAD'),
            'name' => 'Equipe Fokus',
            'email' => $role.'-'.PlatformAdmin::count().'@example.test',
            'password' => Hash::make('SenhaInterna!2026'),
            'status' => 'ativo',
            'platform_role_id' => PlatformRole::where('code', $role)->value('id'),
            'email_verified_at' => now(),
        ]);
    }

    private function subscriptionFixture(array $options = []): array
    {
        $product = DB::table('products')->where('code', 'law')->first();
        $module = DB::table('modules')->where('code', 'processos-advocacia')->first();
        $user = User::create(['id' => PrefixedUlid::make('USR'), 'name' => 'Administrador Alpha', 'cpf' => '52998224725', 'email' => 'cliente-'.User::count().'@example.test', 'password' => Hash::make('SenhaCliente!2026'), 'status' => 'ativa', 'email_verified_at' => now()]);
        $companyId = PrefixedUlid::make('COM');
        DB::table('companies')->insert(['id' => $companyId, 'document_type' => 'cnpj', 'document_number' => '12345678000100', 'legal_name' => 'Empresa Alpha', 'status' => 'ativa', 'version' => 1, 'created_by' => $user->id, 'updated_by' => $user->id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('company_memberships')->insert(['id' => PrefixedUlid::make('MBS'), 'company_id' => $companyId, 'user_id' => $user->id, 'role_id' => DB::table('roles')->where('code', 'admin')->value('id'), 'status' => 'ativo', 'active_admin_company_id' => $companyId, 'version' => 1, 'created_by' => $user->id, 'updated_by' => $user->id, 'created_at' => now(), 'updated_at' => now()]);
        $subscriptionId = PrefixedUlid::make('ASS');
        $endsAt = $options['period_ends_at'] ?? now()->addMonth();
        $snapshot = ['plan_id' => DB::table('plans')->where('code', 'law-advocacia')->value('id'), 'plan_code' => 'law-advocacia', 'plan_name' => 'Advocacia', 'billing_cycle' => 'monthly', 'monthly_amount' => 64.70, 'amount' => 64.70, 'current_period_starts_at' => now()->toISOString(), 'current_period_ends_at' => $endsAt->toISOString(), 'status' => 'ativa', 'items' => [['module_id' => $module->id, 'name' => $module->name, 'quantity' => 1, 'unit_price' => (float) $module->monthly_price, 'conditions' => ['plan_code' => 'law-advocacia']]]];
        DB::table('subscriptions')->insert(['id' => $subscriptionId, 'company_id' => $companyId, 'product_id' => $product->id, 'status' => 'ativa', 'open_company_product' => $companyId.'-'.$product->id, 'version' => 1, 'billing_cycle' => 'monthly', 'current_period_starts_at' => now(), 'current_period_ends_at' => $endsAt, 'commercial_snapshot' => json_encode($snapshot), 'created_by' => $user->id, 'updated_by' => $user->id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('subscription_items')->insert(['id' => PrefixedUlid::make('ITM'), 'company_id' => $companyId, 'subscription_id' => $subscriptionId, 'module_id' => $module->id, 'name_snapshot' => $module->name, 'quantity' => 1, 'unit_price_snapshot' => $module->monthly_price, 'conditions_snapshot' => json_encode(['plan_code' => 'law-advocacia']), 'version' => 1, 'created_by' => $user->id, 'updated_by' => $user->id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('payments')->insert(['id' => PrefixedUlid::make('PAG'), 'company_id' => $companyId, 'subscription_id' => $subscriptionId, 'provider' => 'mercado_pago', 'status' => 'aguardando_pagamento', 'amount' => 64.70, 'currency' => 'BRL', 'provider_payload_sanitized' => json_encode(['test' => true]), 'version' => 1, 'created_by' => $user->id, 'updated_by' => $user->id, 'created_at' => now(), 'updated_at' => now()]);

        return ['company_id' => $companyId, 'subscription_id' => $subscriptionId];
    }
}
