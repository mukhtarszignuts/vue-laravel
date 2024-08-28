<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'            => $this->faker->word(),
            'description'     => $this->faker->sentence(),
            'is_active'       => fake()->boolean(),
            'display_order'   => $this->faker->numberBetween(0, 10),
            'price'           => $this->faker->numberBetween(10, 1000),
            'stock'           => $this->faker->numberBetween(10, 1000),
            'category_id'     => Category::inRandomOrder()->first()->id ?? Category::factory(),
            'sub_category_id' => SubCategory::inRandomOrder()->first()->id ?? SubCategory::factory(),
            'image'           => $this->faker->imageUrl(640, 480, 'products', true),
            
        ];
    }
}
