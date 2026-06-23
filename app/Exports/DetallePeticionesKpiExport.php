<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Helpers\Helpers;

class DetallePeticionesKpiExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $peticiones;
    protected $datos;

    public function __construct($peticiones, $datos = [])
    {
        $this->peticiones = $peticiones;
        $this->datos = $datos;
    }

    public function collection()
    {
        return $this->peticiones;
    }

    public function headings(): array
    {
        $kpiName = 'Total Peticiones';
        if (isset($this->datos['kpi'])) {
            switch ($this->datos['kpi']) {
                case 'total': $kpiName = 'Total Peticiones'; break;
                case 'pendientes': $kpiName = 'Peticiones Pendientes'; break;
                case 'en_proceso': $kpiName = 'Peticiones en Proceso'; break;
                case 'cerradas': $kpiName = 'Peticiones Cerradas'; break;
                case 'sin_asignar': $kpiName = 'Peticiones sin Asignar'; break;
            }
        }

        if (isset($this->datos['paisDetalle'])) {
            $kpiName .= ' - País: ' . $this->datos['paisDetalle']->nombre;
        }

        if (isset($this->datos['tipoDetalle'])) {
            $kpiName .= ' - Tipo: ' . $this->datos['tipoDetalle']->nombre;
        }

        $titulo = 'Detalle de Peticiones (Dashboard): ' . $kpiName;
        $subtitulo = 'Rango de fechas: ' . ($this->datos['rangoFechas'] ?? 'No especificado');

        return [
            [$titulo],
            [$subtitulo],
            [''],
            [
                'Nombre',
                'Teléfono',
                'Email',
                'Petición',
                'Tipo de Petición',
                'Asignada a',
                'Estado'
            ]
        ];
    }

    public function map($peticion): array
    {
        // Extraer Nombre
        if ($peticion->user_id) {
            $nombre = trim($peticion->primer_nombre . ' ' . $peticion->segundo_nombre . ' ' . $peticion->primer_apellido);
        } else {
            $nombre = $peticion->nombre_externo ?? 'N/A';
        }

        // Extraer Teléfono
        if ($peticion->user_id) {
            $telefonos = collect([$peticion->telefono_fijo, $peticion->telefono_movil, $peticion->telefono_otro])->filter();
            $telefono = $telefonos->isNotEmpty() ? $telefonos->implode(', ') : 'N/A';
        } else {
            $telefono = $peticion->telefono_externo ?? 'N/A';
        }

        // Extraer Email
        if ($peticion->user_id) {
            $email = $peticion->email ?? 'N/A';
        } else {
            $email = $peticion->email_externo ?? 'N/A';
        }

        return [
            $nombre,
            $telefono,
            $email,
            $peticion->descripcion ?? '',
            $peticion->tipoPeticion->nombre ?? 'Sin especificar',
            $peticion->asignado ? $peticion->asignado->nombre(3) : 'Sin asignar',
            $peticion->estado ? Helpers::estadoPeticion($peticion->estado) : 'Sin responder'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 16, 'color' => ['argb' => 'FF7367F0']]], // Purple color matching brand
            2 => ['font' => ['bold' => true, 'color' => ['argb' => 'FF444444']]],
            4 => ['font' => ['bold' => true]],
        ];
    }
}
