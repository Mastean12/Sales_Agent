<?php

namespace App\Integrations\Hubspot;

use App\Models\Company;
use App\Models\Contact;
use App\Models\Opportunity;

/**
 * HubSpot remains Marixion's CRM system of record (execution plan section
 * 12). Laravel is expected to push companies/contacts/deals to HubSpot and
 * react to its webhooks, not replace it. Every method returns the HubSpot
 * object id on success so it can be stored on the local record
 * (companies.hubspot_company_id, etc.).
 */
interface HubspotClientInterface
{
    public function upsertCompany(Company $company): ?string;

    public function upsertContact(Contact $contact): ?string;

    public function upsertDeal(Opportunity $opportunity): ?string;

    public function isConfigured(): bool;
}
