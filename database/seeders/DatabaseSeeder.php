<?php

namespace Database\Seeders;

use App\Enums\OpportunityStage;
use App\Models\Company;
use App\Models\Opportunity;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);

        $admin = User::factory()->create([
            'name' => 'Marixion Admin',
            'email' => 'admin@marixion.test',
        ]);
        $admin->assignRole('Admin');

        $founder = User::factory()->create([
            'name' => 'Founder Demo',
            'email' => 'founder@marixion.test',
        ]);
        $founder->assignRole('Founder/Management');

        $salesManager = User::factory()->create([
            'name' => 'Sales Manager Demo',
            'email' => 'sales.manager@marixion.test',
        ]);
        $salesManager->assignRole('Sales Manager');

        $sales = User::factory()->create([
            'name' => 'Sales Demo',
            'email' => 'sales@marixion.test',
        ]);
        $sales->assignRole('Sales');

        $technical = User::factory()->create([
            'name' => 'Technical Demo',
            'email' => 'technical@marixion.test',
        ]);
        $technical->assignRole('Technical/Delivery');

        $finance = User::factory()->create([
            'name' => 'Finance Demo',
            'email' => 'finance@marixion.test',
        ]);
        $finance->assignRole('Finance');

        if (app()->environment('local')) {
            Company::factory(8)
                ->hasContacts(2)
                ->create()
                ->each(function (Company $company) use ($sales) {
                    Opportunity::factory()->create([
                        'company_id' => $company->id,
                        'owner_id' => $sales->id,
                        'stage' => fake()->randomElement(OpportunityStage::cases()),
                    ]);
                });
        }
    }
}
