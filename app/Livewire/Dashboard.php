<?php

namespace App\Livewire;

use App\Enums\OpportunityStage;
use App\Exceptions\InvalidStageTransitionException;
use App\Models\Opportunity;
use App\Workflows\OpportunityWorkflow;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * The "one dashboard" the execution plan describes (section 18): pipeline
 * counts/value plus a stage-by-stage board so a transition is one click
 * away. All transitions still go through OpportunityWorkflow, so this view
 * cannot bypass the controlled pipeline rules.
 */
#[Layout('layouts.app')]
class Dashboard extends Component
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

    #[Computed]
    public function stats(): array
    {
        $open = Opportunity::query()->whereNotIn('stage', ['closed_won', 'closed_lost'])->get();

        return [
            'open_count' => $open->count(),
            'pipeline_value' => $open->sum('value'),
            'expected_value' => $open->sum('expected_value'),
            'closed_won_count' => Opportunity::query()->where('stage', 'closed_won')->count(),
        ];
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

        unset($this->opportunitiesByStage, $this->stats);
    }

    public function render(): View
    {
        return view('livewire.dashboard');
    }
}
