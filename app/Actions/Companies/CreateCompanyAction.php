<?php

namespace App\Actions\Companies;

use App\Enums\AuditAction;
use App\Models\Company;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\DB;

class CreateCompanyAction
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function execute(array $data, User $actor): Company
    {
        return DB::transaction(function () use ($data, $actor) {
            $company = Company::create($data);

            $this->auditLogger->record(
                auditable: $company,
                action: AuditAction::Created,
                actor: $actor,
                after: $company->getAttributes(),
            );

            return $company;
        });
    }
}
