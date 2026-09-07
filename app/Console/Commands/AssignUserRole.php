<?php

namespace App\Console\Commands;

use App\Enums\RoleName;
use App\Models\User;
use Illuminate\Console\Command;

/**
 * Minimal operational path for granting Control Plane access. Self-service
 * registration (Breeze) deliberately does NOT auto-assign a role — an
 * unassigned account can sign in but sees a friendly "access pending" page
 * (resources/views/errors/403.blade.php) until an administrator runs this.
 * A full Administration/user-management UI is out of scope for this phase
 * (execution plan section 3 lists it as a later module).
 */
class AssignUserRole extends Command
{
    protected $signature = 'users:assign-role {email} {role}';

    protected $description = 'Assign one of the six Control Plane roles to a user by email';

    public function handle(): int
    {
        $email = $this->argument('email');
        $role = $this->argument('role');

        if (! in_array($role, RoleName::values(), true)) {
            $this->error("Unknown role \"{$role}\". Valid roles: ".implode(', ', RoleName::values()));

            return self::FAILURE;
        }

        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->error("No user found with email \"{$email}\".");

            return self::FAILURE;
        }

        $user->syncRoles([$role]);

        $this->info("Assigned \"{$role}\" to {$user->name} <{$user->email}>.");

        return self::SUCCESS;
    }
}
