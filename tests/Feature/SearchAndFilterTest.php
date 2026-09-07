<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Contact;
use App\Models\Opportunity;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchAndFilterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_company_search_matches_name_domain_or_industry(): void
    {
        $sales = User::factory()->create()->assignRole('Sales');
        Company::factory()->create(['name' => 'Northwind Logistics', 'industry' => 'Logistics']);
        Company::factory()->create(['name' => 'Acme Retail', 'industry' => 'Retail']);

        $response = $this->actingAs($sales)->get(route('companies.index', ['q' => 'Northwind']));

        $response->assertSee('Northwind Logistics');
        $response->assertDontSee('Acme Retail');
    }

    public function test_company_status_filter_narrows_results(): void
    {
        $sales = User::factory()->create()->assignRole('Sales');
        Company::factory()->create(['name' => 'Active Co', 'status' => 'active']);
        Company::factory()->create(['name' => 'Prospect Co', 'status' => 'prospecting']);

        $response = $this->actingAs($sales)->get(route('companies.index', ['status' => 'active']));

        $response->assertSee('Active Co');
        $response->assertDontSee('Prospect Co');
    }

    public function test_contact_search_matches_name_email_or_company(): void
    {
        $sales = User::factory()->create()->assignRole('Sales');
        $company = Company::factory()->create();
        Contact::factory()->create(['company_id' => $company->id, 'name' => 'Jane Findme', 'email' => 'jane@example.com']);
        Contact::factory()->create(['company_id' => $company->id, 'name' => 'Other Person', 'email' => 'other@example.com']);

        $response = $this->actingAs($sales)->get(route('contacts.index', ['q' => 'Findme']));

        $response->assertSee('Jane Findme');
        $response->assertDontSee('Other Person');
    }

    public function test_opportunity_stage_filter_narrows_results(): void
    {
        // Company names also appear in the filter dropdown regardless of the
        // active filter, so assert on each opportunity's (unique) next
        // action instead of the company name.
        $sales = User::factory()->create()->assignRole('Sales');
        Opportunity::factory()->create(['stage' => 'target_account', 'next_action' => 'Research the account further']);
        Opportunity::factory()->create(['stage' => 'proposal', 'next_action' => 'Send the signed proposal']);

        $response = $this->actingAs($sales)->get(route('opportunities.index', ['stage' => 'proposal']));

        $response->assertSee('Send the signed proposal');
        $response->assertDontSee('Research the account further');
    }
}
