<?php

namespace Tests\Feature;

use App\Exports\DashboardReporteReunionExport;
use Tests\TestCase;

class DashboardReporteReunionExportTest extends TestCase
{
    /**
     * Verifica que el exportador construya correctamente las 4 hojas esperadas.
     */
    public function test_it_creates_four_sheets_for_dashboard_export(): void
    {
        $datosPrueba = [
            'filtros_aplicados' => [
                'fecha_inicio' => '2026-08-01',
                'fecha_fin' => '2026-08-31',
                'estado' => 'finalizado',
            ],
            'kpis' => [
                'total_reportes' => 4,
                'asistencias_acumuladas' => 250,
                'asistencias_miembros' => 230,
                'invitados_totales' => 20,
                'personas_unicas' => 120,
                'promedio_asistencias' => 62.5,
                'cobertura_congregacion' => 76.5,
                'poblacion_elegible_acumulada' => 300,
                'aforo_total' => 400,
                'ocupacion_aforo' => 62.5,
            ],
            'tabla_reuniones' => [
                [
                    'reunion_nombre' => 'Culto Dominical',
                    'tipo_servicio' => 'General',
                    'sede_nombre' => 'Sede Principal',
                    'reportes_count' => 4,
                    'asistencias_totales' => 250,
                    'asistencias_miembros' => 230,
                    'invitados' => 20,
                    'personas_unicas' => 120,
                    'promedio_reporte' => 62.5,
                    'cobertura' => 76.5,
                    'poblacion_elegible' => 300,
                ],
            ],
            'bloques_sede' => [
                [
                    'sede_nombre' => 'Sede Principal',
                    'kpis' => [
                        'total_reportes' => 4,
                        'asistencias_acumuladas' => 250,
                        'personas_unicas' => 120,
                        'promedio_asistencias' => 62.5,
                        'cobertura_congregacion' => 76.5,
                        'invitados_totales' => 20,
                    ],
                ],
            ],
            'demografia' => [
                'por_tipo_usuario' => [
                    ['etiqueta' => 'Miembro', 'total' => 200, 'porcentaje' => 87.0],
                    ['etiqueta' => 'Líder', 'total' => 30, 'porcentaje' => 13.0],
                ],
                'por_genero' => [
                    ['etiqueta' => 'Mujeres', 'total' => 130, 'porcentaje' => 56.5],
                    ['etiqueta' => 'Hombres', 'total' => 100, 'porcentaje' => 43.5],
                ],
            ],
        ];

        $export = new DashboardReporteReunionExport($datosPrueba);
        $sheets = $export->sheets();

        $this->assertCount(4, $sheets);
        $this->assertSame('Resumen General', $sheets[0]->title());
        $this->assertSame('Por Reunión', $sheets[1]->title());
        $this->assertSame('Por Sede', $sheets[2]->title());
        $this->assertSame('Demografía', $sheets[3]->title());

        // Verificar contenido de la hoja Resumen General
        $resumenArray = $sheets[0]->array();
        $this->assertNotEmpty($resumenArray);
        $this->assertSame('Reportes Finalizados Incluidos', $resumenArray[4][0]);
        $this->assertSame(4, $resumenArray[4][1]);
        $this->assertSame('Asistencias Acumuladas', $resumenArray[5][0]);
        $this->assertSame(250, $resumenArray[5][1]);

        // Verificar hoja Por Reunión
        $reunionesArray = $sheets[1]->array();
        $this->assertCount(1, $reunionesArray);
        $this->assertSame('Culto Dominical', $reunionesArray[0][0]);
        $this->assertSame('76.5%', $reunionesArray[0][9]);
    }
}
