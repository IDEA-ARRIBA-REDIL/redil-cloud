<?php

namespace Database\Seeders;

use App\Models\UserAdminRedil;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        UserAdminRedil::firstOrCreate(
            ['email' => 'idea.arriba@gmail.com'],
            [
                'name' => 'Admin IDEA',
                'password' => Hash::make('password'),
                'is_suspended' => false,
            ]
        );

        UserAdminRedil::firstOrCreate(
            ['email' => 'elcorreodedarwin90@gmail.com'],
            [
                'name' => 'Darwin Admin',
                'password' => Hash::make('password'),
                'is_suspended' => false,
            ]
        );
    }
}
