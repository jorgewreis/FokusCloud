<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PruneExpiredData extends Command
{
    protected $signature = 'fokus:prune-expired-data';
    protected $description = 'Remove tokens expirados e auditorias cuja retenção terminou.';

    public function handle(): int
    {
        $tokens = DB::table('security_tokens')->where('expires_at', '<', now()->subDays(90))->delete();
        $audits = DB::table('audit_events')->where('expires_at', '<', now())->delete();
        $this->info("Tokens removidos: {$tokens}; auditorias removidas: {$audits}.");
        return self::SUCCESS;
    }
}
