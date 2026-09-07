<?php

namespace App\Services;

use App\Enums\OpportunityStage;
use App\Models\Opportunity;
use Illuminate\Support\Collection;

/**
 * Dashboard/reporting KPI calculations (execution plan section 15/16).
 * Kept out of Blade/Livewire so the business definitions live in one place:
 * Expected Revenue = quoted value x close probability (Opportunity already
 * persists this as expected_value on save, see Opportunity::booted()).
 */
class PipelineMetrics
{
    public function summary(): array
    {
        $open = Opportunity::query()->whereNotIn('stage', ['closed_won', 'closed_lost'])->get();

        return [
            'open_count' => $open->count(),
            'pipeline_value' => (float) $open->sum('value'),
            'expected_value' => (float) $open->sum('expected_value'),
            'closed_won_count' => Opportunity::query()->where('stage', 'closed_won')->count(),
            'closed_lost_count' => Opportunity::query()->where('stage', 'closed_lost')->count(),
        ];
    }

    /**
     * @return array<string, Collection<int, Opportunity>>
     */
    public function opportunitiesByStage(): array
    {
        $opportunities = Opportunity::query()
            ->with(['company', 'owner'])
            ->orderByDesc('updated_at')
            ->get();

        return collect(OpportunityStage::cases())
            ->mapWithKeys(fn (OpportunityStage $stage) => [
                $stage->value => $opportunities->filter(fn (Opportunity $o) => $o->stage === $stage)->values(),
            ])
            ->all();
    }
}
