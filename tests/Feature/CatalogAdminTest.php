<?php

namespace Tests\Feature;

use App\Models\PlatformAdmin;
use App\Models\PlatformRole;
use App\Services\PrefixedUlid;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CatalogAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Mail::fake();
    }

    public function test_commercial_admin_can_create_drafts_but_cannot_publish_catalog(): void
    {
        $admin = $this->admin('administrador_comercial');

        $this->actingAs($admin, 'platform')->postJson('/api/backoffice/catalog/products', [
            'code' => 'academy',
            'name' => 'Fokus Cloud Academy',
            'status' => 'ativo',
        ])->assertCreated();

        $productId = DB::table('products')->where('code', 'academy')->value('id');
        $this->assertDatabaseHas('products', [
            'id' => $productId,
            'publication_state' => 'rascunho',
        ]);

        $this->actingAs($admin, 'platform')->postJson("/api/backoffice/catalog/{$productId}/publish", [
            'reason' => 'Tentativa comercial.',
        ])->assertForbidden();
    }

    public function test_superadmin_publishes_a_versioned_public_catalog_snapshot(): void
    {
        $admin = $this->admin();
        $productId = DB::table('products')->where('code', 'law')->value('id');

        $this->actingAs($admin, 'platform')->postJson("/api/backoffice/catalog/{$productId}/publish", [
            'reason' => 'Publicação homologada do Marco 3.',
        ])->assertOk()->assertJsonPath('version', 2);

        $this->assertDatabaseHas('catalog_publications', [
            'product_id' => $productId,
            'version' => 2,
            'published_by_platform_admin_id' => $admin->id,
        ]);
        $this->assertDatabaseHas('platform_audit_events', ['action' => 'backoffice.catalog_published']);

        $this->getJson('/api/catalog/law')
            ->assertOk()
            ->assertJsonPath('contract_version', '0.0.3')
            ->assertJsonPath('published_version', 2)
            ->assertJsonStructure(['product', 'modules', 'plans', 'published_at']);
    }

    public function test_publication_refuses_active_plan_without_modules(): void
    {
        $admin = $this->admin();
        $productId = DB::table('products')->where('code', 'law')->value('id');

        $this->actingAs($admin, 'platform')->postJson('/api/backoffice/catalog/plans', [
            'product_id' => $productId,
            'code' => 'law-sem-modulos',
            'name' => 'Sem módulos',
            'status' => 'ativo',
        ])->assertCreated();

        $this->actingAs($admin, 'platform')->postJson("/api/backoffice/catalog/{$productId}/publish", [
            'reason' => 'Deve falhar.',
        ])->assertUnprocessable();
    }

    public function test_public_catalog_keeps_the_last_snapshot_when_a_plan_is_changed_as_draft(): void
    {
        $admin = $this->admin();
        $plan = DB::table('plans')->where('code', 'law-advocacia')->first();
        $before = $this->getJson('/api/catalog/law')->assertOk()->json();

        $this->actingAs($admin, 'platform')->patchJson("/api/backoffice/catalog/plans/{$plan->id}", [
            'name' => 'Advocacia Alterada',
            'status' => 'ativo',
        ])->assertOk();

        $after = $this->getJson('/api/catalog/law')->assertOk()->json();
        $this->assertSame($before['published_version'], $after['published_version']);
        $this->assertContains('Fokus Cloud Law - Advocacia', collect($after['plans'])->pluck('name')->all());
        $this->assertNotContains('Fokus Cloud Law - Advocacia Alterada', collect($after['plans'])->pluck('name')->all());
    }

    public function test_superadmin_can_pause_public_items_but_commercial_admin_cannot(): void
    {
        $commercial = $this->admin('administrador_comercial');
        $super = $this->admin();
        $planId = DB::table('plans')->where('code', 'law-cartorio-criminal')->value('id');

        $this->actingAs($commercial, 'platform')->postJson("/api/backoffice/catalog/plans/{$planId}/pause", [
            'reason' => 'Sem permissão.',
        ])->assertForbidden();

        $this->actingAs($super, 'platform')->postJson("/api/backoffice/catalog/plans/{$planId}/pause", [
            'reason' => 'Pausa homologada.',
        ])->assertOk();

        $this->assertDatabaseHas('plans', [
            'id' => $planId,
            'status' => 'inativo',
            'publication_state' => 'pausado',
        ]);
    }

    private function admin(string $role = 'superadministrador'): PlatformAdmin
    {
        return PlatformAdmin::create([
            'id' => PrefixedUlid::make('PAD'),
            'name' => 'Equipe Fokus',
            'email' => $role.PlatformAdmin::count().'@example.test',
            'password' => Hash::make('SenhaInterna!2026'),
            'status' => 'ativo',
            'platform_role_id' => PlatformRole::where('code', $role)->value('id'),
            'email_verified_at' => now(),
        ]);
    }
}
