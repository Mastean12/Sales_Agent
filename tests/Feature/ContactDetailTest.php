<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Contact;
use App\Models\Opportunity;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactDetailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_contact_detail_page_shows_company_and_its_opportunities(): void
    {
        $sales = User::factory()->create()->assignRole('Sales');
        $company = Company::factory()->create(['name' => 'Contact Detail Co']);
        $contact = Contact::factory()->create(['company_id' => $company->id, 'name' => 'Detail Contact']);
        Opportunity::factory()->create(['company_id' => $company->id]);

        $response = $this->actingAs($sales)->get(route('contacts.show', $contact));

        $response->assertOk();
        $response->assertSee('Detail Contact');
        $response->assertSee('Contact Detail Co');
    }

    public function test_start_outreach_action_is_present_but_disabled(): void
    {
        $sales = User::factory()->create()->assignRole('Sales');
        $contact = Contact::factory()->create();

        $response = $this->actingAs($sales)->get(route('contacts.show', $contact));

        $response->assertSee('Start Outreach');
        $response->assertSee('disabled', false);
    }
}
