<?php

namespace App\AI;

/**
 * Provider abstraction for every AI-assisted capability (execution plan
 * section 6: research, classification, extraction, drafting). No caller may
 * depend on a concrete provider (e.g. OpenAI) directly — only on this
 * interface — so the reasoning engine can be swapped without touching the
 * deterministic control plane.
 *
 * AI output is always advisory. Nothing implementing this interface may be
 * trusted to set a price, approve a discount, confirm a payment, or commit
 * Marixion to a capability — those stay in Laravel services (see the
 * execution plan's "AI vs Deterministic Boundary" table, section 6).
 */
interface AIProviderInterface
{
    /**
     * Ask the provider to reason over a prompt and return unstructured text.
     *
     * @param  array<string, mixed>  $context
     */
    public function complete(string $prompt, array $context = []): string;

    public function isConfigured(): bool;
}
