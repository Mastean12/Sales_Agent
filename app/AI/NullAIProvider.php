<?php

namespace App\AI;

use App\AI\Exceptions\AIProviderNotConfiguredException;

/**
 * Bound whenever AI_PROVIDER=null. Fails loudly instead of silently
 * returning fabricated content, matching the "no invented capabilities,
 * pricing or commitments" rule (execution plan section 9).
 */
class NullAIProvider implements AIProviderInterface
{
    public function complete(string $prompt, array $context = []): string
    {
        throw AIProviderNotConfiguredException::make();
    }

    public function isConfigured(): bool
    {
        return false;
    }
}
