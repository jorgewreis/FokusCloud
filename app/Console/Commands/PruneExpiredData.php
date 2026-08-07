<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PruneExpiredData extends Command
{
    protected $signature = 'fokus:prune-expired-data';
    protected $description = 'Remove dados cuja retenção documentada terminou.';

    public function handle(): int
    {
        $tokens = DB::table('security_tokens')->where('expires_at', '<', now()->subDays(90))->delete();
        $audits = DB::table('audit_events')->where('expires_at', '<', now())->delete();
        $expiredInvitations = DB::table('company_invitations')->where('expires_at', '<', now()->subDays(90))->delete();
        $memberships = DB::table('company_memberships')->where('status', 'removido')->whereNotNull('deleted_at')->where('deleted_at', '<', now()->subDays(90))->delete();
        $this->info("Tokens removidos: {$tokens}; auditorias removidas: {$audits}; convites removidos: {$expiredInvitations}; vínculos removidos: {$memberships}.");
        return self::SUCCESS;
    }
}
