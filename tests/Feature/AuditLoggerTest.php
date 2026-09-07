<?php

namespace Tests\Feature;

use App\Enums\AuditAction;
use App\Models\AuditEvent;
use App\Models\Company;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLoggerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_records_actor_action_and_before_after_state(): void
    {
        $actor = User::factory()->create();
        $company = Company::factory()->create(['status' => 'prospecting']);

        $event = app(AuditLogger::class)->record(
            auditable: $company,
            action: AuditAction::Updated,
            actor: $actor,
            before: ['status' => 'prospecting'],
            after: ['status' => 'active'],
            reason: 'Manually verified fit.',
        );

        $this->assertSame($actor->id, $event->actor_id);
        $this->assertSame(AuditAction::Updated, $event->fresh()->action);
        $this->assertSame(['status' => 'prospecting'], $event->fresh()->before);
        $this->assertSame(['status' => 'active'], $event->fresh()->after);
        $this->assertSame($company->id, $event->auditable_id);
        $this->assertSame($company->getMorphClass(), $event->auditable_type);
    }

    public function test_audit_events_are_never_updated(): void
    {
        $this->assertNull((new AuditEvent)->getUpdatedAtColumn());
    }
}
