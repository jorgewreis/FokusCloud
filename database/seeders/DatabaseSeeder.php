<?php

namespace Database\Seeders;

use App\Services\PrefixedUlid;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['admin' => 'Administrador', 'gestor' => 'Gestor', 'usuario' => 'Usuário'] as $code => $name) {
            $this->upsertCatalog('roles', ['code' => $code], ['name' => $name], 'PFL');
        }

        foreach (['law' => 'Fokus Cloud Law', 'lead' => 'Fokus Cloud Lead'] as $code => $name) {
            $this->upsertCatalog('products', ['code' => $code], ['name' => $name, 'active' => true], 'PRD');
        }

        $modules = [
            'law' => [
                'processos' => ['Gestão de Processos', 29.90, 'law', 'processos', false],
                'contatos' => ['Gestão de Contatos', 14.90, 'law', 'contatos', false],
                'expedicoes' => ['Gestão de Expedições', 19.90, 'law', 'expedicoes', false],
                'tarefas' => ['Gestão de Tarefas', 19.90, 'law', 'tarefas', false],
            ],
            'lead' => [
                'pessoas' => ['Gestão de Pessoas', 4.90, 'one,team', 'pessoas', false],
                'empreendimentos' => ['Gestão de Empreendimentos', 4.90, 'one,team', 'empreendimentos', false],
                'imoveis' => ['Gestão de Imóveis', 4.90, 'one,team', 'imoveis', false],
                'leads' => ['Gestão de Leads', 4.90, 'one,team', 'leads', true],
                'funil' => ['Funil de Vendas', 4.90, 'one,team', 'funil', false],
                'relatorios' => ['Emissão de Relatórios de Transações', 9.90, 'one,team', 'relatorios', false],
                'whatsapp' => ['Integração com WhatsApp', 9.90, 'one,team', 'whatsapp', false],
                'website' => ['Integração com Website', 14.90, 'one,team', 'website', false],
                'portal-imoveis' => ['Portal de Imóveis', 14.90, 'team', 'portal-imoveis', true],
                'equipes' => ['Gestão de Equipes', 9.90, 'team', 'equipes', true],
                'colaboracao' => ['Colaboração entre Corretores', 9.90, 'team', 'colaboracao', true],
                'permissoes' => ['Permissões por Função', 9.90, 'team', 'permissoes', true],
                'distribuicao-leads' => ['Distribuição de Leads', 9.90, 'team', 'distribuicao-leads', true],
                'visao-gerencial' => ['Visão Gerencial', 14.90, 'team', 'visao-gerencial', true],
                'filiais' => ['Gestão de Filiais', 14.90, 'team', 'filiais', true],
                'relatorios-gerenciais' => ['Relatórios Gerenciais', 14.90, 'team', 'relatorios-gerenciais', true],
                'notificacoes' => ['Notificações de Equipe', 4.90, 'team', 'notificacoes', true],
            ],
        ];

        foreach ($modules as $productCode => $items) {
            $productId = DB::table('products')->where('code', $productCode)->value('id');
            foreach ($items as $code => [$name, $price, $context, $variant, $estimate]) {
                $this->upsertCatalog('modules', ['product_id' => $productId, 'code' => $code], ['name' => $name, 'monthly_price' => $price], 'MOD');
                DB::table('modules')->where('product_id', $productId)->where('code', $code)->update([
                    'context_code' => $context,
                    'variant_code' => $variant,
                    'status' => 'rascunho',
                    'price_is_estimate' => $estimate,
                    'updated_at' => now(),
                ]);
            }
        }

        $plans = [
            'law' => [
                'law-advocacia' => ['Advocacia', null, ['processos', 'contatos', 'tarefas']],
                'law-cartorio-criminal' => ['Cartório Criminal', null, ['processos', 'contatos', 'expedicoes', 'tarefas']],
                'law-cartorio-civel' => ['Cartório Cível', null, ['processos', 'contatos', 'expedicoes', 'tarefas']],
                'law-gestao-audiencias' => ['Gestão de Audiências', null, ['processos', 'contatos', 'tarefas']],
                'law-gestao-expedientes' => ['Gestão de Expedientes', null, ['processos', 'contatos', 'expedicoes', 'tarefas']],
            ],
            'lead' => [
                'lead-one-essencial' => ['Essencial', 'one', ['pessoas', 'imoveis', 'notificacoes']],
                'lead-one-profissional' => ['Profissional', 'one', ['pessoas', 'imoveis', 'empreendimentos', 'leads', 'funil', 'website', 'notificacoes']],
                'lead-one-avancado' => ['Avançado', 'one', ['pessoas', 'imoveis', 'empreendimentos', 'leads', 'funil', 'website', 'relatorios', 'notificacoes']],
                'lead-one-premium' => ['Premium', 'one', ['pessoas', 'imoveis', 'empreendimentos', 'leads', 'funil', 'website', 'relatorios', 'whatsapp', 'notificacoes']],
                'lead-team-essencial' => ['Team Essencial', 'team', ['pessoas', 'imoveis', 'empreendimentos', 'funil', 'website', 'equipes', 'colaboracao', 'permissoes', 'relatorios-gerenciais', 'notificacoes']],
                'lead-team-premium' => ['Team Premium', 'team', ['pessoas', 'imoveis', 'empreendimentos', 'leads', 'funil', 'website', 'relatorios', 'whatsapp', 'portal-imoveis', 'equipes', 'colaboracao', 'permissoes', 'distribuicao-leads', 'visao-gerencial', 'filiais', 'relatorios-gerenciais', 'notificacoes']],
            ],
        ];

        foreach ($plans as $productCode => $items) {
            $productId = DB::table('products')->where('code', $productCode)->value('id');
            foreach ($items as $code => [$name, $segment, $moduleCodes]) {
                $plan = DB::table('plans')->where('product_id', $productId)->where('code', $code)->first(['id']);
                if (! $plan) {
                    $this->upsertCatalog('plans', ['product_id' => $productId, 'code' => $code], ['name' => $name], 'PLN');
                    $planId = DB::table('plans')->where('product_id', $productId)->where('code', $code)->value('id');
                    DB::table('plans')->where('id', $planId)->update([
                        'segment' => $segment,
                        'status' => 'inativo',
                        'publication_state' => 'rascunho',
                        'display_order' => array_search($code, array_keys($items), true),
                        'updated_at' => now(),
                    ]);
                } else {
                    $planId = $plan->id;
                }

                $moduleIds = DB::table('modules')->where('product_id', $productId)->whereIn('code', $moduleCodes)->pluck('id');
                foreach ($moduleIds as $moduleId) {
                    DB::table('plan_modules')->updateOrInsert(
                        ['plan_id' => $planId, 'module_id' => $moduleId],
                        ['updated_at' => now(), 'created_at' => now()]
                    );
                }
            }
        }

        foreach ($modules as $productCode => $items) {
            $productId = DB::table('products')->where('code', $productCode)->value('id');
            DB::table('modules')->where('product_id', $productId)->whereNotIn('code', array_keys($items))->delete();
        }
    }

    private function upsertCatalog(string $table, array $where, array $values, string $prefix): void
    {
        $query = DB::table($table)->where($where);
        if ($query->exists()) {
            $query->update([...$values, 'updated_at' => now()]);
            return;
        }
        DB::table($table)->insert([...$where, ...$values, 'id' => PrefixedUlid::make($prefix), 'created_at' => now(), 'updated_at' => now()]);
    }
}
