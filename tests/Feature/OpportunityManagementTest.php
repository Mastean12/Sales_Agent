<?php

namespace Tests\Feature;

use App\Enums\OpportunityStage;
use App\Models\Company;
use App\Models\Opportunity;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OpportunityManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_creating_an_opportunity_always_starts_at_target_account(): void
    {
        $sales = User::factory()->create()->assignRole('Sales');
        $company = Company::factory()->create();

        $response = $this->actingAs($sales)->post(route('opportunities.store'), [
            'company_id' => $company->id,
            'owner_id' => $sales->id,
            'probability' => 20,
        ]);

        $opportunity = Opportunity::firstWhere('company_id', $company->id);
        $response->assertRedirect(route('opportunities.show', $opportunity));
        $this->assertSame(OpportunityStage::TargetAccount, $opportunity->stage);
    }

    public function test_the_stage_field_cannot_be_mass_assigned_through_update(): void
    {
        $sales = User::factory()->create()->assignRole('Sales');
        $opportunity = Opportunity::factory()->create(['owner_id' => $sales->id]);

        $this->actingAs($sales)->put(route('opportunities.update', $opportunity), [
            'owner_id' => $sales->id,
            'probability' => 50,
            'stage' => 'closed_won',
        ]);

        $this->assertSame(OpportunityStage::TargetAccount, $opportunity->fresh()->stage);
    }

    public function test_the_transition_endpoint_moves_an_opportunity_forward(): void
    {
        $sales = User::factory()->create()->assignRole('Sales');
        $opportunity = Opportunity::factory()->create(['owner_id' => $sales->id]);

        $this->actingAs($sales)
            ->patch(route('opportunities.transition', $opportunity), ['to' => 'prospect_identified'])
            ->assertRedirect(route('opportunities.show', $opportunity));

        $this->assertSame(OpportunityStage::ProspectIdentified, $opportunity->fresh()->stage);
    }

    public function test_the_transition_endpoint_rejects_an_unauthorized_role(): void
    {
        $finance = User::factory()->create()->assignRole('Finance');
        $opportunity = Opportunity::factory()->create();

        $this->actingAs($finance)
            ->patch(route('opportunities.transition', $opportunity), ['to' => 'prospect_identified'])
            ->assertForbidden();

        $this->assertSame(OpportunityStage::TargetAccount, $opportunity->fresh()->stage);
    }

    public function test_the_transition_endpoint_rejects_a_skipped_stage_even_for_admin(): void
    {
        // OpportunityPolicy::transition() delegates to
        // OpportunityWorkflow::canTransition(), which folds "is this move
        // legal in the pipeline" and "is this user allowed" into one check.
        // A skipped stage is therefore a 403, not a validation error, even
        // for a role that could otherwise transition the opportunity.
        $admin = User::factory()->create()->assignRole('Admin');
        $opportunity = Opportunity::factory()->create();

        $this->actingAs($admin)
            ->patch(route('opportunities.transition', $opportunity), ['to' => 'negotiation'])
            ->assertForbidden();

        $this->assertSame(OpportunityStage::TargetAccount, $opportunity->fresh()->stage);
    }

    public function test_expected_value_is_recalculated_from_value_and_probability(): void
    {
        $sales = User::factory()->create()->assignRole('Sales');
        $opportunity = Opportunity::factory()->create([
            'owner_id' => $sales->id,
            'value' => 10000,
            'probability' => 25,
        ]);

        $this->assertEquals(2500, $opportunity->fresh()->expected_value);
    }
}
