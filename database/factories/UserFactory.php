<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
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
        return [
            'full_name' => fake()->firstName . " " . fake()->lastName(),
            'display_name' => fake()->unique()->name(),
            'handle' => fake()->unique()->userName(),
            'email' => fake()->unique()->freeEmail(),
            'bio' => fake()->realText(),
            'avatar' => fake()->imageUrl(width: 480),
            'banner' => fake()->imageUrl(),
            'location' => fake()->city(),
            'dob' => fake()->date(),
            'email_verified_at' => now(),
            'password' => Hash::make("123456seed"),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
