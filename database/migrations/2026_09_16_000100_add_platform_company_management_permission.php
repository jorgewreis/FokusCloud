<?php

use App\Services\PrefixedUlid;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $permissionId = DB::table('platform_permissions')->where('code', 'platform.companies.manage')->value('id');
        if (! $permissionId) {
            $permissionId = PrefixedUlid::make('PPM');
            DB::table('platform_permissions')->insert([
                'id' => $permissionId,
                'code' => 'platform.companies.manage',
                'name' => 'Gerir empresas',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $roleId = DB::table('platform_roles')->where('code', 'superadministrador')->value('id');
        if ($roleId && ! DB::table('platform_role_permissions')->where(['platform_role_id' => $roleId, 'platform_permission_id' => $permissionId])->exists()) {
            DB::table('platform_role_permissions')->insert([
                'platform_role_id' => $roleId,
                'platform_permission_id' => $permissionId,
            ]);
        }
    }

    public function down(): void
    {
        $permissionId = DB::table('platform_permissions')->where('code', 'platform.companies.manage')->value('id');
        if (! $permissionId) {
            return;
        }

        DB::table('platform_role_permissions')->where('platform_permission_id', $permissionId)->delete();
        DB::table('platform_permissions')->where('id', $permissionId)->delete();
    }
};
