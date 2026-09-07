<?php

namespace App\Livewire;

use App\Enums\OpportunityStage;
use App\Exceptions\InvalidStageTransitionException;
use App\Models\Opportunity;
use App\Services\PipelineMetrics;
use App\Workflows\OpportunityWorkflow;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * The stage-by-stage pipeline board, shared by the Dashboard and the
 * Opportunities "Pipeline View" (execution plan section 11B: "Pipeline View
 * should resemble the Dashboard pipeline"). Every transition goes through
 * OpportunityPolicy::transition() and OpportunityWorkflow, same as the HTTP
 * endpoint — there is no separate, looser path for moving a stage from here.
 */
class OpportunityPipelineBoard extends Component
{
    public ?string $transitionError = null;

    #[Computed]
    public function stages(): array
    {
        return OpportunityStage::cases();
    }

    #[Computed]
    public function opportunitiesByStage(): array
    {
        return app(PipelineMetrics::class)->opportunitiesByStage();
    }

    public function allowedNextStages(Opportunity $opportunity): array
    {
        return OpportunityWorkflow::allowedNextStages($opportunity->stage);
    }

    public function transitionTo(int $opportunityId, string $toStage, ?string $reason = null): void
    {
        $this->transitionError = null;

        $opportunity = Opportunity::findOrFail($opportunityId);
        $to = OpportunityStage::from($toStage);

        if (Gate::denies('transition', [$opportunity, $to])) {
            $this->transitionError = 'You are not authorized to move this opportunity to '.$to->label().'.';

            return;
        }

        try {
            app(OpportunityWorkflow::class)->transition($opportunity, $to, auth()->user(), $reason);
        } catch (InvalidStageTransitionException|InvalidArgumentException $exception) {
            $this->transitionError = $exception->getMessage();

            return;
        }

        unset($this->opportunitiesByStage);
    }

    public function render(): View
    {
        return view('livewire.opportunity-pipeline-board');
    }
}
