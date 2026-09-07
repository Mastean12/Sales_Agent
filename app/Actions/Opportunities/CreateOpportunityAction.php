<?php

namespace App\Actions\Opportunities;

use App\Enums\AuditAction;
use App\Enums\OpportunityStage;
use App\Models\Opportunity;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\DB;

/**
 * Every opportunity enters the pipeline at Target Account. There is no
 * shortcut to a later stage on creation: reaching Prospect Identified and
 * beyond must go through OpportunityWorkflow so the audit trail and
 * ownership rules are always applied (execution plan section 2).
 */
class CreateOpportunityAction
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function execute(array $data, User $actor): Opportunity
    {
        return DB::transaction(function () use ($data, $actor) {
            $opportunity = Opportunity::create([
                ...$data,
                'stage' => OpportunityStage::TargetAccount,
            ]);

            $this->auditLogger->record(
                auditable: $opportunity,
                action: AuditAction::Created,
                actor: $actor,
                after: $opportunity->getAttributes(),
            );

            return $opportunity;
        });
    }
}
