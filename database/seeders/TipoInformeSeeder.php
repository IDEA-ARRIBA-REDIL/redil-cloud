<?php

namespace Database\Seeders;

use App\Models\TipoInforme;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TipoInformeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TipoInforme::firstOrCreate([
          'nombre' => 'Grupos'
        ]);

        TipoInforme::firstOrCreate([
          'nombre' => 'Personas'
        ]);
    }
}
