<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Contact;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contact>
 */
class ContactFactory extends Factory
{
    protected $model = Contact::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'name' => fake()->name(),
            'role' => fake()->randomElement(['Operations Manager', 'CTO', 'CEO', 'IT Director', 'Finance Lead']),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'source' => fake()->randomElement(['company website', 'linkedin', 'referral']),
            'communication_status' => 'not_contacted',
            'opted_out' => false,
        ];
    }
}
