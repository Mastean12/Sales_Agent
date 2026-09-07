<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Models\AuditEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * The single write path for audit_events. Every state change worth
 * reconstructing later (section 13, "Audit logs for pricing, proposals,
 * approvals, payment events and permissions") should go through here rather
 * than writing to the table directly.
 */
class AuditLogger
{
    public function record(
        Model $auditable,
        AuditAction $action,
        ?User $actor,
        ?array $before = null,
        ?array $after = null,
        ?string $reason = null,
    ): AuditEvent {
        return AuditEvent::create([
            'actor_id' => $actor?->id,
            'action' => $action,
            'auditable_id' => $auditable->getKey(),
            'auditable_type' => $auditable->getMorphClass(),
            'before' => $before,
            'after' => $after,
            'reason' => $reason,
        ]);
    }
}
