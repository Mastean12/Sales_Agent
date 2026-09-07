<?php

namespace App\Actions\Opportunities;

use App\Enums\AuditAction;
use App\Models\Opportunity;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\DB;

class DeleteOpportunityAction
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function execute(Opportunity $opportunity, User $actor): void
    {
        DB::transaction(function () use ($opportunity, $actor) {
            $before = $opportunity->getAttributes();

            $opportunity->delete();

            $this->auditLogger->record(
                auditable: $opportunity,
                action: AuditAction::Deleted,
                actor: $actor,
                before: $before,
            );
        });
    }
}
