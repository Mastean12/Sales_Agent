<?php

namespace Tests\Unit;

use App\Enums\OpportunityStage;
use App\Workflows\OpportunityWorkflow;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class OpportunityWorkflowTransitionsTest extends TestCase
{
    #[DataProvider('forwardTransitions')]
    public function test_each_stage_advances_to_the_next_stage_in_the_controlled_pipeline(
        OpportunityStage $from,
        OpportunityStage $expectedNext,
    ): void {
        $allowed = OpportunityWorkflow::allowedNextStages($from);

        $this->assertContains($expectedNext, $allowed);
    }

    public static function forwardTransitions(): array
    {
        return [
            [OpportunityStage::TargetAccount, OpportunityStage::ProspectIdentified],
            [OpportunityStage::ProspectIdentified, OpportunityStage::EngagedProspect],
            [OpportunityStage::EngagedProspect, OpportunityStage::QualifiedDiscovery],
            [OpportunityStage::QualifiedDiscovery, OpportunityStage::SalesQualifiedOpportunity],
            [OpportunityStage::SalesQualifiedOpportunity, OpportunityStage::TechnicalSolutionDiscovery],
            [OpportunityStage::TechnicalSolutionDiscovery, OpportunityStage::Proposal],
            [OpportunityStage::Proposal, OpportunityStage::Negotiation],
            [OpportunityStage::Negotiation, OpportunityStage::ClosedWon],
        ];
    }

    public function test_open_stages_may_move_to_closed_lost(): void
    {
        foreach (OpportunityStage::cases() as $stage) {
            if ($stage->isClosed()) {
                continue;
            }

            $this->assertContains(
                OpportunityStage::ClosedLost,
                OpportunityWorkflow::allowedNextStages($stage),
                "{$stage->value} should be able to move to Closed Lost",
            );
        }
    }

    public function test_closed_won_can_only_be_reached_from_negotiation(): void
    {
        foreach (OpportunityStage::cases() as $stage) {
            $allowed = OpportunityWorkflow::allowedNextStages($stage);

            if ($stage === OpportunityStage::Negotiation) {
                $this->assertContains(OpportunityStage::ClosedWon, $allowed);
            } else {
                $this->assertNotContains(OpportunityStage::ClosedWon, $allowed);
            }
        }
    }

    public function test_stages_cannot_be_skipped(): void
    {
        $allowed = OpportunityWorkflow::allowedNextStages(OpportunityStage::TargetAccount);

        $this->assertNotContains(OpportunityStage::QualifiedDiscovery, $allowed);
        $this->assertNotContains(OpportunityStage::SalesQualifiedOpportunity, $allowed);
    }

    public function test_closed_stages_have_no_further_transitions(): void
    {
        $this->assertSame([], OpportunityWorkflow::allowedNextStages(OpportunityStage::ClosedWon));
        $this->assertSame([], OpportunityWorkflow::allowedNextStages(OpportunityStage::ClosedLost));
    }
}
