<?php

namespace Database\Factories;

use App\Enums\CompanyStatus;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Company>
 */
class CompanyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'status' => CompanyStatus::Active,
            'city' => fake()->city(),
            'first_session_price' => 180,
            'success_fee' => 450,
        ];
    }
}
