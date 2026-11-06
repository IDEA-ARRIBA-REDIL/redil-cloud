<?php

namespace App\Exports;

use App\Models\AlumnoRespuestaItem;
use App\Models\Calificaciones;
use App\Models\CortePeriodo;
use App\Models\ItemCorteMateriaPeriodo;
use App\Models\MateriaAprobadaUsuario;
use App\Models\Matricula;
use App\Models\ReporteAsistenciaAlumnos;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MatriculasActivasEscuelaExport extends DefaultValueBinder implements FromCollection, ShouldAutoSize, WithColumnFormatting, WithCustomValueBinder, WithHeadings, WithMapping, WithStyles
{
    protected $matriculas;

    protected $resultadosDefinitivos;

    protected $asistencias;

    protected $items;

    protected $cortes;

    protected $calificaciones;

    protected $notasMinimas;

    public function __construct($matriculas)
    {
        $this->matriculas = $matriculas;

        $periodosIds = $matriculas->pluck('periodo_id')->unique()->filter();
        $userIds = $matriculas->pluck('user_id')->unique()->filter();
        $horarioIds = $matriculas->pluck('horario_materia_periodo_id')->unique()->filter();

        // 1. Resultados definitivos registrados en materias_aprobada_usuario (para materias cerradas/finalizadas)
        $this->resultadosDefinitivos = MateriaAprobadaUsuario::whereIn('periodo_id', $periodosIds)
            ->whereIn('user_id', $userIds)
            ->get()
            ->keyBy(fn ($item) => $item->user_id.'-'.$item->materia_periodo_id);

        // 2. Conteo masivo de asistencias por alumno y clase
        $this->asistencias = ReporteAsistenciaAlumnos::query()
            ->join('reportes_asistencia_clase as clase', 'clase.id', '=', 'reportes_asistencia_alumnos.reporte_asistencia_clase_id')
            ->whereIn('clase.horario_materia_periodo_id', $horarioIds)
            ->whereIn('reportes_asistencia_alumnos.user_id', $userIds)
            ->select('reportes_asistencia_alumnos.user_id', 'clase.horario_materia_periodo_id')
            ->selectRaw('SUM(CASE WHEN asistio THEN 1 ELSE 0 END) as presentes')
            ->groupBy('reportes_asistencia_alumnos.user_id', 'clase.horario_materia_periodo_id')
            ->get()
            ->keyBy(fn ($a) => $a->user_id.'-'.$a->horario_materia_periodo_id);

        // 3. Estructura de cortes e ítems para el cálculo de notas actuales
        $this->items = ItemCorteMateriaPeriodo::whereIn('horario_materia_periodo_id', $horarioIds)->get();
        $this->cortes = CortePeriodo::whereIn('periodo_id', $periodosIds)->get()->keyBy('id');
        $this->calificaciones = AlumnoRespuestaItem::whereIn('item_corte_materia_periodo_id', $this->items->pluck('id'))
            ->whereIn('user_id', $userIds)
            ->get()
            ->groupBy('user_id');

        // 4. Notas mínimas aprobatorias según el sistema de calificaciones del periodo
        $sistemasIds = $matriculas->map(fn ($m) => $m->periodo?->sistema_calificaciones_id)->filter()->unique();
        $this->notasMinimas = Calificaciones::whereIn('sistema_calificacion_id', $sistemasIds)
            ->where('aprobado', true)
            ->pluck('nota_minima', 'sistema_calificacion_id');
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->matriculas;
    }

    /**
     * Define los encabezados de las columnas.
     */
    public function headings(): array
    {
        return [
            'Alumno',
            'Identificación',
            'Sede del Alumno',
            'Periodo',
            'Materia',
            'Horario',
            'Sede Matrícula',
            'Estado / Aprobación',
            'Nota Final / Actual',
            'Asistencias Reportadas',
            'Bloqueado',
            'Fecha Bloqueo',
        ];
    }

    /**
     * Mapea cada objeto Matricula a un array que representa una fila en el Excel.
     *
     * @param  Matricula  $matricula
     */
    public function map($matricula): array
    {
        $usuario = $matricula->user;
        $alumnoNombre = $usuario ? $usuario->nombre(4) : 'Alumno no disponible';
        $alumnoId = (string) ($usuario?->identificacion ?? 'N/A');
        $sedeAlumno = $usuario?->sede?->nombre ?? 'Sin sede asignada';
        $periodoNombre = optional($matricula->periodo)->nombre ?? 'N/A';
        $materiaPeriodo = $matricula->horarioMateriaPeriodo?->materiaPeriodo;
        $materiaOriginal = $materiaPeriodo?->materia;
        $materiaNombre = $materiaOriginal?->nombre ?? 'N/A';

        // Construcción de la descripción del horario
        $horarioBase = optional($matricula->horarioMateriaPeriodo)->horarioBase;
        $horarioDesc = 'N/A';
        if ($horarioBase) {
            $horarioDesc = $horarioBase->dia_semana.' '.$horarioBase->hora_inicio_formato.
                ' ('.(optional($horarioBase->aula)->nombre ?? 'Aula N/A').')';
        }

        $sedeMatricula = optional($matricula->sede)->nombre ?? 'N/A';

        // Cálculo de Notas, Asistencias y Estado de Aprobación
        $hId = $matricula->horario_materia_periodo_id;
        $mId = $materiaPeriodo?->id;
        $def = $mId ? $this->resultadosDefinitivos->get($matricula->user_id.'-'.$mId) : null;

        $totalAsistencias = (int) ($def?->total_asistencias ?? ($this->asistencias->get($matricula->user_id.'-'.$hId)?->presentes ?? 0));

        $evaluaNotas = $materiaOriginal ? (bool) $materiaOriginal->habilitar_calificaciones : true;
        $evaluaAsistencias = $materiaOriginal ? (bool) $materiaOriginal->habilitar_asistencias : false;
        $asistenciasMinimas = $materiaOriginal?->asistencias_minimas;

        if ($def && in_array($def->aprobado, [0, 1], true)) {
            $nota = $def->nota_final !== null ? (float) $def->nota_final : null;
            $estadoAprobacion = $def->aprobado === 1 ? 'Aprobado' : 'Reprobado';
        } else {
            $misItems = $this->items->where('horario_materia_periodo_id', $hId);
            $misNotas = $this->calificaciones->get($matricula->user_id, collect())->pluck('nota_obtenida', 'item_corte_materia_periodo_id');
            $notaCalculada = 0.0;

            foreach ($this->cortes->where('periodo_id', $matricula->periodo_id) as $corte) {
                $notaCorte = 0.0;
                foreach ($misItems->where('corte_periodo_id', $corte->id) as $item) {
                    $val = $misNotas->get($item->id);
                    if ($val !== null && is_numeric($item->porcentaje)) {
                        $notaCorte += (float) $val * ((float) $item->porcentaje / 100);
                    }
                }
                if ($corte->porcentaje > 0) {
                    $notaCalculada += round($notaCorte, 2) * ($corte->porcentaje / 100);
                }
            }
            $nota = $evaluaNotas ? round($notaCalculada, 2) : null;

            $min = (float) ($this->notasMinimas->get($matricula->periodo?->sistema_calificaciones_id) ?? 3.0);
            $periodoFinalizado = $matricula->periodo?->fecha_fin ? Carbon::parse($matricula->periodo->fecha_fin)->isPast() : false;

            if ($matricula->bloqueado) {
                $estadoAprobacion = 'Bloqueado';
            } elseif ($periodoFinalizado) {
                $cumpleNota = ! $evaluaNotas || ($nota !== null && $nota >= $min);
                $cumpleAsistencias = ! $evaluaAsistencias || ($asistenciasMinimas === null || $totalAsistencias >= $asistenciasMinimas);
                $estadoAprobacion = ($cumpleNota && $cumpleAsistencias) ? 'Aprobado' : 'Reprobado';
            } else {
                $vaAprobandoNota = ! $evaluaNotas || ($nota !== null && $nota >= $min);
                $estadoAprobacion = $vaAprobandoNota ? 'Aprobando' : 'Cursando';
            }
        }

        $bloqueado = $matricula->bloqueado ? 'Sí' : 'No';
        $fechaBloqueo = $matricula->fecha_bloqueo ? Carbon::parse($matricula->fecha_bloqueo)->format('Y-m-d H:i') : '';

        return [
            $alumnoNombre,
            $alumnoId,
            $sedeAlumno,
            $periodoNombre,
            $materiaNombre,
            $horarioDesc,
            $sedeMatricula,
            $estadoAprobacion,
            $nota !== null ? $nota : 'N/A',
            $totalAsistencias,
            $bloqueado,
            $fechaBloqueo,
        ];
    }

    public function bindValue(Cell $cell, mixed $value): bool
    {
        if (is_string($value)) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }

    public function columnFormats(): array
    {
        return [
            'B' => '@',
            'I' => '0.00',
            'J' => '0',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->freezePane('A2');
        $sheet->setAutoFilter($sheet->calculateWorksheetDimension());

        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
