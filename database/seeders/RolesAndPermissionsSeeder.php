<?php

namespace Database\Seeders;

use App\Enums\RoleName;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Seeds the six roles from the execution plan's Governance & Security
 * sections and a permission per control-plane resource/action. Policies
 * (App\Policies\*) are the actual enforcement point and check roles
 * directly, since the plan defines access in terms of named roles; these
 * permissions exist so Blade/Livewire views can gate UI affordances with
 * "@can(...)" without hard-coding role names in every view.
 */
class RolesAndPermissionsSeeder extends Seeder
{
    private const array PERMISSIONS = [
        'companies.view', 'companies.create', 'companies.update', 'companies.delete',
        'contacts.view', 'contacts.create', 'contacts.update', 'contacts.delete',
        'opportunities.view', 'opportunities.create', 'opportunities.update', 'opportunities.delete',
        'audit.view',
        'horizon.view',
    ];

    private const array ROLE_PERMISSIONS = [
        'Admin' => self::PERMISSIONS,
        'Founder/Management' => self::PERMISSIONS,
        'Sales Manager' => [
            'companies.view', 'companies.create', 'companies.update',
            'contacts.view', 'contacts.create', 'contacts.update',
            'opportunities.view', 'opportunities.create', 'opportunities.update',
            'audit.view',
        ],
        'Sales' => [
            'companies.view', 'companies.create', 'companies.update',
            'contacts.view', 'contacts.create', 'contacts.update',
            'opportunities.view', 'opportunities.create', 'opportunities.update',
        ],
        'Technical/Delivery' => [
            'companies.view', 'contacts.view', 'opportunities.view',
        ],
        'Finance' => [
            'companies.view', 'contacts.view', 'opportunities.view', 'audit.view',
        ],
    ];

    public function run(): void
    {
        foreach (self::PERMISSIONS as $permission) {
            Permission::findOrCreate($permission);
        }

        foreach (RoleName::values() as $roleName) {
            $role = Role::findOrCreate($roleName);
            $role->syncPermissions(self::ROLE_PERMISSIONS[$roleName]);
        }
    }
}
