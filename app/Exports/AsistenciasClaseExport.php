<?php

namespace App\Exports;

use App\Models\HorarioMateriaPeriodo;
use App\Models\ReporteAsistenciaAlumnos;
use App\Models\ReporteAsistenciaClase;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AsistenciasClaseExport implements FromCollection, ShouldAutoSize, WithHeadings
{
    public function __construct(
        private readonly HorarioMateriaPeriodo $horarioAsignado,
        private readonly ?ReporteAsistenciaClase $reporte = null,
    ) {}

    /**
     * @return Collection<int, array<int, string>>
     */
    public function collection(): Collection
    {
        $reportes = ReporteAsistenciaClase::query()
            ->where('horario_materia_periodo_id', $this->horarioAsignado->id)
            ->when($this->reporte, fn ($query) => $query->whereKey($this->reporte->id))
            ->with([
                'reportadoPor',
                'detallesAsistencia.alumno',
                'detallesAsistencia.motivoInasistencia',
            ])
            ->orderBy('fecha_clase_reportada')
            ->get();

        return $reportes->flatMap(function (ReporteAsistenciaClase $reporte): Collection {
            if ($reporte->detallesAsistencia->isEmpty()) {
                return collect([$this->map($reporte)]);
            }

            return $reporte->detallesAsistencia
                ->sortBy(fn (ReporteAsistenciaAlumnos $detalle) => $detalle->alumno?->nombre(4))
                ->map(fn (ReporteAsistenciaAlumnos $detalle) => $this->map($reporte, $detalle));
        });
    }

    public function headings(): array
    {
        return [
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
        ];
    }

    /**
     * @return array<int, string>
     */
    public function map(ReporteAsistenciaClase $reporte, ?ReporteAsistenciaAlumnos $detalle = null): array
    {
        $alumno = $detalle?->alumno;
        $reportadoPor = $reporte->reportadoPor;

        return [
            $reporte->fecha_clase_reportada?->format('d/m/Y') ?? '',
            $reporte->estado_reporte ?? '',
            $alumno ? trim($alumno->nombre(4)) : 'Sin registros de asistencia',
            $alumno?->identificacion ?? '',
            $alumno?->email ?? '',
            $detalle ? ($detalle->asistio ? 'Presente' : 'Ausente') : '',
            $detalle?->motivoInasistencia?->nombre ?? '',
            $detalle ? ($detalle->auto_asistencia ? 'Sí' : 'No') : '',
            $detalle?->observaciones_alumno ?? '',
            $reporte->observaciones_generales ?? '',
            $reportadoPor ? trim($reportadoPor->nombre(4)) : '',
        ];
    }
}
