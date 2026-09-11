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
        Schema::create('product_interests', function (Blueprint $table): void {
            $table->char('id', 30)->charset('ascii')->collation('ascii_bin')->primary();
            $table->string('name', 160);
            $table->string('email', 255);
            $table->string('products', 120);
            $table->string('profiles', 255)->nullable();
            $table->string('organization', 180)->nullable();
            $table->string('city', 120)->nullable();
            $table->char('state', 2)->nullable();
            $table->string('team_size', 80)->nullable();
            $table->text('current_process')->nullable();
            $table->text('main_difficulties')->nullable();
            $table->text('modules')->nullable();
            $table->string('source_url', 500)->nullable();
            $table->string('privacy_version', 64);
            $table->string('status', 32)->default('novo');
            $table->timestamp('consented_at');
            $table->timestamp('last_submitted_at');
            $table->timestamps();
            $table->unique('email');
            $table->index(['status', 'created_at']);
            $table->index('products');
        });

        $permissionId = PrefixedUlid::make('PPM');
        DB::table('platform_permissions')->insert([
            'id' => $permissionId,
            'code' => 'platform.product_interests.manage',
            'name' => 'Gerir interesses de produtos',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $roles = DB::table('platform_roles')->whereIn('code', ['superadministrador', 'administrador_comercial'])->pluck('id');
        foreach ($roles as $roleId) {
            DB::table('platform_role_permissions')->insert([
                'platform_role_id' => $roleId,
                'platform_permission_id' => $permissionId,
            ]);
        }
    }

    public function down(): void
    {
        $permissionId = DB::table('platform_permissions')->where('code', 'platform.product_interests.manage')->value('id');
        if ($permissionId) {
            DB::table('platform_role_permissions')->where('platform_permission_id', $permissionId)->delete();
            DB::table('platform_permissions')->where('id', $permissionId)->delete();
        }
        Schema::dropIfExists('product_interests');
    }
};
