<?php

namespace Database\Factories;

use App\Enums\Capacity;
use App\Enums\TherapistStatus;
use App\Models\Therapist;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Therapist>
 */
class TherapistFactory extends Factory
{
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'city' => fake()->city(),
            'offers_in_person' => true,
            'insurance_types' => ['statutory', 'private', 'self_pay'],
            'languages' => ['Deutsch'],
            'status' => TherapistStatus::Active,
            'capacity' => Capacity::Available,
            'first_session_fee' => 120,
        ];
    }
}
