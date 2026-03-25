<?php

namespace Database\Seeders;

use App\Models\TipoAsignacion;
use Illuminate\Database\Seeder;

class TipoAsignacionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TipoAsignacion::firstOrCreate([
            'nombre' => 'No diligenciado',
            'default' => true,
            'para_asignar_lideres' => false,
            'para_asignar_asistentes' => false,
            'para_desvincular_asistentes' => false,
            'para_desvincular_lideres' => false,
        ]);

        TipoAsignacion::firstOrCreate([
            'nombre' => 'Otro',
            'default' => false,
            'para_asignar_lideres' => true,
            'para_asignar_asistentes' => true,
            'para_desvincular_asistentes' => true,
            'para_desvincular_lideres' => true,
        ]);

        TipoAsignacion::firstOrCreate([
            'nombre' => 'Equivocación',
            'default' => false,
            'para_asignar_lideres' => true,
            'para_asignar_asistentes' => true,
            'para_desvincular_asistentes' => true,
            'para_desvincular_lideres' => true,
        ]);

        TipoAsignacion::firstOrCreate([
            'nombre' => 'Nuevo asistente',
            'default' => false,
            'para_asignar_lideres' => false,
            'para_asignar_asistentes' => true,
            'para_desvincular_asistentes' => false,
            'para_desvincular_lideres' => false,
        ]);

        TipoAsignacion::firstOrCreate([
            'nombre' => 'No desea volver',
            'default' => false,
            'para_asignar_lideres' => false,
            'para_asignar_asistentes' => false,
            'para_desvincular_asistentes' => true,
            'para_desvincular_lideres' => false,
        ]);

        TipoAsignacion::firstOrCreate([
            'nombre' => 'Translado',
            'default' => false,
            'para_asignar_lideres' => true,
            'para_asignar_asistentes' => true,
            'para_desvincular_asistentes' => false,
            'para_desvincular_lideres' => false,
        ]);
    }
}
