<?php

namespace App\Console\Commands;

use App\Services\SubscriptionBillingManager;
use Illuminate\Console\Command;

class ExpireSubscriptionTolerance extends Command
{
    protected $signature = 'fokus:expire-subscription-tolerance';
    protected $description = 'Suspende assinaturas cuja tolerância de inadimplência terminou.';

    public function handle(SubscriptionBillingManager $billing): int
    {
        $count = $billing->expireTolerance();
        $this->info("Assinaturas suspensas: {$count}");
        return self::SUCCESS;
    }
}
