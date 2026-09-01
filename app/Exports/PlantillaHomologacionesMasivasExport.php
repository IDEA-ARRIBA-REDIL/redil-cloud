<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PlantillaHomologacionesMasivasExport implements FromCollection, ShouldAutoSize, WithHeadings, WithStyles
{
    protected ?int $estado;

    public function __construct(?int $estado = 1)
    {
        $this->estado = $estado;
    }

    /**
     * Devuelve filas de ejemplo para orientar al usuario.
     *
     * @return Collection
     */
    public function collection()
    {
        return new Collection([
            [
                'identificacion_alumno' => '1020304050',
                'email' => 'estudiante1@ejemplo.com',
                'nota_final' => $this->estado === 1 ? '4.50' : '',
                'observacion' => 'Homologación de contenido cursado en institución externa.',
            ],
            [
                'identificacion_alumno' => '9876543210',
                'email' => 'estudiante2@ejemplo.com',
                'nota_final' => $this->estado === 1 ? '3.80' : '',
                'observacion' => 'Validación de certificado y plan de estudios presentado.',
            ],
            [
                'identificacion_alumno' => '',
                'email' => 'estudiante3@ejemplo.com',
                'nota_final' => $this->estado === 1 ? '5.00' : '',
                'observacion' => 'Homologación aprobada por el comité académico.',
            ],
        ]);
    }

    /**
     * Encabezados oficiales requeridos para el cargue masivo.
     */
    public function headings(): array
    {
        return [
            'identificacion_alumno',
            'email',
            'nota_final',
            'observacion',
        ];
    }

    /**
     * Aplica estilos a la cabecera del archivo Excel.
     *
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
                    'startColor' => ['argb' => 'FF007788'],
                ],
            ],
        ];
    }
}
