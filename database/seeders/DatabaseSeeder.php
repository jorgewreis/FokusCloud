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
                'processos-advocacia' => ['Gestão de Processos e Movimentações - Advocacia', 4.90, 'advocacia', 'advocacia', false],
                'processos-criminal' => ['Gestão de Processos e Movimentações - Cartório Criminal', 4.90, 'cartorio', 'criminal', false],
                'processos-civel' => ['Gestão de Processos e Movimentações - Cartório Cível', 4.90, 'cartorio', 'civel', false],
                'processos-audiencias' => ['Gestão de Processos e Movimentações - Audiências', 4.90, 'audiencias', 'audiencias', false],
                'processos-expedientes' => ['Gestão de Processos e Movimentações - Expedientes', 4.90, 'expedientes', 'expedientes', false],
                'partes-cartorio' => ['Gestão de Partes - Cartório', 9.90, 'cartorio', 'partes', false],
                'clientes-advocacia' => ['Gestão de Clientes e Partes - Advocacia', 9.90, 'advocacia', 'clientes', false],
                'oficios-cartorio' => ['Ofícios - Setor Cartório', 4.90, 'expedientes', 'oficios-cartorio', false],
                'oficios-gabinete' => ['Ofícios - Setor Gabinete', 4.90, 'expedientes', 'oficios-gabinete', false],
                'cartas-expedidas' => ['Cartas Expedidas - Processuais e Administrativas', 4.90, 'expedientes', 'cartas-expedidas', false],
                'cartas-recebidas' => ['Cartas Recebidas - Processuais e Administrativas', 4.90, 'expedientes', 'cartas-recebidas', false],
                'editais-criminais' => ['Editais Criminais', 4.90, 'criminal', 'editais-criminais', false],
                'editais-civeis' => ['Editais Cíveis', 4.90, 'civel', 'editais-civeis', false],
                'guias-execucao-comum' => ['Guias de Execução - Varas Criminais Comuns', 9.90, 'criminal', 'execucao-comum', false],
                'guias-execucao-penal' => ['Guias de Execução - Varas de Execução Penal', 9.90, 'criminal', 'execucao-penal', false],
                'audiencias-cartorio' => ['Audiências - Controle Interno de Cartório', 14.90, 'audiencias', 'cartorio', false],
                'audiencias-externas' => ['Audiências - Acesso Externo', 14.90, 'audiencias', 'externo', false],
                'audiencias-advocacia' => ['Audiências - Agendamento para Advocacia', 14.90, 'advocacia', 'audiencias', false],
                'prazos-advocacia' => ['Gestão de Prazos e Intimações - Advocacia', 9.90, 'advocacia', 'prazos-intimacoes', true],
                'prazos-civel' => ['Controle de Prazos - Cartório Cível', 9.90, 'civel', 'prazos-processuais', true],
                'prazos-expedientes' => ['Controle de Prazos - Expedientes', 4.90, 'expedientes', 'prazos-expedientes', true],
                'agenda-advocacia' => ['Agenda e Compromissos - Advocacia', 4.90, 'advocacia', 'agenda', true],
                'agenda-cartorio' => ['Controle Interno de Audiências - Cartório', 4.90, 'audiencias', 'agenda-cartorio', true],
                'tarefas-advocacia' => ['Tarefas e Fluxos de Trabalho - Advocacia', 9.90, 'advocacia', 'tarefas', true],
                'tarefas-expedientes' => ['Tarefas e Fluxos de Trabalho - Expedientes', 9.90, 'expedientes', 'tarefas', true],
                'clientes' => ['Gestão de Clientes', 9.90, 'advocacia', 'clientes', true],
                'honorarios' => ['Controle de Honorários', 9.90, 'advocacia', 'honorarios', true],
                'financeiro' => ['Gerenciamento Financeiro', 14.90, 'advocacia', 'financeiro', true],
                'presos' => ['Controle de Presos', 9.90, 'criminal', 'presos', true],
                'monitoramento' => ['Monitoramento Eletrônico', 4.90, 'criminal', 'monitoramento', false],
                'medidas' => ['Medidas Protetivas', 9.90, 'criminal', 'medidas', false],
                'penas' => ['Penas Alternativas', 9.90, 'criminal', 'penas', true],
                'custas' => ['Custas e Recolhimentos', 9.90, 'civel', 'custas', true],
                'documentos' => ['Documentos e Modelos', 9.90, 'transversal', 'documentos', true],
                'assinatura-digital' => ['Assinatura Digital', 9.90, 'transversal', 'assinatura-digital', true],
                'relatorios-operacionais' => ['Relatórios Operacionais', 4.90, 'transversal', 'operacionais', true],
                'relatorios-juridicos' => ['Relatórios Jurídicos', 9.90, 'transversal', 'juridicos', true],
                'relatorios-gerenciais' => ['Relatórios Gerenciais', 14.90, 'transversal', 'gerenciais', true],
                'notificacoes' => ['Notificações', 4.90, 'transversal', 'notificacoes', true],
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
                'law-advocacia' => ['Advocacia', null, ['processos-advocacia', 'clientes-advocacia', 'audiencias-advocacia', 'prazos-advocacia', 'agenda-advocacia', 'tarefas-advocacia', 'honorarios', 'financeiro', 'documentos', 'relatorios-operacionais', 'relatorios-juridicos', 'notificacoes']],
                'law-cartorio-criminal' => ['Cartório Criminal', null, ['processos-criminal', 'partes-cartorio', 'oficios-cartorio', 'oficios-gabinete', 'cartas-expedidas', 'cartas-recebidas', 'editais-criminais', 'guias-execucao-comum', 'guias-execucao-penal', 'audiencias-cartorio', 'agenda-cartorio', 'presos', 'monitoramento', 'medidas', 'penas', 'relatorios-operacionais', 'notificacoes']],
                'law-cartorio-civel' => ['Cartório Cível', null, ['processos-civel', 'partes-cartorio', 'oficios-cartorio', 'oficios-gabinete', 'cartas-expedidas', 'cartas-recebidas', 'editais-civeis', 'custas', 'audiencias-cartorio', 'agenda-cartorio', 'prazos-civel', 'relatorios-operacionais', 'relatorios-juridicos', 'notificacoes']],
                'law-gestao-audiencias' => ['Gestão de Audiências', null, ['processos-audiencias', 'partes-cartorio', 'audiencias-cartorio', 'audiencias-externas', 'agenda-cartorio', 'relatorios-operacionais', 'notificacoes']],
                'law-gestao-expedientes' => ['Gestão de Expedientes', null, ['processos-expedientes', 'oficios-cartorio', 'oficios-gabinete', 'cartas-expedidas', 'cartas-recebidas', 'documentos', 'tarefas-expedientes', 'prazos-expedientes', 'assinatura-digital', 'relatorios-operacionais', 'notificacoes']],
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
