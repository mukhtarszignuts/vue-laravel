<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Nette\Utils\Random;

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
        return [
            'name'              => fake()->name(),
            'email'             => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password'          => bcrypt('password'), // password hashing
            'remember_token'    => Str::random(10),
            'phone'             => fake()->phoneNumber,
            'city'              => fake()->city,
            'role'              => fake()->randomElement(['M', 'C' ,'E']),
            'status'            => fake()->randomElement(['A', 'I', 'P']),
            'is_owner'          => fake()->boolean(),
            // 'company_id'        => Company::inRandomOrder()->first()->id ?? Company::factory()->create()->id
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
