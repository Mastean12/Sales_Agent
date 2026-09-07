<?php

namespace App\Actions\Companies;

use App\Enums\AuditAction;
use App\Models\Company;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\DB;

class UpdateCompanyAction
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function execute(Company $company, array $data, User $actor): Company
    {
        return DB::transaction(function () use ($company, $data, $actor) {
            $before = $company->getAttributes();

            $company->update($data);

            $this->auditLogger->record(
                auditable: $company,
                action: AuditAction::Updated,
                actor: $actor,
                before: $before,
                after: $company->getAttributes(),
            );

            return $company;
        });
    }
}
