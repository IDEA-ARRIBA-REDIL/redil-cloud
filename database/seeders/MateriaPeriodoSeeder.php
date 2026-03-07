<?php

namespace Database\Seeders;


use App\Models\MateriaPeriodo;
use App\Models\Sede;
use App\Models\TipoAula;
use App\Models\Aula;
use App\Models\HorarioBase;
use App\Models\HorarioMateriaPeriodo;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class MateriaPeriodoSeeder extends Seeder
{
    public function run(): void
    {     /*
         // Asociaciones para Escuela Dominical
         for($i = 1; $i <= 4; $i++) {
            MateriaPeriodo::firstOrCreate([
                'materia_id' => $i,
                'periodo_id' => 3,
                'habilitar_calificaciones' => true
            ]);
        }

        // Asociaciones para Escuela Liderazgo

        $periodosLiderazgo = [2, 3];
        $materiasLiderazgo = range(5, 10); // IDs 5-10 son las materias de liderazgo

        foreach($periodosLiderazgo as $periodoId) {
            foreach($materiasLiderazgo as $materiaId) {
                MateriaPeriodo::firstOrCreate([
                    'materia_id' => $materiaId,
                    'periodo_id' => $periodoId,
                    'habilitar_asistencias' => true
                ]);
            }
        }*/

    }
}
