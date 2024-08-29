<?php

namespace Database\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Events>
 */
class EventsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title'       => $this->faker->name,
            'url'         => $this->faker->url,
            'is_allday'   => fake()->boolean(),
            'type'        => $this->faker->randomElement(['P', 'B', 'F', 'H', 'E']),
            'start_date'  => Carbon::now()->subDays(rand(0, 30)),
            'end_date'    => Carbon::now()->addDays(rand(0, 30)),
        ];
    }
}
