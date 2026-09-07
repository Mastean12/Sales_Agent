<?php

namespace App\Policies;

use App\Enums\OpportunityStage;
use App\Models\Opportunity;
use App\Models\User;
use App\Workflows\OpportunityWorkflow;

class OpportunityPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->roles()->exists();
    }

    /**
     * MVP visibility is shared across all internal roles so everyone works
     * from the one pipeline dashboard the execution plan describes
     * (section 18). Row-level ownership scoping was not specified and would
     * need a human decision before being introduced.
     */
    public function view(User $user, Opportunity $opportunity): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Sales', 'Sales Manager', 'Founder/Management', 'Admin']);
    }

    public function update(User $user, Opportunity $opportunity): bool
    {
        return $user->id === $opportunity->owner_id
            || $user->hasAnyRole(['Sales Manager', 'Founder/Management', 'Admin']);
    }

    public function delete(User $user, Opportunity $opportunity): bool
    {
        return $user->hasAnyRole(['Founder/Management', 'Admin']);
    }

    /**
     * Whether $user may move $opportunity to $to. Mirrors
     * OpportunityWorkflow::canTransition so controllers/Livewire components
     * can gate the UI before invoking the workflow.
     */
    public function transition(User $user, Opportunity $opportunity, OpportunityStage $to): bool
    {
        return app(OpportunityWorkflow::class)->canTransition($user, $opportunity, $to);
    }
}
