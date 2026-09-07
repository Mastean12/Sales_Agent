<?php

namespace App\Policies;

use App\Models\Contact;
use App\Models\User;

class ContactPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->roles()->exists();
    }

    public function view(User $user, Contact $contact): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Sales', 'Sales Manager', 'Founder/Management', 'Admin']);
    }

    public function update(User $user, Contact $contact): bool
    {
        return $user->hasAnyRole(['Sales', 'Sales Manager', 'Founder/Management', 'Admin']);
    }

    public function delete(User $user, Contact $contact): bool
    {
        return $user->hasAnyRole(['Founder/Management', 'Admin']);
    }
}
