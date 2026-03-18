<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DetalleConsolidacionKpiExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    protected $usuarios;

    public function __construct($usuarios)
    {
        $this->usuarios = $usuarios;
    }

    public function collection()
    {
        return $this->usuarios;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nombre',
            'Sede',
            'Fecha Llegada',
            'Estado',
        ];
    }

    public function map($usuario): array
    {
        return [
            $usuario->id,
            $usuario->nombre(3),
            $usuario->sede->nombre ?? 'N/A',
            $usuario->created_at->format('Y-m-d'),
            $usuario->trashed() ? 'Dado de baja' : 'Activo',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
