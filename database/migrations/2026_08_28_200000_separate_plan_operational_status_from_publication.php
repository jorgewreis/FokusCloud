<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->enum('publication_state', ['rascunho', 'publicado', 'pausado', 'arquivado'])->default('rascunho')->after('status');
            $table->enum('status', ['rascunho', 'ativo', 'inativo', 'pausado', 'arquivado'])->default('rascunho')->change();
            $table->index(['product_id', 'publication_state', 'status']);
        });

        DB::table('plans')->orderBy('id')->each(function (object $plan): void {
            $publicationState = match ($plan->status) {
                'ativo' => 'publicado',
                'pausado', 'inativo' => 'pausado',
                'arquivado' => 'arquivado',
                default => 'rascunho',
            };

            DB::table('plans')->where('id', $plan->id)->update([
                'status' => $plan->status === 'ativo' ? 'ativo' : 'inativo',
                'publication_state' => $publicationState,
            ]);
        });

        Schema::table('plans', function (Blueprint $table) {
            $table->enum('status', ['ativo', 'inativo'])->default('inativo')->change();
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->enum('status', ['rascunho', 'ativo', 'inativo', 'pausado', 'arquivado'])->default('rascunho')->change();
        });

        DB::table('plans')->orderBy('id')->each(function (object $plan): void {
            $legacyStatus = $plan->publication_state === 'publicado' && $plan->status === 'ativo'
                ? 'ativo'
                : $plan->publication_state;

            DB::table('plans')->where('id', $plan->id)->update(['status' => $legacyStatus]);
        });

        Schema::table('plans', function (Blueprint $table) {
            $table->dropIndex('plans_product_id_publication_state_status_index');
            $table->dropColumn('publication_state');
            $table->enum('status', ['rascunho', 'ativo', 'pausado', 'arquivado'])->default('rascunho')->change();
        });
    }
};
