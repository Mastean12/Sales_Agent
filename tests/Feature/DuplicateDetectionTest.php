<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Contact;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DuplicateDetectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_creating_a_company_with_an_existing_domain_is_blocked_with_a_warning(): void
    {
        $sales = User::factory()->create()->assignRole('Sales');
        Company::factory()->create(['name' => 'Existing Co', 'domain' => 'existing.com']);

        $response = $this->actingAs($sales)->post(route('companies.store'), [
            'name' => 'Duplicate Co',
            'domain' => 'existing.com',
            'status' => 'prospecting',
        ]);

        $response->assertSessionHasErrors('domain');
        $this->assertDatabaseMissing('companies', ['name' => 'Duplicate Co']);
        $this->assertDatabaseCount('companies', 1);
    }

    public function test_company_domain_matching_is_case_insensitive(): void
    {
        $sales = User::factory()->create()->assignRole('Sales');
        Company::factory()->create(['name' => 'Existing Co', 'domain' => 'existing.com']);

        $response = $this->actingAs($sales)->post(route('companies.store'), [
            'name' => 'Duplicate Co',
            'domain' => 'EXISTING.com',
            'status' => 'prospecting',
        ]);

        $response->assertSessionHasErrors('domain');
    }

    public function test_creating_a_contact_with_a_duplicate_email_at_the_same_company_is_blocked(): void
    {
        $sales = User::factory()->create()->assignRole('Sales');
        $company = Company::factory()->create();
        Contact::factory()->create(['company_id' => $company->id, 'email' => 'dup@example.com']);

        $response = $this->actingAs($sales)->post(route('contacts.store'), [
            'company_id' => $company->id,
            'name' => 'Second Person',
            'email' => 'dup@example.com',
            'communication_status' => 'not_contacted',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertDatabaseCount('contacts', 1);
    }

    public function test_same_email_is_allowed_across_different_companies(): void
    {
        $sales = User::factory()->create()->assignRole('Sales');
        Contact::factory()->create(['email' => 'shared@example.com']);
        $otherCompany = Company::factory()->create();

        $response = $this->actingAs($sales)->post(route('contacts.store'), [
            'company_id' => $otherCompany->id,
            'name' => 'Second Person',
            'email' => 'shared@example.com',
            'communication_status' => 'not_contacted',
        ]);

        $response->assertSessionDoesntHaveErrors('email');
        $this->assertDatabaseCount('contacts', 2);
    }
}
