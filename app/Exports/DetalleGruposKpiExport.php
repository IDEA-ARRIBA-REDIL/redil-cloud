<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DetalleGruposKpiExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $grupos;
    protected $mapaSedes;
    protected $mapaTipos;
    protected $kpi;

    public function __construct($grupos, $mapaSedes, $mapaTipos, $kpi)
    {
        $this->grupos = $grupos;
        $this->mapaSedes = $mapaSedes;
        $this->mapaTipos = $mapaTipos;
        $this->kpi = $kpi;
    }

    public function collection()
    {
        return $this->grupos;
    }

    public function headings(): array
    {
        $headings = [
            'ID',
            'Nombre',
            'Encargado',
            'Sede',
            'Tipo',
            'Fecha Apertura'
        ];

        if ($this->kpi == 'bajas') {
            $headings[] = 'Fecha Baja';
        }

        return $headings;
    }

    public function map($grupo): array
    {
        // Resolver Sede Histórica
        $nombreSede = 'N/A';
        if ($grupo->sede_historica_id && isset($this->mapaSedes[$grupo->sede_historica_id])) {
            $nombreSede = $this->mapaSedes[$grupo->sede_historica_id]->nombre;
        } elseif ($grupo->sede) {
            $nombreSede = $grupo->sede->nombre;
        }

        // Resolver Tipo Histórico
        $nombreTipo = 'N/A';
        if ($grupo->tipo_historico_id && isset($this->mapaTipos[$grupo->tipo_historico_id])) {
            $nombreTipo = $this->mapaTipos[$grupo->tipo_historico_id]->nombre;
        } elseif ($grupo->tipoGrupo) {
            $nombreTipo = $grupo->tipoGrupo->nombre;
        }

        // Resolver Encargado
        $nombreEncargado = 'N/A';
        if ($grupo->encargados->count() > 0) {
            // Usando lógica simple, similar a la vista
            $nombreEncargado = $grupo->encargados->first()->nombre(2);
        }

        $row = [
            $grupo->id,
            $grupo->nombre,
            $nombreEncargado,
            $nombreSede,
            $nombreTipo,
            $grupo->fecha_apertura,
        ];

        if ($this->kpi == 'bajas') {
            // Lógica para obtener fecha de baja
             $reporteBaja = $grupo->reportesBajaAlta()
                                 ->where('dado_baja', true)
                                 ->orderBy('fecha', 'desc')
                                 ->first();
             $row[] = $reporteBaja ? $reporteBaja->fecha : 'N/A';
        }

        return $row;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
