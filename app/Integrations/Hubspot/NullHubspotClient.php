<?php

namespace App\Integrations\Hubspot;

use App\Models\Company;
use App\Models\Contact;
use App\Models\Opportunity;
use Illuminate\Support\Facades\Log;

/**
 * Bound whenever HUBSPOT_ENABLED=false. No-op, logs intent so the sync
 * boundary is visible in local/dev logs without requiring real credentials.
 */
class NullHubspotClient implements HubspotClientInterface
{
    public function upsertCompany(Company $company): ?string
    {
        Log::info('HubSpot integration disabled: skipped company sync.', ['company_id' => $company->id]);

        return null;
    }

    public function upsertContact(Contact $contact): ?string
    {
        Log::info('HubSpot integration disabled: skipped contact sync.', ['contact_id' => $contact->id]);

        return null;
    }

    public function upsertDeal(Opportunity $opportunity): ?string
    {
        Log::info('HubSpot integration disabled: skipped deal sync.', ['opportunity_id' => $opportunity->id]);

        return null;
    }

    public function isConfigured(): bool
    {
        return false;
    }
}
