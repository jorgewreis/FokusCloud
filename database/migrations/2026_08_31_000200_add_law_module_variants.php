<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('modules', function (Blueprint $table): void {
            $table->string('module_code', 64)->nullable()->after('code');
            $table->string('segment_code', 32)->nullable()->after('product_id');
            $table->json('capabilities')->nullable()->after('price_is_estimate');
            $table->json('dependencies')->nullable()->after('capabilities');
            $table->json('incompatibilities')->nullable()->after('dependencies');
            $table->index(['product_id', 'module_code', 'segment_code', 'context_code']);
        });
    }

    public function down(): void
    {
        Schema::table('modules', function (Blueprint $table): void {
            $table->dropIndex('modules_product_id_module_code_segment_code_context_code_index');
            $table->dropColumn(['module_code', 'segment_code', 'capabilities', 'dependencies', 'incompatibilities']);
        });
    }
};
