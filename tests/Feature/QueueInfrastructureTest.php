<?php

namespace Tests\Feature;

use App\Models\User;
use App\Providers\HorizonServiceProvider;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The test environment uses the "sync" queue driver (phpunit.xml), so these
 * checks don't require a live Redis server — they verify the app's queue
 * wiring is correct (a redis connection is configured for real environments,
 * Horizon's dashboard is gated correctly) and that dispatching actually
 * calls a job's handler, without depending on external infrastructure.
 *
 * A live-Redis, real-worker, real-Horizon-dashboard run was verified
 * manually against a local Redis instance — see README "Queue
 * infrastructure verification".
 */
class QueueInfrastructureTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_redis_queue_connection_is_configured_for_non_test_environments(): void
    {
        $this->assertSame('redis', config('queue.connections.redis.driver'));
        $this->assertSame('default', config('queue.connections.redis.queue'));
    }

    public function test_horizon_is_registered_and_configured(): void
    {
        $this->assertNotNull(config('horizon.environments.production') ?? config('horizon.environments.local'));
        $this->assertContains(HorizonServiceProvider::class, require base_path('bootstrap/providers.php'));
    }

    public function test_dispatching_a_job_actually_runs_it(): void
    {
        // Queued closures are serialized and reconstructed before running
        // (true under every driver, including "sync"), so a by-reference
        // variable capture wouldn't observe the real execution — a
        // filesystem side effect does.
        $marker = storage_path('framework/testing/queue-infra-test-marker.txt');
        @unlink($marker);

        dispatch(function () use ($marker) {
            file_put_contents($marker, 'ran');
        });

        $this->assertFileExists($marker, 'Expected the queued closure to run under the sync driver used for tests.');

        @unlink($marker);
    }

    public function test_only_admin_and_founder_management_can_view_horizon(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $salesManager = User::factory()->create()->assignRole('Sales Manager');
        $admin = User::factory()->create()->assignRole('Admin');
        $founder = User::factory()->create()->assignRole('Founder/Management');

        $this->assertFalse($salesManager->can('viewHorizon'));
        $this->assertTrue($admin->can('viewHorizon'));
        $this->assertTrue($founder->can('viewHorizon'));
    }
}
