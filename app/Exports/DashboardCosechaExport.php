<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DashboardCosechaExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize, WithEvents
{
    protected $datos;
    protected $tiposVinculaciones;
    protected $filtrosTexto;

    public function __construct(array $datos, array $tiposVinculaciones, string $filtrosTexto = '')
    {
        $this->datos = $datos;
        $this->tiposVinculaciones = $tiposVinculaciones;
        $this->filtrosTexto = $filtrosTexto;
    }

    public function array(): array
    {
        return $this->datos;
    }

    public function headings(): array
    {
        $numVinculaciones = count($this->tiposVinculaciones);
        $totalCols = 4 + $numVinculaciones + 11 + 14; // Cosecha + Escuelas(11) + Membresías(14)

        // Fila 1: Título principal
        $row1 = ['Detalle de consolidación'];
        for ($i = 1; $i < $totalCols; $i++) $row1[] = ''; 

        // Fila 2: Subtítulo con filtros
        $row2 = [$this->filtrosTexto];
        for ($i = 1; $i < $totalCols; $i++) $row2[] = '';

        // Fila 3: Súper-grupos (COSECHA, ESCUELAS, MEMBRESÍAS)
        $row3 = [
            'Sede / Bloque', // A3
            'COSECHA',       // B3
        ];
        // Relleno para COSECHA
        for ($i = 1; $i < (3 + $numVinculaciones); $i++) $row3[] = '';
        
        $row3[] = 'ESCUELAS'; 
        for ($i = 1; $i < 11; $i++) $row3[] = ''; // Relleno Escuelas
        
        $row3[] = 'MEMBRESÍAS';
        for ($i = 1; $i < 14; $i++) $row3[] = ''; // Relleno Membresías

        // Fila 4: Sub-grupos y nombres de columnas
        $row4 = [
            '', // A4 (fusionado con A3)
            'Total cosecha', // B4
            'Cosecha efectiva', // C4
            'Efectividad (%)', // D4
            'Cosecha por vinculación', // E4
        ];
        // Relleno Vinculación
        for ($i = 1; $i < $numVinculaciones; $i++) $row4[] = ''; 
        
        // Empieza sección Escuelas en Fila 4
        $row4[] = 'Total matrículas';
        $row4[] = 'Matrículas efectivas';
        $row4[] = 'Efectividad de matrículas (%)';
        $row4[] = 'Templo vs sector';
        $row4[] = ''; // Relleno Templo vs Sector
        $row4[] = 'Matrículas sector';
        $row4[] = ''; // Relleno Matriculas Sector
        $row4[] = 'Matrículas templo';
        $row4[] = ''; // Relleno Matriculas Templo
        $row4[] = 'Aptos vs Unión libre';
        $row4[] = ''; // Relleno Aptos vs Union Libre

        // Empieza sección Membresías en Fila 4
        $row4[] = 'Totales de membresía';
        $row4[] = ''; // Relleno
        $row4[] = ''; // Relleno
        $row4[] = ''; // Relleno Totales (son 4)
        $row4[] = 'Estado civil (Matriculados)';
        $row4[] = ''; 
        $row4[] = ''; 
        $row4[] = ''; // Relleno Estado Civil (son 4)
        $row4[] = 'Traslados';
        $row4[] = ''; 
        $row4[] = ''; // Relleno Traslados (son 3)
        $row4[] = 'Bautismos';
        $row4[] = ''; 
        $row4[] = ''; // Relleno Bautismos (son 3)

        // Fila 5: Nombres inferiores
        $row5 = [
            '', // A5 (fusionado con A3)
            '', // B5 (fusionado con B4)
            '', // C5 (fusionado con C4)
            '', // D5 (fusionado con D4)
        ];
        foreach ($this->tiposVinculaciones as $nombreVinculacion) {
            $row5[] = $nombreVinculacion; // E5, F5...
        }
        
        // Empieza sección Escuelas inferior
        $row5[] = ''; // Total matriculas (fusionado 4:5)
        $row5[] = ''; // Matriculas efectivas (fusionado 4:5)
        $row5[] = ''; // Efectividad (fusionado 4:5)
        $row5[] = 'Templo';
        $row5[] = 'Sector';
        $row5[] = 'Adultos';
        $row5[] = 'Warriors';
        $row5[] = 'Adultos';
        $row5[] = 'Warriors';
        $row5[] = 'Aptos';
        $row5[] = 'Unión libre';

        // Empieza sección Membresías inferior
        $row5[] = 'Total miembros'; 
        $row5[] = 'Ef. Matrículas a membresías (%)';
        $row5[] = 'Ubicados en grupos';
        $row5[] = 'Ef. Ubicación en grupos (%)';
        $row5[] = 'Total Unión libre';
        $row5[] = 'Pendientes';
        $row5[] = 'Formalizados';
        $row5[] = 'Ef. Formalización (%)';
        $row5[] = 'Total traslados';
        $row5[] = 'Adultos';
        $row5[] = 'Warriors';
        $row5[] = 'Total bautismos';
        $row5[] = 'Adultos';
        $row5[] = 'Warriors';

        return [
            $row1,
            $row2,
            $row3,
            $row4,
            $row5,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            'A' => ['font' => ['bold' => true]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestColumn = $sheet->getHighestColumn();
                $highestRow = $sheet->getHighestRow();
                $numVinc = count($this->tiposVinculaciones);

                // Funciones de Excel (Column Index 1-based)
                $colStr = function($idx) { return \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx); };

                // 1. Título y Filtros
                $sheet->mergeCells("A1:{$highestColumn}1");
                $sheet->mergeCells("A2:{$highestColumn}2");

                $sheet->getStyle("A1")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 16, 'color' => ['argb' => 'FF2E7D32']], 
                ]);
                $sheet->getStyle("A2")->applyFromArray([
                    'font' => ['bold' => false, 'size' => 11, 'color' => ['argb' => 'FF444444']], 
                ]);

                // 2. Súper-grupos (Fila 3)
                $colCosechaEndIdx = 4 + $numVinc;
                $colCosechaEnd = $colStr($colCosechaEndIdx);
                $sheet->mergeCells("B3:{$colCosechaEnd}3");

                $colEscuelasStartIdx = $colCosechaEndIdx + 1;
                $colEscuelasEndIdx = $colEscuelasStartIdx + 10;
                $colEscuelasStart = $colStr($colEscuelasStartIdx);
                $colEscuelasEnd = $colStr($colEscuelasEndIdx);
                $sheet->mergeCells("{$colEscuelasStart}3:{$colEscuelasEnd}3");
                
                $colMemStartIdx = $colEscuelasEndIdx + 1;
                $colMemEndIdx = $colMemStartIdx + 13;
                $colMemStart = $colStr($colMemStartIdx);
                $colMemEnd = $colStr($colMemEndIdx);
                $sheet->mergeCells("{$colMemStart}3:{$colMemEnd}3");

                // 3. Sub-grupos horizontales (Fila 4) - Cosecha y Escuelas
                if ($numVinc > 0) {
                    $sheet->mergeCells("E4:{$colCosechaEnd}4"); 
                }

                $idxTvS = $colEscuelasStartIdx + 3; 
                $idxMatS = $colEscuelasStartIdx + 5; 
                $idxMatT = $colEscuelasStartIdx + 7; 
                $idxAvU = $colEscuelasStartIdx + 9; 

                $sheet->mergeCells($colStr($idxTvS)."4:".$colStr($idxTvS+1)."4");
                $sheet->mergeCells($colStr($idxMatS)."4:".$colStr($idxMatS+1)."4");
                $sheet->mergeCells($colStr($idxMatT)."4:".$colStr($idxMatT+1)."4");
                $sheet->mergeCells($colStr($idxAvU)."4:".$colStr($idxAvU+1)."4");

                // Sub-grupos horizontales (Fila 4) - Membresías
                $idxTotMem = $colMemStartIdx; 
                $idxEstCiv = $colMemStartIdx + 4; 
                $idxNueTras = $colMemStartIdx + 8; 
                $idxNueBau = $colMemStartIdx + 11; 

                $sheet->mergeCells($colStr($idxTotMem)."4:".$colStr($idxTotMem+3)."4");
                $sheet->mergeCells($colStr($idxEstCiv)."4:".$colStr($idxEstCiv+3)."4");
                $sheet->mergeCells($colStr($idxNueTras)."4:".$colStr($idxNueTras+2)."4");
                $sheet->mergeCells($colStr($idxNueBau)."4:".$colStr($idxNueBau+2)."4");

                // 4. Fusiones verticales (A3:A5, B4:B5, C4:C5, etc)
                $sheet->mergeCells("A3:A5");
                $sheet->mergeCells("B4:B5");
                $sheet->mergeCells("C4:C5");
                $sheet->mergeCells("D4:D5");

                $idxTotMat = $colEscuelasStartIdx;
                $idxMatEf = $colEscuelasStartIdx + 1;
                $idxEfMat = $colEscuelasStartIdx + 2;

                $sheet->mergeCells($colStr($idxTotMat)."4:".$colStr($idxTotMat)."5");
                $sheet->mergeCells($colStr($idxMatEf)."4:".$colStr($idxMatEf)."5");
                $sheet->mergeCells($colStr($idxEfMat)."4:".$colStr($idxEfMat)."5");

                // 5. Estilos de los agrupadores
                $sheet->getStyle("A3:{$colMemEnd}5")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF7367F0']],
                    'alignment' => [
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 
                    ]
                ]);

                $sheet->getStyle("A3:A5")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

                // Diferenciar con bordes las secciones
                $sheet->getStyle("A3:{$colMemEnd}5")->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                $sheet->getStyle("A3:{$colMemEnd}5")->getBorders()->getAllBorders()->getColor()->setARGB('FFFFFFFF');

                // Diferentes colores de fondo para diferenciar grandemente los Mega-Grupos
                // COSECHA (Mantiene el FF7367F0 - Morado)
                // ESCUELAS (Le damos un tono ligeramente azulado/verdoso para distinguir)
                $sheet->getStyle("{$colEscuelasStart}3:{$colEscuelasEnd}5")->applyFromArray([
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF00B0FF']]
                ]);
                // MEMBRESÍAS (Le damos un tono coral/naranja para distinguir)
                $sheet->getStyle("{$colMemStart}3:{$colMemEnd}5")->applyFromArray([
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFF9F43']]
                ]);

                // 6. Inmovilizar desde la fila 6
                $sheet->freezePane('B6');
                
                // 7. Resaltos en filas con TOTAL y sangría de sedes
                for ($row = 6; $row <= $highestRow; $row++) {
                    $cellValue = (string) $sheet->getCell('A' . $row)->getValue();
                    if (str_starts_with($cellValue, 'TOTAL:')) {
                        $sheet->getStyle('A'.$row.':'.$highestColumn.$row)->applyFromArray([
                            'font' => ['bold' => true, 'color' => ['argb' => 'FF000000']],
                            'fill' => [
                                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'startColor' => ['argb' => 'FFECECEC']
                            ]
                        ]);
                    } else {
                        $sheet->getStyle('A'.$row)->getAlignment()->setIndent(1);
                        $sheet->getStyle('A'.$row)->applyFromArray([
                            'font' => ['bold' => false],
                        ]);
                    }
                }
            },
        ];
    }
}
