<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
// use Intervention\Image\Facades\Image;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Product::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        $public_disk = Storage::disk('public');

        $images = $public_disk->files('avatars');

        //product list 
        $products = [];

        $directory = 'product_images/';

        // Delete all files in the specified directory
        Storage::disk('public')->deleteDirectory($directory);

        // Optionally, you can also recreate the directory if needed
        Storage::disk('public')->makeDirectory($directory);

        foreach ($images as $key => $image) {
            $image_name = basename($image);
            $image_content = $public_disk->get($image);
            Storage::disk('public')->put($directory . $image_name, $image_content);
            $products[$key] = [
                'id'              => ++$key,
                'name'            => fake()->name,
                'description'     => fake()->sentence(),
                'is_active'       => fake()->boolean(),
                'display_order'   => fake()->numberBetween(0, 10),
                'price'           => fake()->numberBetween(0, 999),
                'stock'           => fake()->numberBetween(0, 999),
                'category_id'     => Category::inRandomOrder()->first()->id ?? Category::factory(),
                'sub_category_id' => SubCategory::inRandomOrder()->first()->id ?? SubCategory::factory(),
                'image'           => $image_name,
                'created_at'      => now(),
            ];
        }
        
        Product::insert($products);
    }
}
