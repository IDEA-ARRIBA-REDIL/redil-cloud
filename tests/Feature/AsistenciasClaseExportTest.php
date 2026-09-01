<?php

namespace Tests\Feature;

use App\Exports\AsistenciasClaseExport;
use App\Models\HorarioMateriaPeriodo;
use App\Models\MotivoInasistencia;
use App\Models\ReporteAsistenciaAlumnos;
use App\Models\ReporteAsistenciaClase;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class AsistenciasClaseExportTest extends TestCase
{
    public function test_it_builds_a_row_for_an_attendance_detail(): void
    {
        $alumno = new User([
            'primer_nombre' => 'Ana',
            'segundo_nombre' => 'María',
            'primer_apellido' => 'Pérez',
            'segundo_apellido' => 'López',
            'identificacion' => '123456789',
            'email' => 'ana@example.test',
        ]);
        $reportadoPor = new User([
            'primer_nombre' => 'Carlos',
            'segundo_nombre' => 'Andrés',
            'primer_apellido' => 'Gómez',
            'segundo_apellido' => 'Ruiz',
        ]);
        $detalle = new ReporteAsistenciaAlumnos([
            'asistio' => false,
            'auto_asistencia' => true,
            'observaciones_alumno' => 'Presentó excusa médica.',
        ]);
        $detalle->setRelation('alumno', $alumno);
        $detalle->setRelation('motivoInasistencia', new MotivoInasistencia(['nombre' => 'Incapacidad médica']));

        $reporte = new ReporteAsistenciaClase([
            'fecha_clase_reportada' => Carbon::parse('2026-09-01'),
            'estado_reporte' => 'completado',
            'observaciones_generales' => 'Se revisó la lección 3.',
        ]);
        $reporte->setRelation('reportadoPor', $reportadoPor);

        $exportacion = new AsistenciasClaseExport(new HorarioMateriaPeriodo);

        $this->assertSame([
            'Fecha de clase',
            'Estado del reporte',
            'Alumno',
            'Identificación',
            'Correo electrónico',
            'Asistencia',
            'Motivo de inasistencia',
            'Autoasistencia',
            'Observaciones del alumno',
            'Observaciones generales',
            'Reportado por',
        ], $exportacion->headings());

        $this->assertSame([
            '01/09/2026',
            'completado',
            'Ana María Pérez López',
            '123456789',
            'ana@example.test',
            'Ausente',
            'Incapacidad médica',
            'Sí',
            'Presentó excusa médica.',
            'Se revisó la lección 3.',
            'Carlos Andrés Gómez Ruiz',
        ], $exportacion->map($reporte, $detalle));
    }

    public function test_it_keeps_reports_without_attendance_details_in_the_export(): void
    {
        $reporte = new ReporteAsistenciaClase([
            'fecha_clase_reportada' => Carbon::parse('2026-09-01'),
            'estado_reporte' => 'pendiente_detalle',
        ]);

        $exportacion = new AsistenciasClaseExport(new HorarioMateriaPeriodo);

        $this->assertSame('Sin registros de asistencia', $exportacion->map($reporte)[2]);
    }
}
