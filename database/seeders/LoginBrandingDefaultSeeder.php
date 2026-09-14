<?php

namespace Database\Seeders;

use App\Models\LoginBrandingDefault;
use Illuminate\Database\Seeder;

class LoginBrandingDefaultSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branding = LoginBrandingDefault::query()->firstOrCreate(
            ['singleton_key' => true],
            ['carousel_interval_ms' => 6000]
        );

        $branding->slides()->firstOrCreate(
            ['image_path' => 'Banner-login.png'],
            ['alt_text' => 'Software Redil', 'sort_order' => 0, 'is_active' => true]
        );
    }
}
