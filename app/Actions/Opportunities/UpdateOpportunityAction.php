<?php

namespace App\Actions\Opportunities;

use App\Enums\AuditAction;
use App\Models\Opportunity;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\DB;

/**
 * Updates the editable, non-workflow fields of an Opportunity. Stage is
 * deliberately excluded here — it can only change via OpportunityWorkflow.
 */
class UpdateOpportunityAction
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function execute(Opportunity $opportunity, array $data, User $actor): Opportunity
    {
        unset($data['stage']);

        return DB::transaction(function () use ($opportunity, $data, $actor) {
            $before = $opportunity->getAttributes();

            $opportunity->update($data);

            $ownerChanged = array_key_exists('owner_id', $data)
                && $before['owner_id'] !== $opportunity->owner_id;

            $this->auditLogger->record(
                auditable: $opportunity,
                action: $ownerChanged ? AuditAction::OwnerReassigned : AuditAction::Updated,
                actor: $actor,
                before: $before,
                after: $opportunity->getAttributes(),
            );

            return $opportunity;
        });
    }
}
