<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlatformAudit
{
    public function record(?string $adminId, string $action, ?string $entityType = null, ?string $entityId = null, ?string $companyId = null, ?string $reason = null, ?string $ticket = null, ?array $metadata = null, ?Request $request = null): void
    {
        DB::table('platform_audit_events')->insert([
            'id' => PrefixedUlid::make('PAD'), 'platform_admin_id' => $adminId, 'action' => $action,
            'entity_type' => $entityType, 'entity_id' => $entityId, 'company_id' => $companyId,
            'reason' => $reason, 'support_ticket' => $ticket, 'metadata' => $metadata ? json_encode($metadata) : null,
            'ip_address' => $request?->ip(), 'user_agent' => $request?->userAgent(), 'created_at' => now(),
        ]);
    }
}
