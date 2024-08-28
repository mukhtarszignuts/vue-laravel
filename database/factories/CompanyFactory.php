<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Company>
 */
class CompanyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'          => fake()->name(),
            'is_active'     => fake()->boolean(),
            'employee_size' => rand(10, 50),
            'owner_id'      => User::inRandomOrder()->first()->id ?? User::factory()->create()->id,
            'address'       => $this->faker->sentence(),
            'logo'          => $this->faker->imageUrl(640, 480, 'company', true),
        ];
        
    }
}
