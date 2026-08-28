<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->string('segment', 16)->nullable()->after('product_id');
            $table->enum('status', ['rascunho', 'ativo', 'pausado', 'arquivado'])->default('rascunho')->after('name');
            $table->unsignedInteger('display_order')->default(0)->after('status');
            $table->boolean('featured')->default(false)->after('display_order');
            $table->index(['product_id', 'segment', 'status']);
        });

        Schema::table('modules', function (Blueprint $table) {
            $table->string('context_code', 64)->nullable()->after('product_id');
            $table->string('variant_code', 64)->nullable()->after('context_code');
            $table->enum('status', ['rascunho', 'ativo', 'pausado', 'arquivado'])->default('rascunho')->after('monthly_price');
            $table->boolean('price_is_estimate')->default(false)->after('status');
            $table->index(['product_id', 'context_code', 'variant_code']);
            $table->index(['product_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->dropIndex('modules_product_id_context_code_variant_code_index');
            $table->dropIndex('modules_product_id_status_index');
            $table->dropColumn(['context_code', 'variant_code', 'status', 'price_is_estimate']);
        });

        Schema::table('plans', function (Blueprint $table) {
            $table->dropIndex('plans_product_id_segment_status_index');
            $table->dropColumn(['segment', 'status', 'display_order', 'featured']);
        });
    }
};
