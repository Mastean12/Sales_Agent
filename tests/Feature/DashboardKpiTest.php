<?php

namespace Tests\Feature;

use App\Models\Opportunity;
use App\Services\PipelineMetrics;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardKpiTest extends TestCase
{
    use RefreshDatabase;

    public function test_summary_reflects_real_database_state(): void
    {
        Opportunity::factory()->create(['stage' => 'target_account', 'value' => 10000, 'probability' => 50]);
        Opportunity::factory()->create(['stage' => 'proposal', 'value' => 20000, 'probability' => 25]);
        Opportunity::factory()->create(['stage' => 'closed_won', 'value' => 5000, 'probability' => 100]);
        Opportunity::factory()->create(['stage' => 'closed_lost', 'value' => 8000, 'probability' => 0]);

        $summary = app(PipelineMetrics::class)->summary();

        // Open = not closed_won/closed_lost => the target_account + proposal rows.
        $this->assertSame(2, $summary['open_count']);
        $this->assertEquals(30000, $summary['pipeline_value']);
        // Expected revenue = value * probability / 100, summed over open opportunities.
        $this->assertEquals(5000 + 5000, $summary['expected_value']);
        $this->assertSame(1, $summary['closed_won_count']);
        $this->assertSame(1, $summary['closed_lost_count']);
    }

    public function test_opportunities_by_stage_groups_every_stage(): void
    {
        Opportunity::factory()->create(['stage' => 'negotiation']);

        $byStage = app(PipelineMetrics::class)->opportunitiesByStage();

        $this->assertCount(1, $byStage['negotiation']);
        $this->assertCount(0, $byStage['target_account']);
        $this->assertArrayHasKey('closed_lost', $byStage);
    }
}
