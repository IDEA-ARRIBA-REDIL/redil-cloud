<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReporteErroresHomologacionExport implements FromCollection, ShouldAutoSize, WithHeadings, WithStyles
{
    protected array $errores;

    public function __construct(array $errores)
    {
        $this->errores = $errores;
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        $filas = [];
        foreach ($this->errores as $err) {
            $filas[] = [
                'fila' => $err['fila_numero'] ?? 'N/A',
                'identificacion' => $err['identificacion'] ?? '',
                'email' => $err['email'] ?? '',
                'nota_final' => $err['nota_final'] ?? '',
                'observacion' => $err['observacion'] ?? '',
                'motivo_error' => $err['mensaje_diagnostico'] ?? 'Error desconocido',
            ];
        }

        return new Collection($filas);
    }

    public function headings(): array
    {
        return [
            'Fila Original',
            'Identificación Alumno',
            'Email',
            'Nota Final',
            'Observación',
            'Motivo del Error',
        ];
    }

    /**
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['argb' => 'FFFFFFFF'],
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFDC3545'],
                ],
            ],
        ];
    }
}
