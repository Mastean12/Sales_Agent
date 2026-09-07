<?php

namespace App\Actions\Contacts;

use App\Enums\AuditAction;
use App\Models\Contact;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\DB;

class UpdateContactAction
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function execute(Contact $contact, array $data, User $actor): Contact
    {
        return DB::transaction(function () use ($contact, $data, $actor) {
            $before = $contact->getAttributes();

            $contact->update($data);

            $this->auditLogger->record(
                auditable: $contact,
                action: AuditAction::Updated,
                actor: $actor,
                before: $before,
                after: $contact->getAttributes(),
            );

            return $contact;
        });
    }
}
