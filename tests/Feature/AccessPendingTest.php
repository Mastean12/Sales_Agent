<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Custom error views (resources/views/errors/403.blade.php) only render when
 * APP_DEBUG is false — in debug mode Laravel shows the full exception page
 * instead. This is what actually happens for a real user in staging/
 * production, so the test forces debug off to exercise that branch.
 */
class AccessPendingTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_with_no_role_sees_a_friendly_access_pending_message(): void
    {
        config(['app.debug' => false]);

        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('companies.index'));

        $response->assertStatus(403);
        $response->assertSee('Access pending');
        $response->assertSee('php artisan users:assign-role', false);
    }

    public function test_a_user_with_a_role_but_insufficient_permission_sees_the_generic_message(): void
    {
        config(['app.debug' => false]);
        $this->seed(RolesAndPermissionsSeeder::class);

        $finance = User::factory()->create();
        $finance->assignRole('Finance');
        $company = Company::factory()->create();

        $response = $this->actingAs($finance)->delete(route('companies.destroy', $company));

        $response->assertStatus(403);
        $response->assertSee('Not authorized');
        $response->assertDontSee('Access pending');
    }
}
