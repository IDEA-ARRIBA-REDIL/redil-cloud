<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Materia;
use App\Models\Aula;
use App\Models\HorarioBase;
use Carbon\Carbon;

class HorarioBaseSeeder extends Seeder
{
    public function run(): void
    {
        // Franjas horarias fijas para alternar
        $franjas = [
            ['07:00', '09:00'],
            ['10:00', '12:00'],
            ['15:00', '17:00'],
            ['18:00', '20:00'],
        ];

        // Recorrer todas las materias
        Materia::all()->each(function (Materia $materia) use ($franjas) {
            // Elegir aulas según la escuela de la materia
            $aulaIds = $materia->escuela_id === 1 ? [1, 2] : [3, 4];

            // Crear dos horarios base por materia–aula
            for ($i = 0; $i < 4; $i++) {
                $aulaId    = $aulaIds[$i % count($aulaIds)];
                $franja    = $franjas[$i % count($franjas)];
                $diaSemana = ($materia->id % 7) + 1; // 1=Lunes … 7=Domingo

                // Obtener la capacidad del aula (si existe)
                $cap = Aula::find($aulaId)?->capacidad ?? 20;

                HorarioBase::firstOrCreate([
                    'materia_id'      => $materia->id,
                    'aula_id'         => $aulaId,
                    'dia'             => $diaSemana,
                    'hora_inicio'     => $franja[0],
                    'hora_fin'        => $franja[1],
                    'capacidad'       => $cap,
                    'capacidad_limite'=> $cap * 2,
                    'created_at'      => Carbon::now(),
                    'updated_at'      => Carbon::now(),
                ]);
            }
        });
    }
}
