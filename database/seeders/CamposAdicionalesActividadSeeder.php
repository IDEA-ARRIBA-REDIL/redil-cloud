<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CamposAdicionalesActividadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $campos = [
            ['nombre' => 'Talla de camiseta', 'obligatorio' => false],
            ['nombre' => 'Prescripción médica	', 'obligatorio' => false],
            ['nombre' => 'Nombre de tu Pastor', 'obligatorio' => false],
        ];

        foreach ($campos as $campo) {
            \App\Models\CamposAdicionalesActividad::firstOrCreate(
                ['nombre' => $campo['nombre']],
                $campo
            );
        }
    }
}
