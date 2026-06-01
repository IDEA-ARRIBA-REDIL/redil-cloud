<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

class DashboardConsolidacionExport implements FromCollection, WithHeadings, WithStyles, WithTitle, ShouldAutoSize
{
    protected $datos;
    protected $indicesBloques;

    public function __construct($datos, $indicesBloques = [])
    {
        $this->datos = $datos;
        $this->indicesBloques = $indicesBloques;
    }

    public function collection()
    {
        return new Collection($this->datos);
    }

    public function headings(): array
    {
        return [
            'Sede / Bloque',
            'Total Cosecha',
            'Cosecha Efectiva',
            'Deserciones',
            '% de Efectividad',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Freeze Panes (inmovilizar primera fila y primera columna)
        $sheet->freezePane('B2'); 

        // Negrita para encabezados
        $sheet->getStyle('A1:E1')->getFont()->setBold(true);

        // Estilos para filas de Total (Bloques)
        foreach ($this->indicesBloques as $index) {
            // +2 porque $index es 0-based y la fila 1 son encabezados
            $row = $index + 2; 
            $sheet->getStyle("A{$row}:E{$row}")->getFont()->setBold(true);
            $sheet->getStyle("A{$row}:E{$row}")->getFill()
                  ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                  ->getStartColor()->setARGB('FFF0F0F0');
        }

        return [];
    }

    public function title(): string
    {
        return 'Cosecha';
    }
}
