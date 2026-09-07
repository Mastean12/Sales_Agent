<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Contact;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_sales_can_create_a_contact_for_a_company(): void
    {
        $sales = User::factory()->create()->assignRole('Sales');
        $company = Company::factory()->create();

        $response = $this->actingAs($sales)->post(route('contacts.store'), [
            'company_id' => $company->id,
            'name' => 'Jane Doe',
            'communication_status' => 'not_contacted',
        ]);

        $contact = Contact::firstWhere('name', 'Jane Doe');
        $response->assertRedirect(route('contacts.show', $contact));
        $this->assertDatabaseHas('contacts', ['name' => 'Jane Doe', 'company_id' => $company->id]);
    }

    public function test_opting_a_contact_out_is_persisted(): void
    {
        $sales = User::factory()->create()->assignRole('Sales');
        $contact = Contact::factory()->create(['opted_out' => false]);

        $this->actingAs($sales)->put(route('contacts.update', $contact), [
            'company_id' => $contact->company_id,
            'name' => $contact->name,
            'communication_status' => 'opted_out',
            'opted_out' => '1',
        ])->assertRedirect(route('contacts.show', $contact));

        $this->assertTrue($contact->fresh()->opted_out);
    }

    public function test_finance_cannot_create_a_contact(): void
    {
        $finance = User::factory()->create()->assignRole('Finance');
        $company = Company::factory()->create();

        $this->actingAs($finance)->post(route('contacts.store'), [
            'company_id' => $company->id,
            'name' => 'Should Fail',
            'communication_status' => 'not_contacted',
        ])->assertForbidden();
    }
}
