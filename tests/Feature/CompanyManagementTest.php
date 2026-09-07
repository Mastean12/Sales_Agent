<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_guests_cannot_view_companies(): void
    {
        $this->get(route('companies.index'))->assertRedirect(route('login'));
    }

    public function test_sales_can_create_a_company(): void
    {
        $sales = User::factory()->create()->assignRole('Sales');

        $response = $this->actingAs($sales)->post(route('companies.store'), [
            'name' => 'Acme Manufacturing',
            'status' => 'prospecting',
        ]);

        $company = Company::firstWhere('name', 'Acme Manufacturing');
        $response->assertRedirect(route('companies.show', $company));

        $this->assertDatabaseHas('companies', ['name' => 'Acme Manufacturing']);
        $this->assertDatabaseHas('audit_events', [
            'auditable_type' => $company->getMorphClass(),
            'auditable_id' => $company->id,
            'action' => 'created',
            'actor_id' => $sales->id,
        ]);
    }

    public function test_technical_delivery_cannot_create_a_company(): void
    {
        $technical = User::factory()->create()->assignRole('Technical/Delivery');

        $response = $this->actingAs($technical)->post(route('companies.store'), [
            'name' => 'Should Not Be Created',
            'status' => 'prospecting',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('companies', ['name' => 'Should Not Be Created']);
    }

    public function test_only_founder_management_or_admin_can_delete_a_company(): void
    {
        $company = Company::factory()->create();
        $sales = User::factory()->create()->assignRole('Sales');

        $this->actingAs($sales)
            ->delete(route('companies.destroy', $company))
            ->assertForbidden();

        $founder = User::factory()->create()->assignRole('Founder/Management');

        $this->actingAs($founder)
            ->delete(route('companies.destroy', $company))
            ->assertRedirect(route('companies.index'));

        $this->assertDatabaseMissing('companies', ['id' => $company->id]);
    }
}
