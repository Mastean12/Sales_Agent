<?php

namespace App\Actions\Companies;

use App\Enums\AuditAction;
use App\Models\Company;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\DB;

class DeleteCompanyAction
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function execute(Company $company, User $actor): void
    {
        DB::transaction(function () use ($company, $actor) {
            $before = $company->getAttributes();

            $company->delete();

            $this->auditLogger->record(
                auditable: $company,
                action: AuditAction::Deleted,
                actor: $actor,
                before: $before,
            );
        });
    }
}
