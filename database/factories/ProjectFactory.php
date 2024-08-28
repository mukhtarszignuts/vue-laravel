<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'        => $this->faker->name(),
            'description' => $this->faker->sentence,
            'location'    => $this->faker->address,
            'type'        => $this->faker->randomElement(['S', 'M', 'L']),
            'start_date'  => Carbon::now()->subDays(rand(0,30)),
            'end_date'    => Carbon::now()->addDays(rand(0,30)),
            'total_budget'=> $this->faker->numberBetween(10000, 100000),
            'client_id'   => User::inRandomOrder()->first()->id ?? User::factory()->create()->id,
            'company_id'  => Company::inRandomOrder()->first()->id ?? Company::factory()->create()->id,
            'status'      => $this->faker->randomElement(['I','P','C','R','H']),
            'thumbnil'    => $this->faker->imageUrl(640, 480, 'business', true),
        ];
    }
}
