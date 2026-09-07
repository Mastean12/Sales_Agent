<?php

namespace Database\Factories;

use App\Enums\OpportunityStage;
use App\Models\Company;
use App\Models\Opportunity;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Opportunity>
 */
class OpportunityFactory extends Factory
{
    protected $model = Opportunity::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'owner_id' => User::factory(),
            'stage' => OpportunityStage::TargetAccount,
            'problem' => fake()->sentence(12),
            'value' => fake()->randomFloat(2, 5000, 250000),
            'probability' => fake()->numberBetween(0, 100),
            'next_action' => fake()->sentence(8),
        ];
    }

    public function inStage(OpportunityStage $stage): static
    {
        return $this->state(fn () => ['stage' => $stage]);
    }
}
