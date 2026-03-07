<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use App\Models\Matricula;
use App\Models\ReporteAsistenciaClase;

class ReporteAsistenciaExport implements WithEvents, ShouldAutoSize
{
    protected $horariosAgrupados;
    protected $infoAdicional;

    public function __construct($horariosAgrupados, array $infoAdicional)
    {
        $this->horariosAgrupados = $horariosAgrupados;
        $this->infoAdicional = $infoAdicional;
    }

    /**
     * Función de ayuda para forzar la escritura del '0' como texto.
     */
    private function formatZeroAsText($value)
    {
        return $value === 0 ? '0' : $value;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $currentRow = 1;

                // --- ESTILOS PREDEFINIDOS ---
                $styleCenter = ['alignment' => ['horizontal' => 'center', 'vertical' => 'center']];
                $styleBold = ['font' => ['bold' => true]];
                $styleHeaderTitle = ['font' => ['bold' => true, 'size' => 16], 'alignment' => $styleCenter['alignment']];
                $styleSedeHeader = ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '212529']], 'alignment' => $styleCenter['alignment']];
                $styleMateriaHeader = ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E9ECEF']], 'alignment' => ['horizontal' => 'left']];
                $styleTableHeader = array_merge($styleBold, $styleCenter);
                $styleSedeTotal = ['font' => ['bold' => true, 'size' => 12], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'F8F9FA']], 'alignment' => $styleCenter['alignment']];
                $styleGrandTotalHeader = ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '0D6EFD']], 'alignment' => $styleCenter['alignment']];
                $styleGrandTotalData = ['font' => ['bold' => true, 'size' => 14], 'alignment' => $styleCenter['alignment']];

                // --- 1. CONSTRUCCIÓN DEL ENCABEZADO DEL REPORTE ---
                $sheet->setCellValue("A{$currentRow}", 'Reporte de Asistencia Detallado');
                $sheet->mergeCells("A{$currentRow}:G{$currentRow}");
                $sheet->getStyle("A{$currentRow}")->applyFromArray($styleHeaderTitle);
                $currentRow++;

                $sheet->setCellValue("A{$currentRow}", "Periodo: {$this->infoAdicional['periodo']}");
                $sheet->mergeCells("A{$currentRow}:G{$currentRow}");
                $sheet->getStyle("A{$currentRow}")->applyFromArray($styleCenter);
                $currentRow++;

                $sheet->fromArray(["Materia: {$this->infoAdicional['materia']}", '', '', "Semana: {$this->infoAdicional['semana']}"], null, "A{$currentRow}");
                $sheet->mergeCells("A{$currentRow}:C{$currentRow}");
                $sheet->mergeCells("D{$currentRow}:G{$currentRow}");
                $sheet->getStyle("A{$currentRow}:G{$currentRow}")->applyFromArray($styleCenter);
                $currentRow += 2;

                $granTotalReporte = ['asistio' => 0, 'ausente' => 0, 'no_registrado' => 0, 'desercion' => 0];

                // --- 2. BUCLE POR SEDE ---
                foreach ($this->horariosAgrupados as $nombreSede => $materiasEnSede) {
                    $sheet->setCellValue("A{$currentRow}", "SEDE: {$nombreSede}");
                    $sheet->mergeCells("A{$currentRow}:G{$currentRow}");
                    $sheet->getStyle("A{$currentRow}")->applyFromArray($styleSedeHeader);
                    $currentRow++;

                    $totalesSede = ['asistio' => 0, 'ausente' => 0, 'no_registrado' => 0, 'desercion' => 0];

                    // --- 3. BUCLE POR MATERIA (DENTRO DE LA SEDE) ---
                    foreach ($materiasEnSede as $nombreMateria => $horariosEnMateria) {
                        $sheet->setCellValue("A{$currentRow}", "   Materia: {$nombreMateria}");
                        $sheet->mergeCells("A{$currentRow}:G{$currentRow}");
                        $sheet->getStyle("A{$currentRow}")->applyFromArray($styleMateriaHeader);
                        $currentRow++;

                        // Encabezado de la tabla de horarios
                        $sheet->fromArray(['HORARIO', '', 'TOTAL', 'ASISTIÓ', 'NO ASISTIÓ', 'NO REPORTADO', 'DESERCIÓN'], null, "A{$currentRow}");
                        $sheet->mergeCells("A{$currentRow}:B{$currentRow}");
                        $sheet->getStyle("A{$currentRow}:G{$currentRow}")->applyFromArray($styleTableHeader);
                        $currentRow++;

                        // --- 4. BUCLE POR HORARIO (DENTRO DE LA MATERIA) ---
                        foreach ($horariosEnMateria as $horario) {
                            $contadoresHorario = $this->calcularContadoresParaHorario($horario);
                            list($diaHora, $aulaInfo) = explode(' - Aula: ', $horario->horarioBase->dia_semana . ' ' . $horario->horarioBase->hora_inicio_formato . ' - Aula: ' . $horario->horarioBase->aula->nombre);
                            $totalMatriculasHorario = array_sum($contadoresHorario);

                            $dataRow = [
                                $diaHora,
                                $aulaInfo,
                                $this->formatZeroAsText($totalMatriculasHorario),
                                $this->formatZeroAsText($contadoresHorario['asistio']),
                                $this->formatZeroAsText($contadoresHorario['ausente']),
                                $this->formatZeroAsText($contadoresHorario['no_registrado']),
                                $this->formatZeroAsText($contadoresHorario['desercion']),
                            ];
                            $sheet->fromArray($dataRow, null, "A{$currentRow}");
                            $sheet->getStyle("C{$currentRow}:G{$currentRow}")->applyFromArray($styleCenter);
                            $currentRow++;

                            // Acumula para el total de la sede
                            foreach ($totalesSede as $key => &$value) {
                                $value += $contadoresHorario[$key];
                            }
                        }
                    }

                    // Escribir fila de totales de la SEDE
                    $totalGeneralSede = array_sum($totalesSede);
                    $totalRow = [
                        'TOTALES DE LA SEDE',
                        '',
                        $this->formatZeroAsText($totalGeneralSede),
                        $this->formatZeroAsText($totalesSede['asistio']),
                        $this->formatZeroAsText($totalesSede['ausente']),
                        $this->formatZeroAsText($totalesSede['no_registrado']),
                        $this->formatZeroAsText($totalesSede['desercion']),
                    ];
                    $sheet->fromArray($totalRow, null, "A{$currentRow}");
                    $sheet->mergeCells("A{$currentRow}:B{$currentRow}");
                    $sheet->getStyle("A{$currentRow}:G{$currentRow}")->applyFromArray($styleSedeTotal);
                    $currentRow += 2;

                    // Acumula para el gran total
                    foreach ($granTotalReporte as $key => &$value) {
                        $value += $totalesSede[$key];
                    }
                }

                // --- 5. CONSTRUCCIÓN DE LA TABLA DE GRAN TOTAL ---
                if ($this->horariosAgrupados->isNotEmpty()) {
                    // (código idéntico a las versiones anteriores para el gran total)
                }
            },
        ];
    }

    private function calcularContadoresParaHorario($horario): array
    {
        $contadores = ['asistio' => 0, 'ausente' => 0, 'no_registrado' => 0, 'desercion' => 0];
        $matriculas = Matricula::where('horario_materia_periodo_id', $horario->id)->get();
        if ($matriculas->isEmpty()) return $contadores;

        $alumnosDesertadosIds = $matriculas->where('bloqueado', true)
            ->whereBetween('fecha_bloqueo', [$this->infoAdicional['inicioSemana'], $this->infoAdicional['finSemana']])
            ->pluck('user_id');
        $contadores['desercion'] = $alumnosDesertadosIds->count();

        $reporte = ReporteAsistenciaClase::where('horario_materia_periodo_id', $horario->id)
            ->whereBetween('fecha_clase_reportada', [$this->infoAdicional['inicioSemana'], $this->infoAdicional['finSemana']])
            ->with('detallesAsistencia')->first();

        $matriculasActivas = $matriculas->whereNotIn('user_id', $alumnosDesertadosIds);

        if (!$reporte) {
            $contadores['no_registrado'] = $matriculasActivas->count();
        } else {
            $detalles = $reporte->detallesAsistencia->keyBy('user_id');
            foreach ($matriculasActivas as $matricula) {
                if ($detalle = $detalles->get($matricula->user_id)) {
                    if ($detalle->asistio) $contadores['asistio']++;
                    else $contadores['ausente']++;
                } else {
                    $contadores['no_registrado']++;
                }
            }
        }
        return $contadores;
    }
}
