<?php

use App\Services\PrefixedUlid;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_roles', function (Blueprint $table): void {
            $table->char('id', 30)->charset('ascii')->collation('ascii_bin')->primary();
            $table->string('code', 64)->unique();
            $table->string('name', 120);
            $table->timestamps();
        });

        Schema::create('platform_permissions', function (Blueprint $table): void {
            $table->char('id', 30)->charset('ascii')->collation('ascii_bin')->primary();
            $table->string('code', 100)->unique();
            $table->string('name', 160);
            $table->timestamps();
        });

        Schema::create('platform_role_permissions', function (Blueprint $table): void {
            $table->char('platform_role_id', 30)->charset('ascii')->collation('ascii_bin');
            $table->char('platform_permission_id', 30)->charset('ascii')->collation('ascii_bin');
            $table->primary(['platform_role_id', 'platform_permission_id'], 'prp_role_permission_pk');
            $table->foreign('platform_role_id')->references('id')->on('platform_roles')->cascadeOnDelete();
            $table->foreign('platform_permission_id')->references('id')->on('platform_permissions')->cascadeOnDelete();
        });

        Schema::table('platform_admins', function (Blueprint $table): void {
            $table->char('platform_role_id', 30)->charset('ascii')->collation('ascii_bin')->nullable()->after('password');
            $table->unsignedInteger('failed_login_count')->default(0)->after('last_login_at');
            $table->timestamp('failed_login_window_started_at')->nullable()->after('failed_login_count');
            $table->timestamp('locked_until')->nullable()->after('failed_login_window_started_at');
            $table->timestamp('manual_blocked_at')->nullable()->after('locked_until');
            $table->char('manual_blocked_by', 30)->charset('ascii')->collation('ascii_bin')->nullable()->after('manual_blocked_at');
            $table->string('blocked_reason', 1000)->nullable()->after('manual_blocked_by');
            $table->timestamp('deactivated_at')->nullable()->after('blocked_reason');
            $table->foreign('platform_role_id')->references('id')->on('platform_roles')->restrictOnDelete();
        });

        Schema::table('platform_login_challenges', function (Blueprint $table): void {
            $table->unsignedTinyInteger('attempt_count')->default(0)->after('code_hash');
            $table->timestamp('resend_available_at')->nullable()->after('expires_at');
        });

        Schema::create('platform_login_attempts', function (Blueprint $table): void {
            $table->char('id', 30)->charset('ascii')->collation('ascii_bin')->primary();
            $table->char('platform_admin_id', 30)->charset('ascii')->collation('ascii_bin')->nullable();
            $table->char('email_hash', 64)->charset('ascii')->collation('ascii_bin');
            $table->string('ip_address', 45)->nullable();
            $table->char('device_hash', 64)->charset('ascii')->collation('ascii_bin')->nullable();
            $table->string('outcome', 40);
            $table->timestamp('created_at');
            $table->foreign('platform_admin_id')->references('id')->on('platform_admins')->nullOnDelete();
            $table->index(['platform_admin_id', 'created_at'], 'pla_admin_created_idx');
            $table->index(['email_hash', 'created_at'], 'pla_email_created_idx');
            $table->index(['ip_address', 'created_at'], 'pla_ip_created_idx');
            $table->index(['device_hash', 'created_at'], 'pla_device_created_idx');
        });

        Schema::create('platform_admin_invitations', function (Blueprint $table): void {
            $table->char('id', 30)->charset('ascii')->collation('ascii_bin')->primary();
            $table->char('platform_admin_id', 30)->charset('ascii')->collation('ascii_bin');
            $table->char('invited_by_platform_admin_id', 30)->charset('ascii')->collation('ascii_bin');
            $table->char('token_hash', 64)->charset('ascii')->collation('ascii_bin')->unique();
            $table->timestamp('expires_at');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();
            $table->foreign('platform_admin_id')->references('id')->on('platform_admins')->cascadeOnDelete();
            $table->foreign('invited_by_platform_admin_id')->references('id')->on('platform_admins')->restrictOnDelete();
            $table->index(['platform_admin_id', 'expires_at'], 'pai_admin_expires_idx');
        });

        Schema::table('platform_audit_events', function (Blueprint $table): void {
            $table->json('before_masked')->nullable()->after('metadata');
            $table->json('after_masked')->nullable()->after('before_masked');
            $table->timestamp('expires_at')->nullable()->after('created_at');
            $table->index('expires_at', 'pae_expires_idx');
        });

        $roles = [
            'superadministrador' => 'Superadministrador',
            'administrador_comercial' => 'Administrador comercial',
        ];
        foreach ($roles as $code => $name) {
            DB::table('platform_roles')->insert(['id' => PrefixedUlid::make('PRL'), 'code' => $code, 'name' => $name, 'created_at' => now(), 'updated_at' => now()]);
        }

        $permissions = [
            'platform.access' => 'Acessar o Backoffice',
            'platform.dashboard.view' => 'Consultar painel',
            'platform.catalog.manage' => 'Gerir catalogo',
            'platform.companies.view' => 'Consultar empresas',
            'platform.subscriptions.manage' => 'Gerir assinaturas',
            'platform.vouchers.manage' => 'Gerir vouchers',
            'platform.audit.view_commercial' => 'Consultar auditoria comercial',
            'platform.audit.view_all' => 'Consultar auditoria completa',
            'platform.security.manage' => 'Gerir seguranca e administradores',
            'platform.catalog.publish' => 'Publicar catalogo',
            'platform.commercial.override' => 'Executar override comercial',
            'platform.reconciliation.manage' => 'Corrigir conciliacao',
        ];
        foreach ($permissions as $code => $name) {
            DB::table('platform_permissions')->insert(['id' => PrefixedUlid::make('PPM'), 'code' => $code, 'name' => $name, 'created_at' => now(), 'updated_at' => now()]);
        }

        $superRoleId = DB::table('platform_roles')->where('code', 'superadministrador')->value('id');
        $commercialRoleId = DB::table('platform_roles')->where('code', 'administrador_comercial')->value('id');
        $permissionIds = DB::table('platform_permissions')->pluck('id', 'code');
        foreach ($permissionIds as $permissionId) {
            DB::table('platform_role_permissions')->insert(['platform_role_id' => $superRoleId, 'platform_permission_id' => $permissionId]);
        }
        foreach (['platform.access', 'platform.dashboard.view', 'platform.catalog.manage', 'platform.companies.view', 'platform.subscriptions.manage', 'platform.vouchers.manage', 'platform.audit.view_commercial'] as $permission) {
            DB::table('platform_role_permissions')->insert(['platform_role_id' => $commercialRoleId, 'platform_permission_id' => $permissionIds[$permission]]);
        }

        DB::table('platform_admins')->update(['platform_role_id' => $superRoleId]);
    }

    public function down(): void
    {
        Schema::table('platform_audit_events', function (Blueprint $table): void {
            $table->dropIndex('pae_expires_idx');
            $table->dropColumn(['before_masked', 'after_masked', 'expires_at']);
        });
        Schema::dropIfExists('platform_admin_invitations');
        Schema::dropIfExists('platform_login_attempts');
        Schema::table('platform_login_challenges', function (Blueprint $table): void {
            $table->dropColumn(['attempt_count', 'resend_available_at']);
        });
        Schema::table('platform_admins', function (Blueprint $table): void {
            $table->dropForeign(['platform_role_id']);
            $table->dropColumn(['platform_role_id', 'failed_login_count', 'failed_login_window_started_at', 'locked_until', 'manual_blocked_at', 'manual_blocked_by', 'blocked_reason', 'deactivated_at']);
        });
        Schema::dropIfExists('platform_role_permissions');
        Schema::dropIfExists('platform_permissions');
        Schema::dropIfExists('platform_roles');
    }
};
