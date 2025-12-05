<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $activityLevels = ['sedentary', 'lightly_active', 'very_active', 'highly_active'];
        $goalOptions = ['eat_healthy', 'lose_weight', 'gain_weight', 'build_muscle', 'maintain_weight'];
        $hasFoodAllergies = fake()->boolean;

        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
            'goal' => fake()->randomElement($goalOptions),
            'activity_level' => fake()->randomElement($activityLevels),
            'has_food_allergies' => $hasFoodAllergies,
            'allergies' => $hasFoodAllergies ? fake()->randomElements(['milk', 'tree nuts', 'eggs', 'peanuts', 'shellfish', 'soybeans', 'wheat', 'sesame'], fake()->numberBetween(1, 3)) : [],
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return $this
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
