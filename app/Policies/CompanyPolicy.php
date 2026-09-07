<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\User;

class CompanyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->roles()->exists();
    }

    public function view(User $user, Company $company): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Sales', 'Sales Manager', 'Founder/Management', 'Admin']);
    }

    public function update(User $user, Company $company): bool
    {
        return $user->hasAnyRole(['Sales', 'Sales Manager', 'Founder/Management', 'Admin']);
    }

    public function delete(User $user, Company $company): bool
    {
        return $user->hasAnyRole(['Founder/Management', 'Admin']);
    }
}
