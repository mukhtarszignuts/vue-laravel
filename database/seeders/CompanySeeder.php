<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Company::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        $public_disk = Storage::disk('public');

        $images = $public_disk->files('logos');

       
        $directory = 'company/logo/';

        // Delete all files in the specified directory
        Storage::disk('public')->deleteDirectory($directory);

        // Optionally, you can also recreate the directory if needed
        Storage::disk('public')->makeDirectory($directory);

        foreach ($images as $key => $image) {
            $image_name = basename($image);
            $image_content = $public_disk->get($image);
            Storage::disk('public')->put($directory . $image_name, $image_content);
            
            $comapnies[$key] = [
                'id'            => ++$key,
                'name'          => fake()->name(),
                'address'       => fake()->address,
                'is_active'     => fake()->boolean(),
                'employee_size' => [25, 50, 75, 100][array_rand([25, 50, 75, 100])],
                'owner_id'      => null,
                'logo'          => $image_name,
                'created_at'    => now(),
            ];
        }
        
        Company::insert($comapnies);
    }
}
