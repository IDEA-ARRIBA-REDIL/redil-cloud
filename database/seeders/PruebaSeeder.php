<?php

namespace Database\Seeders;

use App\Models\Prueba;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PruebaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //

        Prueba::firstOrCreate([
            'nombre' => 'prueba',

        ]);
    }
}
