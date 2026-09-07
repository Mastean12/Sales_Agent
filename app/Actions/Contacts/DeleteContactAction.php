<?php

namespace App\Actions\Contacts;

use App\Enums\AuditAction;
use App\Models\Contact;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\DB;

class DeleteContactAction
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function execute(Contact $contact, User $actor): void
    {
        DB::transaction(function () use ($contact, $actor) {
            $before = $contact->getAttributes();

            $contact->delete();

            $this->auditLogger->record(
                auditable: $contact,
                action: AuditAction::Deleted,
                actor: $actor,
                before: $before,
            );
        });
    }
}
