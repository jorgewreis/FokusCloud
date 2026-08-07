<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\PrefixedUlid;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AuthenticationAndIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Mail::fake();
        Http::fake(['api.pwnedpasswords.com/*' => Http::response('', 200)]);
    }

    public function test_company_registration_rejects_invalid_cpf(): void
    {
        $this->postJson('/api/auth/register-company', $this->registration(['cpf' => '111.111.111-11']))
            ->assertUnprocessable()->assertJsonValidationErrors('cpf');
    }

    public function test_registration_creates_company_user_and_single_admin(): void
    {
        $response = $this->postJson('/api/auth/register-company', $this->registration());
        $response->assertCreated()->assertJsonPath('message', 'Cadastro criado. Confirme seu e-mail antes de escolher a assinatura.');
        $this->assertDatabaseCount('companies', 1);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseHas('company_memberships', ['status' => 'ativo']);
    }

    public function test_existing_company_document_is_not_duplicated(): void
    {
        $this->postJson('/api/auth/register-company', $this->registration())->assertCreated();
        $this->postJson('/api/auth/register-company', $this->registration(['cpf' => '11144477735', 'email' => 'outro@example.test']))
            ->assertConflict();
    }

    public function test_login_blocks_after_five_failed_attempts(): void
    {
        $user = User::create([
            'id' => PrefixedUlid::make('USR'), 'name' => 'Pessoa Teste', 'cpf' => '11144477735',
            'email' => 'pessoa@example.test', 'password' => Hash::make('SenhaSegura!2026'), 'status' => 'ativa', 'email_verified_at' => now(),
        ]);
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->postJson('/api/auth/login', ['cpf' => $user->cpf, 'password' => 'senha-errada'])->assertUnprocessable();
        }
        $this->assertDatabaseHas('users', ['id' => $user->id, 'status' => 'bloqueada']);
    }

    public function test_user_cannot_select_another_company_without_membership(): void
    {
        $user = User::create([
            'id' => PrefixedUlid::make('USR'), 'name' => 'Pessoa Teste', 'cpf' => '11144477735',
            'email' => 'pessoa@example.test', 'password' => Hash::make('SenhaSegura!2026'), 'status' => 'ativa', 'email_verified_at' => now(),
        ]);
        $companyId = PrefixedUlid::make('EMP');
        DB::table('companies')->insert(['id' => $companyId, 'document_type' => 'cnpj', 'document_number' => '11222333000181', 'legal_name' => 'Outra Empresa', 'status' => 'ativa', 'version' => 1, 'created_at' => now(), 'updated_at' => now()]);
        $this->actingAs($user)->postJson('/api/auth/select-company', ['company_id' => $companyId])->assertForbidden();
    }

    private function registration(array $overrides = []): array
    {
        return array_merge([
            'document_type' => 'cnpj', 'document_number' => '11.222.333/0001-81', 'legal_name' => 'Empresa Teste Ltda',
            'name' => 'Administrador Teste', 'cpf' => '11144477735', 'email' => 'admin@example.test',
            'password' => 'SenhaSegura!2026', 'terms_version' => '1.0', 'privacy_version' => '1.0',
        ], $overrides);
    }
}
