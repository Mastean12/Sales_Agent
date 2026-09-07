<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Company>
 */
class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company(),
            'domain' => fake()->unique()->domainName(),
            'industry' => fake()->randomElement(['Manufacturing', 'Logistics', 'Healthcare', 'Retail', 'Financial Services']),
            'location' => fake()->city(),
            'icp_score' => fake()->numberBetween(0, 100),
            'source' => fake()->randomElement(['research', 'referral', 'inbound', 'event']),
            'status' => 'prospecting',
        ];
    }
}
