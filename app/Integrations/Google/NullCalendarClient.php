<?php

namespace App\Integrations\Google;

use App\Models\Opportunity;
use DateTimeInterface;
use Illuminate\Support\Facades\Log;

class NullCalendarClient implements CalendarClientInterface
{
    public function scheduleMeeting(
        Opportunity $opportunity,
        string $title,
        DateTimeInterface $start,
        DateTimeInterface $end,
        array $attendeeEmails,
    ): ?string {
        Log::info('Google Calendar integration disabled: skipped meeting scheduling.', [
            'opportunity_id' => $opportunity->id,
            'title' => $title,
        ]);

        return null;
    }

    public function isConfigured(): bool
    {
        return false;
    }
}
