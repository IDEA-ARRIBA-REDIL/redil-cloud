<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MateriaPeriodo;
use App\Models\Sede;
use App\Models\TipoAula;
use App\Models\Aula;
use App\Models\HorarioBase;
use App\Models\HorarioMateriaPeriodo;
use Carbon\Carbon;

class HorarioMateriaPeriodoSeeder extends Seeder
{
    public function run(): void
    {
        $materiaPeriodos = MateriaPeriodo::all();
        $sedes            = Sede::all();
        $tiposAula        = TipoAula::all();

        foreach ($materiaPeriodos as $mp) {
            foreach ($sedes as $sede) {
                foreach ($tiposAula as $tipo) {
                    // Tomamos hasta 2 aulas de este tipo en esta sede
                    $aulas = Aula::where('sede_id', $sede->id)
                                 ->where('tipo_aula_id', $tipo->id)                              
                                 ->get();

                    foreach ($aulas as $aula) {
                        // Para cada aula, buscamos hasta 3 horarios base de esta materia
                        $horariosBase = HorarioBase::where('materia_id', $mp->materia_id)
                                                   ->where('aula_id', $aula->id)
                                                   ->take(3)
                                                   ->get();

                        foreach ($horariosBase as $hb) {
                            HorarioMateriaPeriodo::firstOrCreate([
                                'materia_periodo_id'     => $mp->id,
                                'horario_base_id'        => $hb->id,
                                'habilitado'             => true,
                                'capacidad'              => 30,
                                'capacidad_limite'       => (int) ($aula->capacidad * 1.5),
                                'cupos_disponibles'      => 8,
                                'ampliar_cupos_limite'   => false,
                                'created_at'             => now(),
                                'updated_at'             => now(),
                            ]);
                        }
                    }
                }
            }
        }
    }
}
