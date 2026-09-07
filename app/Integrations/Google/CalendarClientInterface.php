<?php

namespace App\Integrations\Google;

use App\Models\Opportunity;
use DateTimeInterface;

/**
 * Discovery scheduling and meeting events via Google Calendar (execution
 * plan section 4). Not wired up yet — Phase 4 (Meeting & Calendar).
 */
interface CalendarClientInterface
{
    /**
     * @param  list<string>  $attendeeEmails
     */
    public function scheduleMeeting(
        Opportunity $opportunity,
        string $title,
        DateTimeInterface $start,
        DateTimeInterface $end,
        array $attendeeEmails,
    ): ?string;

    public function isConfigured(): bool;
}
