<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::transaction(function (): void {
            $law = DB::table('products')->where('code', 'law')->first(['id']);

            if (! $law) {
                return;
            }

            $moduleIds = DB::table('modules')
                ->where('product_id', $law->id)
                ->whereNotIn('code', ['processos', 'contatos', 'expedicoes', 'tarefas'])
                ->pluck('id');

            if ($moduleIds->isNotEmpty() && DB::table('subscription_items')->whereIn('module_id', $moduleIds)->exists()) {
                throw new RuntimeException(
                    'O catalogo Law legado possui itens de assinatura dependentes; a recriacao foi interrompida para preservar o historico.'
                );
            }

            $planIds = DB::table('plans')->where('product_id', $law->id)->pluck('id');

            if ($planIds->isNotEmpty()) {
                DB::table('plan_modules')->whereIn('plan_id', $planIds)->delete();
                DB::table('plans')->whereIn('id', $planIds)->delete();
            }

            if ($moduleIds->isNotEmpty()) {
                DB::table('modules')->whereIn('id', $moduleIds)->delete();
            }
        });
    }

    public function down(): void
    {
        // O catalogo anterior nao e restaurado automaticamente: o seeder e a fonte
        // idempotente do catalogo inicial e historico comercial nao deve ser revertido.
    }
};
