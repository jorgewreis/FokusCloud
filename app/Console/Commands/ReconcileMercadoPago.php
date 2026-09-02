<?php

namespace App\Console\Commands;

use App\Services\BillingReconciliationManager;
use Illuminate\Console\Command;

class ReconcileMercadoPago extends Command
{
    protected $signature = 'fokus:reconcile-mercado-pago {--subscription=} {--dry-run}';
    protected $description = 'Compara assinaturas locais com o Mercado Pago e registra divergências.';

    public function handle(BillingReconciliationManager $manager): int
    {
        $count = $manager->reconcile($this->option('subscription'), (bool) $this->option('dry-run'));
        $this->info("Divergências abertas: {$count}");
        return self::SUCCESS;
    }
}
