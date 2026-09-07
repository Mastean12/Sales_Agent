<?php

namespace App\AI\Exceptions;

use RuntimeException;

class AIProviderNotConfiguredException extends RuntimeException
{
    public static function make(): self
    {
        return new self(
            'No AI provider is configured (AI_PROVIDER=null). AI-assisted '
            .'features are deferred until Phase 3 (Prospect Intelligence).',
        );
    }
}
