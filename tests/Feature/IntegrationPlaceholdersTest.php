<?php

namespace Tests\Feature;

use App\AI\AIProviderInterface;
use App\AI\Exceptions\AIProviderNotConfiguredException;
use App\Integrations\Google\CalendarClientInterface;
use App\Integrations\Google\GmailClientInterface;
use App\Integrations\Hubspot\HubspotClientInterface;
use Tests\TestCase;

/**
 * These integrations are deliberately not wired to real APIs yet (execution
 * plan says Prospect Intelligence / Outreach / HubSpot sync come later).
 * This test only proves the interfaces resolve to safe no-op
 * implementations, so nothing accidentally calls a real, unconfigured API.
 */
class IntegrationPlaceholdersTest extends TestCase
{
    public function test_ai_provider_is_unconfigured_by_default_and_fails_loudly(): void
    {
        $provider = app(AIProviderInterface::class);

        $this->assertFalse($provider->isConfigured());

        $this->expectException(AIProviderNotConfiguredException::class);
        $provider->complete('anything');
    }

    public function test_hubspot_client_is_unconfigured_by_default(): void
    {
        $this->assertFalse(app(HubspotClientInterface::class)->isConfigured());
    }

    public function test_gmail_client_is_unconfigured_by_default(): void
    {
        $this->assertFalse(app(GmailClientInterface::class)->isConfigured());
    }

    public function test_calendar_client_is_unconfigured_by_default(): void
    {
        $this->assertFalse(app(CalendarClientInterface::class)->isConfigured());
    }
}
