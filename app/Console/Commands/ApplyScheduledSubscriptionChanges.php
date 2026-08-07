<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ApplyScheduledSubscriptionChanges extends Command
{
    protected $signature = 'fokus:apply-subscription-changes';
    protected $description = 'Aplica cancelamentos e downgrades cuja vigência chegou ao fim.';

    public function handle(): int
    {
        $changes = DB::table('subscription_changes')->where('status', 'agendada')->where('effective_at', '<=', now())->orderBy('effective_at')->get();
        foreach ($changes as $change) {
            DB::transaction(function () use ($change) {
                $locked = DB::table('subscription_changes')->where('id', $change->id)->lockForUpdate()->first();
                if (! $locked || $locked->status !== 'agendada') return;
                if ($locked->type === 'cancelamento') {
                    DB::table('subscriptions')->where('id', $locked->subscription_id)->update(['status' => 'encerrada', 'open_company_product' => null, 'updated_at' => now(), 'version' => DB::raw('version + 1')]);
                }
                DB::table('subscription_changes')->where('id', $locked->id)->update(['status' => 'aplicada', 'updated_at' => now()]);
            });
        }
        $this->info("Alterações aplicadas: {$changes->count()}");
        return self::SUCCESS;
    }
}
