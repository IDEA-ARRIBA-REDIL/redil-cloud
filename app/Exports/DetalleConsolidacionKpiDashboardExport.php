<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DetalleConsolidacionKpiDashboardExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
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
                case 'cosecha_total': $kpiName = 'Total cosecha'; break;
                case 'cosecha_efectiva': $kpiName = 'Cosecha efectiva'; break;
                case 'deserciones': $kpiName = 'Deserciones (Cosecha)'; break;
                case 'total_matriculas': $kpiName = 'Total matrículas'; break;
                case 'matriculas_sector': $kpiName = 'Matrículas de Sector'; break;
                case 'matriculas_templo': $kpiName = 'Matrículas de Templo'; break;
                case 'matriculas_aptos': $kpiName = 'Matrículas Aptos (Cosecha Vigentes)'; break;
                case 'matriculas_union_libre': $kpiName = 'Matrículas Unión Libre'; break;
                case 'matriculas_efectivos': $kpiName = 'Matrículas Efectivas'; break;
                case 'matriculas_deserciones': $kpiName = 'Deserciones (Matrículas)'; break;
                case 'total_miembros': $kpiName = 'Total miembros'; break;
                case 'miembros_ubicados': $kpiName = 'Miembros ubicados en grupos'; break;
                case 'union_libre_matriculados': $kpiName = 'Unión libre matriculados'; break;
                case 'miembros_formalizados': $kpiName = 'Miembros que estaban en unión libre (formalizados)'; break;
                case 'pendientes_membresia_union_libre': $kpiName = 'Pendientes por membresía (Unión libre)'; break;
                case 'bautismos': $kpiName = 'Bautismos'; break;
                case 'traslados': $kpiName = 'Traslados'; break;
                default: 
                    if (str_starts_with($this->datos['kpi'], 'cosecha_vinculacion_')) {
                        $kpiName = 'Cosecha por Vinculación';
                    } else if (str_starts_with($this->datos['kpi'], 'traslados_')) {
                        $kpiName = 'Traslados: ' . (str_contains($this->datos['kpi'], 'adultos') ? 'Adultos' : 'Warriors');
                    } else if (str_starts_with($this->datos['kpi'], 'bautismos_')) {
                        $kpiName = 'Bautismos: ' . (str_contains($this->datos['kpi'], 'adultos') ? 'Adultos' : 'Warriors');
                    }
                    break;
            }
        }

        $titulo = 'Detalle de Consolidación (Dashboard): ' . $kpiName;
        
        $filtrosInfo = '';
        if (isset($this->datos['bloqueDetalle'])) {
            $filtrosInfo .= 'Bloque: ' . $this->datos['bloqueDetalle']->nombre;
        } else {
            $filtrosInfo .= 'Bloques seleccionados: ' . count($this->datos['bloquesSeleccionados']);
        }

        if (isset($this->datos['sedeDetalle'])) {
            $filtrosInfo .= ' | Sede: ' . $this->datos['sedeDetalle']->nombre;
        } else {
            $filtrosInfo .= ' | Sedes seleccionadas: ' . count($this->datos['sedesSeleccionadas']);
        }
        
        $rangoInfo = isset($this->datos['rangoFechas']) ? ' | Rango: ' . $this->datos['rangoFechas'] : '';
        $subtitulo = trim($filtrosInfo . $rangoInfo, ' |');

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
                '¿Dado de baja?'
            ]
        ];
    }

    public function map($usuario): array
    {
        $telefonos = collect([$usuario->telefono_fijo, $usuario->telefono_movil, $usuario->telefono_otro])->filter();

        return [
            $usuario->nombre(3),
            $telefonos->isNotEmpty() ? $telefonos->implode(', ') : 'N/A',
            $usuario->email ?? 'N/A',
            $usuario->sede->nombre ?? 'N/A',
            $usuario->created_at->format('Y-m-d'),
            $usuario->trashed() ? 'Sí' : 'No'
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
