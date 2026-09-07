<?php

namespace App\Integrations\Google;

use Illuminate\Support\Facades\Log;

class NullGmailClient implements GmailClientInterface
{
    public function send(array $to, string $subject, string $htmlBody): ?string
    {
        Log::info('Gmail integration disabled: skipped outbound email.', ['to' => $to, 'subject' => $subject]);

        return null;
    }

    public function isConfigured(): bool
    {
        return false;
    }
}
