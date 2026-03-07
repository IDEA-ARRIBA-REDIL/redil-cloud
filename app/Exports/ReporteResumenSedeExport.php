<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ReporteResumenSedeExport implements WithEvents, ShouldAutoSize
{
    protected $datosParaExportar;
    protected $materias;
    protected $granTotal;
    protected $infoAdicional;

    public function __construct(array $datosParaExportar, $materias, array $granTotal, array $infoAdicional)
    {
        $this->datosParaExportar = $datosParaExportar;
        $this->materias = $materias;
        $this->granTotal = $granTotal;
        $this->infoAdicional = $infoAdicional;
    }

    private function formatZeroAsText($value)
    {
        return ($value === 0 || $value === 0.0) ? '0' : $value;
    }
    private function formatPercent($value)
    {
        return ($value === 0 || $value === 0.0) ? '0.00%' : $value;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $colIndex = 1;

                // --- ESTILOS PREDEFINIDOS ---
                $styleCenter = ['alignment' => ['horizontal' => 'center', 'vertical' => 'center']];
                $styleBold = ['font' => ['bold' => true]];
                $styleWhiteText = ['font' => ['color' => ['rgb' => 'FFFFFF']]];
                $styleBlueFill = ['fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '00529B']]];
                $styleDarkBlueFill = ['fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '003366']]];
                $styleLightGreenFill = ['fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '92D050']]];
                $styleDarkGreenFill = ['fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '548235']]];
                $stylePinkFill = ['fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'FFC7CE']]];
                $styleTotalFill = ['fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'D9E1F2']]];

                // --- 1. CONSTRUCCIÓN DINÁMICA DE ENCABEZADOS ---
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex++) . '1', 'PASTOR / COORDINADOR');
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex++) . '1', 'TOTAL LEGALIZADOS');
                $sheet->mergeCells("A1:A2");
                $sheet->mergeCells("B1:B2");

                foreach ($this->materias as $materia) {
                    $startIntCol = $colIndex;
                    $sheet->setCellValue(Coordinate::stringFromColumnIndex($startIntCol) . '1', $materia->nombre);
                    $sheet->fromArray(['LEGALIZADOS', 'PRESENTES', 'AUSENTES', 'NO REPORTADOS', 'DESERCIONES'], null, Coordinate::stringFromColumnIndex($startIntCol) . '2');
                    $colIndex += 5;
                    $sheet->mergeCells(Coordinate::stringFromColumnIndex($startIntCol) . '1:' . Coordinate::stringFromColumnIndex($colIndex - 1) . '1');

                    $startPctCol = $colIndex;
                    $sheet->setCellValue(Coordinate::stringFromColumnIndex($startPctCol) . '1', $materia->nombre . ' %');
                    $sheet->fromArray(['PRESENTES', 'AUSENTES', 'NO REPORTADOS', 'DESERCIONES'], null, Coordinate::stringFromColumnIndex($startPctCol) . '2');
                    $colIndex += 4;
                    $sheet->mergeCells(Coordinate::stringFromColumnIndex($startPctCol) . '1:' . Coordinate::stringFromColumnIndex($colIndex - 1) . '1');
                }

                $lastCol = Coordinate::stringFromColumnIndex($colIndex - 1);
                $sheet->getStyle("A1:{$lastCol}2")->applyFromArray(array_merge($styleBold, $styleCenter));
                $sheet->getStyle('A1:A2')->applyFromArray(array_merge($styleWhiteText, $styleBlueFill));
                $sheet->getStyle('B1:B2')->applyFromArray(array_merge($styleWhiteText, $styleDarkBlueFill));

                $colIndexForStyle = 3;
                foreach ($this->materias as $materia) {
                    $sheet->getStyle(Coordinate::stringFromColumnIndex($colIndexForStyle) . '2')->applyFromArray($styleLightGreenFill);
                    $sheet->getStyle(Coordinate::stringFromColumnIndex($colIndexForStyle) . '1:' . Coordinate::stringFromColumnIndex($colIndexForStyle + 8) . '1')->applyFromArray(array_merge($styleWhiteText, $styleDarkGreenFill));
                    $sheet->getStyle(Coordinate::stringFromColumnIndex($colIndexForStyle + 7) . '2')->applyFromArray($stylePinkFill);
                    $colIndexForStyle += 9;
                }

                // --- 2. CONSTRUCCIÓN DINÁMICA DE FILAS DE DATOS ---
                $currentRow = 3;
                foreach ($this->datosParaExportar as $filaSede) {
                    $colIndex = 1;
                    $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex++) . $currentRow, $filaSede['nombre']);
                    $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex++) . $currentRow, $this->formatZeroAsText($filaSede['totalLegalizadosPeriodo']));

                    foreach ($this->materias as $materia) {
                        $stats = $filaSede['stats'][$materia->id] ?? null;

                        // Bloque de Cantidades
                        if ($stats) {
                            $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex++) . $currentRow, $this->formatZeroAsText($stats['legalizados']));
                            $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex++) . $currentRow, $this->formatZeroAsText($stats['contadores']['asistio']));
                            $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex++) . $currentRow, $this->formatZeroAsText($stats['contadores']['ausente']));
                            $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex++) . $currentRow, $this->formatZeroAsText($stats['contadores']['no_registrado']));
                            $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex++) . $currentRow, $this->formatZeroAsText($stats['contadores']['desercion']));
                        } else {
                            for ($i = 0; $i < 5; $i++) $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex++) . $currentRow, '0');
                        }

                        // ----> INICIO DE LA CORRECCIÓN <----
                        // Guardamos la columna donde empiezan los porcentajes
                        $startPctColNum = $colIndex;
                        // Bloque de Porcentajes
                        if ($stats && $stats['legalizados'] > 0) {
                            $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex++) . $currentRow, $stats['contadores']['asistio'] / $stats['legalizados']);
                            $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex++) . $currentRow, $stats['contadores']['ausente'] / $stats['legalizados']);
                            $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex++) . $currentRow, $stats['contadores']['no_registrado'] / $stats['legalizados']);
                            $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex++) . $currentRow, $stats['contadores']['desercion'] / $stats['legalizados']);
                        } else {
                            for ($i = 0; $i < 4; $i++) $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex++) . $currentRow, 0.0);
                        }
                        // Aplicamos el formato de porcentaje SOLO a las 4 celdas que acabamos de escribir
                        $sheet->getStyle(Coordinate::stringFromColumnIndex($startPctColNum) . $currentRow . ':' . Coordinate::stringFromColumnIndex($colIndex - 1) . $currentRow)->getNumberFormat()->setFormatCode('0.00%');
                        // ----> FIN DE LA CORRECCIÓN <----
                    }
                    $sheet->getStyle("B{$currentRow}:{$lastCol}{$currentRow}")->applyFromArray($styleCenter);
                    $currentRow++;
                }

                // --- 3. CONSTRUCCIÓN DINÁMICA DE FILA DE GRAN TOTAL ---
                $currentRow++;
                $colIndex = 1;
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex++) . $currentRow, 'GRAN TOTAL');
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex++) . $currentRow, $this->formatZeroAsText(array_sum(array_column($this->datosParaExportar, 'totalLegalizadosPeriodo'))));

                foreach ($this->materias as $materia) {
                    $gt = $this->granTotal[$materia->id] ?? null;

                    if ($gt) {
                        $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex++) . $currentRow, $this->formatZeroAsText($gt['legalizadosMateria']));
                        $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex++) . $currentRow, $this->formatZeroAsText($gt['asistio']));
                        $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex++) . $currentRow, $this->formatZeroAsText($gt['ausente']));
                        $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex++) . $currentRow, $this->formatZeroAsText($gt['no_registrado']));
                        $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex++) . $currentRow, $this->formatZeroAsText($gt['desercion']));
                    } else {
                        for ($i = 0; $i < 5; $i++) $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex++) . $currentRow, '0');
                    }

                    // ----> INICIO DE LA CORRECCIÓN <----
                    $startPctColNum = $colIndex;
                    if ($gt && $gt['legalizadosMateria'] > 0) {
                        $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex++) . $currentRow, $gt['asistio'] / $gt['legalizadosMateria']);
                        $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex++) . $currentRow, $gt['ausente'] / $gt['legalizadosMateria']);
                        $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex++) . $currentRow, $gt['no_registrado'] / $gt['legalizadosMateria']);
                        $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex++) . $currentRow, $gt['desercion'] / $gt['legalizadosMateria']);
                    } else {
                        for ($i = 0; $i < 4; $i++) $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex++) . $currentRow, 0.0);
                    }
                    $sheet->getStyle(Coordinate::stringFromColumnIndex($startPctColNum) . $currentRow . ':' . Coordinate::stringFromColumnIndex($colIndex - 1) . $currentRow)->getNumberFormat()->setFormatCode('0.00%');
                    // ----> FIN DE LA CORRECCIÓN <----
                }
                $sheet->getStyle("A{$currentRow}:{$lastCol}{$currentRow}")->applyFromArray(array_merge($styleBold, $styleCenter, $styleTotalFill));

                // Aplicar bordes a toda la tabla
                $sheet->getStyle("A1:{$lastCol}{$currentRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            },
        ];
    }
}
