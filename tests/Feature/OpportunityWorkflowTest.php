<?php

namespace Tests\Feature;

use App\Enums\AuditAction;
use App\Enums\OpportunityStage;
use App\Exceptions\InvalidStageTransitionException;
use App\Models\Opportunity;
use App\Models\User;
use App\Workflows\OpportunityWorkflow;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class OpportunityWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private OpportunityWorkflow $workflow;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->workflow = app(OpportunityWorkflow::class);
    }

    public function test_sales_owner_can_advance_an_opportunity_through_the_pipeline(): void
    {
        $sales = User::factory()->create()->assignRole('Sales');
        $opportunity = Opportunity::factory()->create(['owner_id' => $sales->id]);

        $updated = $this->workflow->transition($opportunity, OpportunityStage::ProspectIdentified, $sales);

        $this->assertSame(OpportunityStage::ProspectIdentified, $updated->stage);
    }

    public function test_cannot_skip_stages(): void
    {
        $sales = User::factory()->create()->assignRole('Sales');
        $opportunity = Opportunity::factory()->create(['owner_id' => $sales->id]);

        $this->expectException(InvalidStageTransitionException::class);

        $this->workflow->transition($opportunity, OpportunityStage::QualifiedDiscovery, $sales);
    }

    public function test_a_user_without_the_owning_role_cannot_transition_the_stage(): void
    {
        $finance = User::factory()->create()->assignRole('Finance');
        $opportunity = Opportunity::factory()->create();

        $this->expectException(InvalidStageTransitionException::class);

        $this->workflow->transition($opportunity, OpportunityStage::ProspectIdentified, $finance);
    }

    public function test_technical_delivery_owns_the_technical_solution_discovery_stage(): void
    {
        $technical = User::factory()->create()->assignRole('Technical/Delivery');
        $opportunity = Opportunity::factory()->create([
            'stage' => OpportunityStage::TechnicalSolutionDiscovery,
        ]);

        $updated = $this->workflow->transition($opportunity, OpportunityStage::Proposal, $technical);

        $this->assertSame(OpportunityStage::Proposal, $updated->stage);
    }

    public function test_admin_can_override_and_transition_any_stage(): void
    {
        $admin = User::factory()->create()->assignRole('Admin');
        $opportunity = Opportunity::factory()->create();

        $updated = $this->workflow->transition($opportunity, OpportunityStage::ProspectIdentified, $admin);

        $this->assertSame(OpportunityStage::ProspectIdentified, $updated->stage);
    }

    public function test_closing_lost_requires_a_reason(): void
    {
        $sales = User::factory()->create()->assignRole('Sales');
        $opportunity = Opportunity::factory()->create(['owner_id' => $sales->id]);

        $this->expectException(InvalidArgumentException::class);

        $this->workflow->transition($opportunity, OpportunityStage::ClosedLost, $sales);
    }

    public function test_closing_lost_with_a_reason_records_it_and_stamps_closed_at(): void
    {
        $sales = User::factory()->create()->assignRole('Sales');
        $opportunity = Opportunity::factory()->create(['owner_id' => $sales->id]);

        $updated = $this->workflow->transition($opportunity, OpportunityStage::ClosedLost, $sales, 'Budget frozen.');

        $this->assertSame(OpportunityStage::ClosedLost, $updated->stage);
        $this->assertSame('Budget frozen.', $updated->closed_reason);
        $this->assertNotNull($updated->closed_at);
    }

    public function test_transitions_cannot_be_made_out_of_a_closed_stage(): void
    {
        $admin = User::factory()->create()->assignRole('Admin');
        $opportunity = Opportunity::factory()->create(['stage' => OpportunityStage::ClosedWon]);

        $this->expectException(InvalidStageTransitionException::class);

        $this->workflow->transition($opportunity, OpportunityStage::Negotiation, $admin);
    }

    public function test_a_successful_transition_writes_an_audit_event(): void
    {
        $sales = User::factory()->create()->assignRole('Sales');
        $opportunity = Opportunity::factory()->create(['owner_id' => $sales->id]);

        $this->workflow->transition($opportunity, OpportunityStage::ProspectIdentified, $sales);

        $this->assertDatabaseHas('audit_events', [
            'auditable_type' => $opportunity->getMorphClass(),
            'auditable_id' => $opportunity->id,
            'action' => AuditAction::StageTransitioned->value,
            'actor_id' => $sales->id,
        ]);
    }

    public function test_a_failed_transition_does_not_write_an_audit_event(): void
    {
        $finance = User::factory()->create()->assignRole('Finance');
        $opportunity = Opportunity::factory()->create();

        try {
            $this->workflow->transition($opportunity, OpportunityStage::ProspectIdentified, $finance);
        } catch (InvalidStageTransitionException) {
            // expected
        }

        $this->assertDatabaseMissing('audit_events', [
            'auditable_type' => $opportunity->getMorphClass(),
            'auditable_id' => $opportunity->id,
        ]);
    }
}
