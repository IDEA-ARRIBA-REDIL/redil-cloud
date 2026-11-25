<?php

namespace App\Exports;

use App\Helpers\Helpers;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DetalleConsolidacionKpiExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    protected $usuarios;

    protected $datos;

    public function __construct($usuarios, $datos = [])
    {
        $this->usuarios = $usuarios;
        $this->datos = $datos;
    }

    public function collection()
    {
        return $this->usuarios;
    }

    public function headings(): array
    {
        $kpiName = 'KPI';
        if (isset($this->datos['kpi'])) {
            switch ($this->datos['kpi']) {
                case 'cosecha_total': $kpiName = 'Total cosecha';
                    break;
                case 'cosecha_efectiva': $kpiName = 'Cosecha efectiva';
                    break;
                case 'sin_gestion': $kpiName = 'Sin gestión de tareas';
                    break;
                case 'matriculas': $kpiName = 'Matrículas';
                    break;
                default: $kpiName = isset($this->datos['paso']) ? $this->datos['paso']->nombre : 'KPI';
                    break;
            }
        }

        $titulo = 'Detalle de '.Helpers::termino('consolidacion').': '.$kpiName;

        $zonaInfo = isset($this->datos['zona']) ? 'Zona: '.$this->datos['zona']->nombre : '';
        if (isset($this->datos['sede']) && $this->datos['sede']) {
            $zonaInfo .= ' | Sede: '.$this->datos['sede']->nombre;
        }
        $rangoInfo = isset($this->datos['rangoFechas']) ? ' | Rango: '.$this->datos['rangoFechas'] : '';
        $subtitulo = trim($zonaInfo.$rangoInfo, ' |');

        return [
            [$titulo],
            [$subtitulo],
            [''],
            [
                'Nombre',
                'Teléfono',
                'Email',
                'Sede',
                'Fecha Creación',
                '¿Dado de baja?',
                'Motivo de baja',
                'Dado de baja por',
            ],
        ];
    }

    public function map($usuario): array
    {
        $telefonos = collect([$usuario->telefono_fijo, $usuario->telefono_movil, $usuario->telefono_otro])->filter();

        $reporteBaja = $usuario->obtenerReporteBajaPorBitacora(
            $this->datos['inicio'] ?? null,
            $this->datos['fin'] ?? null
        );
        $estaDadoDeBaja = $reporteBaja !== null || $usuario->trashed();
        $motivoBaja = $estaDadoDeBaja
            ? ($reporteBaja?->tipo?->nombre ?? $reporteBaja?->motivo ?? 'No especificado')
            : 'N/A';
        $autorBaja = $estaDadoDeBaja
            ? ($reporteBaja?->autor ? $reporteBaja->autor->nombre(3) : 'No registrado')
            : 'N/A';

        return [
            $usuario->nombre(3),
            $telefonos->isNotEmpty() ? $telefonos->implode(', ') : 'N/A',
            $usuario->email ?? 'N/A',
            $usuario->sede->nombre ?? 'N/A',
            $usuario->created_at ? $usuario->created_at->format('Y-m-d') : 'N/A',
            $estaDadoDeBaja ? 'Sí' : 'No',
            $motivoBaja,
            $autorBaja,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 16, 'color' => ['argb' => 'FF2E7D32']]],
            2 => ['font' => ['bold' => true, 'color' => ['argb' => 'FF444444']]],
            4 => ['font' => ['bold' => true]],
        ];
    }
}
