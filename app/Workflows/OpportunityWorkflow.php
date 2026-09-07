<?php

namespace App\Workflows;

use App\Enums\AuditAction;
use App\Enums\OpportunityStage;
use App\Events\OpportunityStageChanged;
use App\Exceptions\InvalidStageTransitionException;
use App\Models\Opportunity;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * The only supported way to move an Opportunity between pipeline stages.
 *
 * Enforces the execution plan's controlled revenue journey (section 2):
 * stages progress forward one step at a time, an opportunity can be marked
 * Closed Lost from any open stage, Closed Won is only reachable from
 * Negotiation, and only the role that owns a stage (or Founder/Management
 * / Admin) may move an opportunity out of it.
 *
 * NOTE: the "any open stage -> Closed Lost" and "Closed Won only from
 * Negotiation" rules are an interpretation of the plan, not stated
 * verbatim there. Flagged for human confirmation in the implementation
 * report.
 */
class OpportunityWorkflow
{
    /** @var array<string, list<string>> */
    private const array TRANSITIONS = [
        'target_account' => ['prospect_identified', 'closed_lost'],
        'prospect_identified' => ['engaged_prospect', 'closed_lost'],
        'engaged_prospect' => ['qualified_discovery', 'closed_lost'],
        'qualified_discovery' => ['sales_qualified_opportunity', 'closed_lost'],
        'sales_qualified_opportunity' => ['technical_solution_discovery', 'closed_lost'],
        'technical_solution_discovery' => ['proposal', 'closed_lost'],
        'proposal' => ['negotiation', 'closed_lost'],
        'negotiation' => ['closed_won', 'closed_lost'],
        'closed_won' => [],
        'closed_lost' => [],
    ];

    public function __construct(private readonly AuditLogger $auditLogger) {}

    /**
     * @return list<OpportunityStage>
     */
    public static function allowedNextStages(OpportunityStage $from): array
    {
        return array_map(
            fn (string $value) => OpportunityStage::from($value),
            self::TRANSITIONS[$from->value] ?? [],
        );
    }

    public function canTransition(User $actor, Opportunity $opportunity, OpportunityStage $to): bool
    {
        $from = $opportunity->stage;

        return ! $from->isClosed()
            && in_array($to, self::allowedNextStages($from), true)
            && $this->authorized($actor, $from);
    }

    public function transition(
        Opportunity $opportunity,
        OpportunityStage $to,
        User $actor,
        ?string $reason = null,
    ): Opportunity {
        return DB::transaction(function () use ($opportunity, $to, $actor, $reason) {
            /** @var Opportunity $locked */
            $locked = Opportunity::query()->lockForUpdate()->findOrFail($opportunity->getKey());
            $from = $locked->stage;

            if ($from->isClosed() || ! in_array($to, self::allowedNextStages($from), true)) {
                throw InvalidStageTransitionException::notAllowed($from, $to);
            }

            if (! $this->authorized($actor, $from)) {
                throw InvalidStageTransitionException::unauthorized($from);
            }

            if ($to === OpportunityStage::ClosedLost && ! $reason) {
                throw new InvalidArgumentException(
                    'A reason is required when marking an opportunity Closed Lost.',
                );
            }

            $before = ['stage' => $from->value];

            $locked->stage = $to;

            if ($to->isClosed()) {
                $locked->closed_at = now();
                $locked->closed_reason = $reason;
            }

            $locked->save();

            $this->auditLogger->record(
                auditable: $locked,
                action: AuditAction::StageTransitioned,
                actor: $actor,
                before: $before,
                after: ['stage' => $to->value],
                reason: $reason,
            );

            OpportunityStageChanged::dispatch($locked, $from, $to, $actor, $reason);

            return $locked;
        });
    }

    private function authorized(User $actor, OpportunityStage $from): bool
    {
        if ($actor->hasAnyRole(['Admin', 'Founder/Management'])) {
            return true;
        }

        return $actor->hasAnyRole($from->ownerRoles());
    }
}
