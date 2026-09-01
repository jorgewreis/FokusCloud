<?php

namespace Tests\Feature;

use App\Models\PlatformAdmin;
use App\Models\PlatformRole;
use App\Services\PrefixedUlid;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PlatformSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Mail::fake();
    }

    public function test_commercial_admin_cannot_manage_internal_accounts(): void
    {
        $admin = $this->admin('administrador_comercial');
        $this->actingAs($admin, 'platform')->getJson('/api/backoffice/admins')->assertForbidden();
    }

    public function test_three_password_failures_temporarily_lock_an_internal_account(): void
    {
        $admin = $this->admin();
        for ($attempt = 0; $attempt < 3; $attempt++) {
            $this->postJson('/api/backoffice/auth/login', ['email' => $admin->email, 'password' => 'senha-incorreta'])->assertUnprocessable();
        }
        $this->assertDatabaseHas('platform_admins', ['id' => $admin->id]);
        $this->assertNotNull($admin->fresh()->locked_until);
        $this->assertDatabaseHas('platform_audit_events', ['action' => 'backoffice.account_temporarily_locked']);
    }

    public function test_last_active_superadmin_cannot_be_blocked(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin, 'platform')->postJson("/api/backoffice/admins/{$admin->id}/block", ['reason' => 'Teste'])->assertUnprocessable();
    }

    public function test_invited_admin_activates_with_a_token_and_own_password(): void
    {
        $super = $this->admin();
        $this->actingAs($super, 'platform')->postJson('/api/backoffice/admins/invitations', ['name' => 'Comercial', 'email' => 'comercial@example.test', 'role' => 'administrador_comercial'])->assertCreated();
        $invited = PlatformAdmin::where('email', 'comercial@example.test')->firstOrFail();
        $this->assertSame('suspenso', $invited->status);
    }

    private function admin(string $role = 'superadministrador'): PlatformAdmin
    {
        return PlatformAdmin::create(['id' => PrefixedUlid::make('PAD'), 'name' => 'Equipe Fokus', 'email' => $role === 'superadministrador' ? 'super@example.test' : 'comercial@example.test', 'password' => Hash::make('SenhaInterna!2026'), 'status' => 'ativo', 'platform_role_id' => PlatformRole::where('code', $role)->value('id'), 'email_verified_at' => now()]);
    }
}
