<?php

namespace App\Events;

use App\Enums\OpportunityStage;
use App\Models\Opportunity;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OpportunityStageChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Opportunity $opportunity,
        public readonly OpportunityStage $from,
        public readonly OpportunityStage $to,
        public readonly ?User $actor,
        public readonly ?string $reason = null,
    ) {}
}
