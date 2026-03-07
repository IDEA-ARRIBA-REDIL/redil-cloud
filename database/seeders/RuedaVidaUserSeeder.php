<?php

namespace Database\Seeders;

use App\Models\RuedaDeLaVida;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RuedaDeLaVidaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //

        RuedaDeLaVida::firstOrCreate([
            'user_id' => '1',
            'promedio_general' => '5.4',
            'fecha' => '2025-02-19'

        ]);
    }
}
