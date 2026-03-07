<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CamposAdicionalesActividadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $campos = [
          ['nombre'=> 'Talla de camiseta', 'obligatorio'=> FALSE],
          ['nombre'=> 'Prescripción médica	', 'obligatorio'=> FALSE],
          ['nombre'=> 'Nombre de tu Pastor', 'obligatorio'=> FALSE],
        ];

        foreach ($campos as $campo) {
          \App\Models\CamposAdicionalesActividad::firstOrCreate(
            ['nombre' => $campo['nombre']],
            $campo
          );
        }
    }
}
