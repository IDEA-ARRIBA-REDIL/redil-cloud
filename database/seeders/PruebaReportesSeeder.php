<?php

namespace Database\Seeders;

use App\Models\Grupo;
use App\Models\ReporteGrupo;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PruebaReportesSeeder extends Seeder
{
    public function run()
    {
        $grupoId = 2;
        $grupo = Grupo::find($grupoId);
        if (! $grupo) {
            echo "Grupo no encontrado.\n";

            return;
        }

        $encargados = $grupo->encargados()->get();
        $asistentes = $grupo->asistentes()->get();

        // Crear 8 reportes (últimas 8 semanas)
        for ($i = 7; $i >= 0; $i--) {
            // Empezar desde hace 8 semanas, e ir avanzando hacia hoy
            $fecha = Carbon::now()->subWeeks($i)->startOfWeek()->addDays(2); // Miércoles

            $infoEncargados = [];
            foreach ($encargados as $enc) {
                $infoEncargados[] = [
                    'id' => $enc->id,
                    'asistio' => rand(0, 100) > 20, // 80% chance de asistir
                ];
            }

            $reporte = ReporteGrupo::create([
                'grupo_id' => $grupoId,
                'fecha' => $fecha->format('Y-m-d'),
                'tema' => 'Tema de prueba '.(8 - $i),
                'observacion' => 'Reporte generado automáticamente para pruebas',
                'reporte_a_tiempo' => 1,
                'informacion_encargado_grupo' => $infoEncargados,
                'cantidad_asistencias' => 0,
                'cantidad_inasistencias' => 0,
                'aprobado' => 1,
                'cerrado' => 1,
                'finalizado' => 1,
                'autor_creacion' => 1,
            ]);

            $asistenciasCount = 0;
            $inasistenciasCount = 0;

            foreach ($asistentes as $asis) {
                $asistio = rand(0, 100) > 30; // 70% chance de asistir
                if ($asistio) {
                    $asistenciasCount++;
                } else {
                    $inasistenciasCount++;
                }

                $reporte->usuarios()->attach($asis->id, [
                    'asistio' => $asistio,
                ]);
            }

            $reporte->cantidad_asistencias = $asistenciasCount;
            $reporte->cantidad_inasistencias = $inasistenciasCount;
            $reporte->save();
        }

        echo "8 reportes generados con éxito para el grupo {$grupoId} (Grupo: {$grupo->nombre}).\n";
    }
}
