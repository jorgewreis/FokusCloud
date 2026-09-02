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

class CompanyAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_company_query_is_paginated_and_masks_document_and_email(): void
    {
        $admin = $this->platformAdmin();
        $this->companyFixture('Empresa Beta', '98765432000100', 'beta@example.test');

        $this->actingAs($admin, 'platform')->getJson('/api/backoffice/companies?per_page=1')
            ->assertOk()
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('data.0.document_masked', '**.***.***/****-00')
            ->assertJsonPath('data.0.admin_email_masked', 'b***@example.test')
            ->assertJsonMissing(['document_number' => '98765432000100']);
    }

    public function test_user_without_company_permission_cannot_query_companies(): void
    {
        $admin = $this->platformAdmin('administrador_comercial');
        DB::table('platform_role_permissions')->where('platform_role_id', $admin->platform_role_id)
            ->where('platform_permission_id', DB::table('platform_permissions')->where('code', 'platform.companies.view')->value('id'))->delete();

        $this->actingAs($admin, 'platform')->getJson('/api/backoffice/companies')->assertForbidden();
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

    private function companyFixture(string $name, string $document, string $email): string
    {
        $user = User::create([
            'id' => PrefixedUlid::make('USR'),
            'name' => 'Administrador Beta',
            'cpf' => '52998224725',
            'email' => $email,
            'password' => Hash::make('SenhaCliente!2026'),
            'status' => 'ativa',
            'email_verified_at' => now(),
        ]);
        $companyId = PrefixedUlid::make('COM');
        DB::table('companies')->insert([
            'id' => $companyId,
            'document_type' => 'cnpj',
            'document_number' => $document,
            'legal_name' => $name,
            'status' => 'ativa',
            'version' => 1,
            'created_by' => $user->id,
            'updated_by' => $user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('company_memberships')->insert([
            'id' => PrefixedUlid::make('MBS'),
            'company_id' => $companyId,
            'user_id' => $user->id,
            'role_id' => DB::table('roles')->where('code', 'admin')->value('id'),
            'status' => 'ativo',
            'active_admin_company_id' => $companyId,
            'version' => 1,
            'created_by' => $user->id,
            'updated_by' => $user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $companyId;
    }
}
