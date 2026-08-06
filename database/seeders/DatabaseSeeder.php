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

        foreach (['law' => 'Fokus Law', 'lead' => 'Fokus Lead'] as $code => $name) {
            $this->upsertCatalog('products', ['code' => $code], ['name' => $name, 'active' => true], 'PRD');
        }

        $modules = [
            'law' => ['oficios' => ['Gestão de Ofícios', 4.90], 'partes' => ['Gestão de Partes', 9.90], 'processos' => ['Gestão de Processos e Movimentações', 4.90], 'cartas-exp' => ['Cartas Expedidas', 4.90], 'cartas-rec' => ['Cartas Recebidas', 4.90], 'editais' => ['Editais', 4.90], 'guias' => ['Guias', 9.90], 'audiencias' => ['Audiências', 14.90], 'monitoramento' => ['Monitoramento Eletrônico', 4.90], 'medidas' => ['Medidas Protetivas', 9.90]],
            'lead' => ['pessoas' => ['Gestão de Pessoas', 4.90], 'empreendimentos' => ['Gestão de Empreendimentos', 4.90], 'imoveis' => ['Gestão de Imóveis', 4.90], 'funil' => ['Funil de Vendas', 4.90], 'relatorios' => ['Emissão de Relatórios de Transações', 9.90], 'whatsapp' => ['Integração com WhatsApp', 9.90], 'website' => ['Integração com Website', 14.90]],
        ];
        foreach ($modules as $productCode => $items) {
            $productId = DB::table('products')->where('code', $productCode)->value('id');
            foreach ($items as $code => [$name, $price]) {
                $this->upsertCatalog('modules', ['product_id' => $productId, 'code' => $code], ['name' => $name, 'monthly_price' => $price], 'MOD');
            }
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
