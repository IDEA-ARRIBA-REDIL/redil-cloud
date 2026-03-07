<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InformeAsistenciaSemanalGruposExport implements FromView, WithTitle, WithStyles, WithColumnWidths, WithEvents
{
    protected $dataPivoteada;
    protected $resumen;
    protected $encabezadosAgrupados;
    protected $encabezados;
    protected $clasificacionesSeleccionadas;
    protected $totalColumnas;

    public function __construct(
      ?array $dataPivoteada,
      ?array $resumen,
      ?array $encabezadosAgrupados,
      ?array $encabezados,
      $clasificacionesSeleccionadas
    ) {
        $this->dataPivoteada = $dataPivoteada;
        $this->resumen = $resumen;
        $this->encabezadosAgrupados = $encabezadosAgrupados;
        $this->encabezados = $encabezados;
        $this->clasificacionesSeleccionadas = $clasificacionesSeleccionadas;
        // Calculamos el número total de columnas: 1 (desc) + N semanas + 1 (prom)
        $this->totalColumnas = count($this->encabezados ?? []) + 2;
    }

    /**
     * @return View
     */
    public function view(): View
    {
        return view('contenido.paginas.grupos.exportar.exportarInformeAsistenciaSemanalGrupos', [
            'dataPivoteada' => $this->dataPivoteada,
            'resumen' => $this->resumen,
            'encabezadosAgrupados' => $this->encabezadosAgrupados,
            'encabezados' => $this->encabezados,
            'clasificacionesSeleccionadas' => $this->clasificacionesSeleccionadas,
            'totalColumnas' => $this->totalColumnas,
        ]);
    }

    public function title(): string
    {
        return 'Informe de Asistencia Semanal';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 45, // Ancho para la primera columna (descripciones)
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Estilo general para los encabezados de ambas tablas
        $sheet->getStyle('1:2')->getFont()->setBold(true);

        $filaInicioTablaGeneral = 10 + count($this->clasificacionesSeleccionadas);
        $sheet->getStyle($filaInicioTablaGeneral . ':' . ($filaInicioTablaGeneral + 1))->getFont()->setBold(true);

        return []; // Los estilos más complejos se manejan en registerEvents
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // --- FUSIONAR ENCABEZADOS DE MESES (TABLA RESUMEN) ---
                $columnaActual = 'B';
                foreach ($this->encabezadosAgrupados as $data) {
                    if ($data['colspan'] > 1) {
                        $columnaFin = chr(ord($columnaActual) + $data['colspan'] - 1);
                        $sheet->mergeCells("{$columnaActual}1:{$columnaFin}1");
                        $columnaActual = chr(ord($columnaFin) + 1);
                    } else {
                        $columnaActual++;
                    }
                }

                // --- FUSIONAR ENCABEZADOS DE MESES (TABLA GENERAL) ---
                $filaInicioTablaGeneral = 6 + count($this->clasificacionesSeleccionadas);
                $columnaActual = 'B';
                foreach ($this->encabezadosAgrupados as $data) {
                    if ($data['colspan'] > 1) {
                        $columnaFin = chr(ord($columnaActual) + $data['colspan'] - 1);
                        $sheet->mergeCells("{$columnaActual}{$filaInicioTablaGeneral}:{$columnaFin}{$filaInicioTablaGeneral}");
                        $columnaActual = chr(ord($columnaFin) + 1);
                    } else {
                        $columnaActual++;
                    }
                }

                // --- FUSIONAR FILAS DE TÍTULO DE GRUPO (TABLA GENERAL) ---
                $ultimaColumnaLetra = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($this->totalColumnas);
                $filaActual = $filaInicioTablaGeneral + 6; // Empezamos después de los encabezados de la tabla general


                foreach ($this->dataPivoteada as $datosGrupo) {
                    // Fusionar la fila del nombre del grupo
                    $sheet->mergeCells("A{$filaActual}:{$ultimaColumnaLetra}{$filaActual}");
                    // Aplicar estilo de fondo (simulando table-success)
                     $sheet->getStyle("A{$filaActual}")->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('D1E7DD');
                     $sheet->getStyle("A{$filaActual}")->getFont()->setBold(true);


                    $numFilasPorGrupo = count($this->clasificacionesSeleccionadas) + 3; // N clasificaciones + Asis + Inas + Reportes

                    // Aplicar estilo de fondo (simulando table-light)
                    $inicioLight = $filaActual + count($this->clasificacionesSeleccionadas) + 1;
                    $finLight = $filaActual + $numFilasPorGrupo;
                     $sheet->getStyle("A{$inicioLight}:{$ultimaColumnaLetra}{$finLight}")->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('F8F9FA');

                    $filaActual += $numFilasPorGrupo + 2; // +1 por la fila del título
                }
            },
        ];
    }
}
