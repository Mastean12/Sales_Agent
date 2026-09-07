<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Contact;
use App\Models\Opportunity;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The other feature tests mostly POST/PUT/PATCH directly. This makes sure
 * every GET page (including forms, which aren't otherwise visited) actually
 * renders without a Blade/view error for an authorized user.
 */
class PageRenderTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_control_plane_page_renders_for_an_admin(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $admin = User::factory()->create()->assignRole('Admin');

        $company = Company::factory()->create();
        $contact = Contact::factory()->create(['company_id' => $company->id]);
        $opportunity = Opportunity::factory()->create(['company_id' => $company->id]);

        $this->actingAs($admin);

        $routes = [
            route('dashboard'),
            route('companies.index'),
            route('companies.create'),
            route('companies.show', $company),
            route('companies.edit', $company),
            route('contacts.index'),
            route('contacts.create'),
            route('contacts.show', $contact),
            route('contacts.edit', $contact),
            route('opportunities.index'),
            route('opportunities.index', ['view' => 'pipeline']),
            route('opportunities.create'),
            route('opportunities.show', $opportunity),
            route('opportunities.edit', $opportunity),
        ];

        foreach ($routes as $url) {
            $this->get($url)->assertOk();
        }
    }
}
