<?php

namespace App\Livewire;

use App\Services\PipelineMetrics;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * The "one dashboard" the execution plan describes (section 18): KPI cards
 * plus the pipeline board (App\Livewire\OpportunityPipelineBoard). All KPI
 * math lives in PipelineMetrics, not here or in Blade, per section 16.
 */
#[Layout('layouts.app')]
class Dashboard extends Component
{
    #[Computed]
    public function stats(): array
    {
        return app(PipelineMetrics::class)->summary();
    }

    public function render(): View
    {
        return view('livewire.dashboard');
    }
}
