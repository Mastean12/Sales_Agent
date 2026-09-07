<?php

namespace App\Providers;

use App\AI\AIProviderInterface;
use App\AI\NullAIProvider;
use App\Integrations\Google\CalendarClientInterface;
use App\Integrations\Google\GmailClientInterface;
use App\Integrations\Google\NullCalendarClient;
use App\Integrations\Google\NullGmailClient;
use App\Integrations\Hubspot\HubspotClientInterface;
use App\Integrations\Hubspot\NullHubspotClient;
use Illuminate\Support\ServiceProvider;

/**
 * Binds every external integration to a safe no-op implementation until its
 * phase is built. Swapping AI_PROVIDER, HUBSPOT_ENABLED or
 * GOOGLE_WORKSPACE_ENABLED to a real client later should not require
 * touching any code that depends on these interfaces.
 */
class IntegrationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AIProviderInterface::class, function () {
            return match (config('services.ai.provider')) {
                default => new NullAIProvider,
            };
        });

        $this->app->bind(HubspotClientInterface::class, function () {
            if (! config('services.hubspot.enabled')) {
                return new NullHubspotClient;
            }

            return new NullHubspotClient;
        });

        $this->app->bind(GmailClientInterface::class, function () {
            if (! config('services.google.enabled')) {
                return new NullGmailClient;
            }

            return new NullGmailClient;
        });

        $this->app->bind(CalendarClientInterface::class, function () {
            if (! config('services.google.enabled')) {
                return new NullCalendarClient;
            }

            return new NullCalendarClient;
        });
    }
}
