<?php

namespace App\Exceptions;

use App\Enums\OpportunityStage;
use DomainException;

class InvalidStageTransitionException extends DomainException
{
    public static function notAllowed(OpportunityStage $from, OpportunityStage $to): self
    {
        return new self(sprintf(
            'Opportunities cannot move from "%s" to "%s". Stage transitions must follow the controlled pipeline.',
            $from->label(),
            $to->label(),
        ));
    }

    public static function unauthorized(OpportunityStage $from): self
    {
        return new self(sprintf(
            'You do not hold the role required to move an opportunity out of "%s".',
            $from->label(),
        ));
    }
}
