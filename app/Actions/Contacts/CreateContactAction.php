<?php

namespace App\Actions\Contacts;

use App\Enums\AuditAction;
use App\Models\Contact;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\DB;

class CreateContactAction
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function execute(array $data, User $actor): Contact
    {
        return DB::transaction(function () use ($data, $actor) {
            $contact = Contact::create($data);

            $this->auditLogger->record(
                auditable: $contact,
                action: AuditAction::Created,
                actor: $actor,
                after: $contact->getAttributes(),
            );

            return $contact;
        });
    }
}
