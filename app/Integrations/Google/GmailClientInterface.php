<?php

namespace App\Integrations\Google;

/**
 * Outreach/reply email is sent through Marixion's controlled company
 * mailbox via the Gmail/Google Workspace API (execution plan section 4).
 * Not wired up yet — Phase 3 (Outreach Engine).
 */
interface GmailClientInterface
{
    /**
     * @param  list<string>  $to
     */
    public function send(array $to, string $subject, string $htmlBody): ?string;

    public function isConfigured(): bool;
}
