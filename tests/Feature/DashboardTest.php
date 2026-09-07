<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_view_the_dashboard(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $user = User::factory()->create()->assignRole('Sales');

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSeeText('Revenue Pipeline');
    }

    public function test_horizon_dashboard_is_gated_to_admin_and_founder_management(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $sales = User::factory()->create()->assignRole('Sales');
        $admin = User::factory()->create()->assignRole('Admin');

        $this->assertFalse($sales->can('viewHorizon'));
        $this->assertTrue($admin->can('viewHorizon'));
    }
}
