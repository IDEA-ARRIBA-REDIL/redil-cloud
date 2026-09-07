<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DashboardReporteReunionExport implements WithMultipleSheets
{
    use Exportable;

    public function __construct(protected array $datos) {}

    public function sheets(): array
    {
        return [
            new ResumenGeneralSheet($this->datos),
            new PorReunionSheet($this->datos['tabla_reuniones'] ?? []),
            new PorSedeSheet($this->datos['bloques_sede'] ?? []),
            new DemografiaSheet($this->datos['demografia'] ?? []),
        ];
    }
}

class ResumenGeneralSheet implements FromArray, ShouldAutoSize, WithHeadings, WithStyles, WithTitle
{
    public function __construct(protected array $datos) {}

    public function title(): string
    {
        return 'Resumen General';
    }

    public function headings(): array
    {
        return [
            'Métrica',
            'Valor',
        ];
    }

    public function array(): array
    {
        $kpis = $this->datos['kpis'] ?? [];
        $filtros = $this->datos['filtros_aplicados'] ?? [];

        return [
            ['Período Inicial', $filtros['fecha_inicio'] ?? 'N/A'],
            ['Período Final', $filtros['fecha_fin'] ?? 'N/A'],
            ['Estado de Reportes', ucfirst($filtros['estado'] ?? 'finalizado')],
            ['---', '---'],
            ['Reportes Finalizados Incluidos', $kpis['total_reportes'] ?? 0],
            ['Asistencias Acumuladas', $kpis['asistencias_acumuladas'] ?? 0],
            ['Asistencias Miembros Registrados', $kpis['asistencias_miembros'] ?? 0],
            ['Invitados Externos Confirmados', $kpis['invitados_totales'] ?? 0],
            ['Personas Únicas en el Período', $kpis['personas_unicas'] ?? 0],
            ['Promedio de Asistentes por Reporte', $kpis['promedio_asistencias'] ?? 0],
            ['Cobertura Congregacional Ponderada (%)', ($kpis['cobertura_congregacion'] ?? 0).'%'],
            ['Población Elegible Acumulada', $kpis['poblacion_elegible_acumulada'] ?? 0],
            ['Aforo Total Acumulado', $kpis['aforo_total'] ?? 0],
            ['Ocupación Promedio de Aforo (%)', ($kpis['ocupacion_aforo'] ?? 0).'%'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}

class PorReunionSheet implements FromArray, ShouldAutoSize, WithHeadings, WithStyles, WithTitle
{
    public function __construct(protected array $reuniones) {}

    public function title(): string
    {
        return 'Por Reunión';
    }

    public function headings(): array
    {
        return [
            'Reunión',
            'Tipo de Servicio',
            'Sede Organizadora',
            'Reportes',
            'Asistencias Totales',
            'Asistencias Miembros',
            'Invitados',
            'Personas Únicas',
            'Promedio / Reporte',
            'Cobertura Congregacional (%)',
            'Población Elegible',
        ];
    }

    public function array(): array
    {
        $rows = [];
        foreach ($this->reuniones as $r) {
            $rows[] = [
                $r['reunion_nombre'] ?? '',
                $r['tipo_servicio'] ?? '',
                $r['sede_nombre'] ?? '',
                $r['reportes_count'] ?? 0,
                $r['asistencias_totales'] ?? 0,
                $r['asistencias_miembros'] ?? 0,
                $r['invitados'] ?? 0,
                $r['personas_unicas'] ?? 0,
                $r['promedio_reporte'] ?? 0,
                ($r['cobertura'] ?? 0).'%',
                $r['poblacion_elegible'] ?? 0,
            ];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}

class PorSedeSheet implements FromArray, ShouldAutoSize, WithHeadings, WithStyles, WithTitle
{
    public function __construct(protected array $bloquesSede) {}

    public function title(): string
    {
        return 'Por Sede';
    }

    public function headings(): array
    {
        return [
            'Sede Organizadora',
            'Reportes',
            'Asistencias Totales',
            'Personas Únicas',
            'Promedio / Reporte',
            'Cobertura (%)',
            'Invitados',
        ];
    }

    public function array(): array
    {
        $rows = [];
        foreach ($this->bloquesSede as $b) {
            $kpis = $b['kpis'] ?? [];
            $rows[] = [
                $b['sede_nombre'] ?? '',
                $kpis['total_reportes'] ?? 0,
                $kpis['asistencias_acumuladas'] ?? 0,
                $kpis['personas_unicas'] ?? 0,
                $kpis['promedio_asistencias'] ?? 0,
                ($kpis['cobertura_congregacion'] ?? 0).'%',
                $kpis['invitados_totales'] ?? 0,
            ];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}

class DemografiaSheet implements FromArray, ShouldAutoSize, WithHeadings, WithStyles, WithTitle
{
    public function __construct(protected array $demografia) {}

    public function title(): string
    {
        return 'Demografía';
    }

    public function headings(): array
    {
        return [
            'Categoría',
            'Grupo',
            'Total Asistencias',
            'Porcentaje (%)',
        ];
    }

    public function array(): array
    {
        $rows = [];

        foreach ($this->demografia['por_tipo_usuario'] ?? [] as $t) {
            $rows[] = [
                'Tipo de Usuario',
                $t['etiqueta'] ?? '',
                $t['total'] ?? 0,
                ($t['porcentaje'] ?? 0).'%',
            ];
        }

        foreach ($this->demografia['por_genero'] ?? [] as $g) {
            $rows[] = [
                'Género',
                $g['etiqueta'] ?? '',
                $g['total'] ?? 0,
                ($g['porcentaje'] ?? 0).'%',
            ];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
