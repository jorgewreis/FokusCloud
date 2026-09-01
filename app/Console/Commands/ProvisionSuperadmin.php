<?php

namespace App\Console\Commands;

use App\Models\PlatformAdmin;
use App\Models\PlatformRole;
use App\Services\PrefixedUlid;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class ProvisionSuperadmin extends Command
{
    protected $signature = 'fokus:provision-superadmin';

    protected $description = 'Cria, uma única vez, o superadministrador configurado no ambiente.';

    public function handle(): int
    {
        $name = env('FOKUS_INITIAL_SUPERADMIN_NAME');
        $email = strtolower((string) env('FOKUS_INITIAL_SUPERADMIN_EMAIL'));
        $password = env('FOKUS_INITIAL_SUPERADMIN_PASSWORD');
        if (! $name || ! filter_var($email, FILTER_VALIDATE_EMAIL) || ! $password || strlen($password) < 12) {
            $this->error('Defina FOKUS_INITIAL_SUPERADMIN_NAME, EMAIL e PASSWORD (mínimo 12 caracteres).');

            return self::FAILURE;
        }
        if (PlatformAdmin::where('email', $email)->exists()) {
            $this->warn('O superadministrador já está provisionado.');

            return self::SUCCESS;
        }
        $roleId = PlatformRole::where('code', 'superadministrador')->value('id');
        if (! $roleId) {
            $this->error('Execute as migrations da seguranca interna antes de provisionar o superadministrador.');

            return self::FAILURE;
        }
        PlatformAdmin::create([
            'id' => PrefixedUlid::make('PAD'), 'name' => $name, 'email' => $email,
            'password' => Hash::make($password), 'status' => 'ativo', 'platform_role_id' => $roleId, 'email_verified_at' => now(),
        ]);
        $this->info('Superadministrador criado. Remova a senha inicial do ambiente após o primeiro acesso.');

        return self::SUCCESS;
    }
}
