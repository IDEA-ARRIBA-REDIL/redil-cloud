<?php

namespace App\Livewire\Reportes;

use App\Models\Materia;
use App\Models\Matricula;
use App\Models\Periodo;
use App\Models\ReporteAsistenciaClase;
use App\Models\Sede;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Livewire\Component;

class FiltrosAsistenciaPeriodo extends Component
{
    public $periodoSeleccionado = null;

    public $sedesSeleccionadas = [];

    public $materiasSeleccionadas = [];

    public $semanaSeleccionada = null;

    public $periodos = [];

    public $sedes = [];

    public $materias = [];

    public $semanas = [];

    public bool $reporteConsultado = false;

    public array $datosReporte = [];

    public array $granTotalReporte = [
        'totalMatriculas' => 0,
        'asistio' => 0,
        'ausente' => 0,
        'no_registrado' => 0,
        'desercion' => 0,
        'porcentajeAsistencia' => 0,
    ];

    public array $graficoGeneral = [];

    public array $graficoSedes = [];

    public array $graficoMaterias = [];

    public string $infoSemanaTexto = '';

    public function mount()
    {
        $this->periodos = Periodo::orderBy('fecha_inicio', 'desc')->get();
    }

    public function updatedPeriodoSeleccionado($periodoId)
    {
        $this->reset([
            'sedesSeleccionadas',
            'materiasSeleccionadas',
            'semanaSeleccionada',
            'sedes',
            'materias',
            'semanas',
            'reporteConsultado',
            'datosReporte',
            'graficoGeneral',
            'graficoSedes',
            'graficoMaterias',
            'infoSemanaTexto',
        ]);
        $this->dispatch('actualizar-sedes-select2', sedes: []);
        $this->dispatch('actualizar-materias-select2', materias: []);

        if ($periodoId) {
            $periodo = Periodo::find($periodoId);
            if ($periodo) {
                $this->sedes = $periodo->sedes()->orderBy('nombre')->get();
            }
            $this->calcularSemanasDelPeriodo($periodoId);
            $this->dispatch('actualizar-sedes-select2', sedes: $this->sedes->toArray());
        }
    }

    public function updatedSedesSeleccionadas()
    {
        $this->reset([
            'materiasSeleccionadas',
            'materias',
            'reporteConsultado',
            'datosReporte',
            'graficoGeneral',
            'graficoSedes',
            'graficoMaterias',
        ]);
        $this->dispatch('actualizar-materias-select2', materias: []);
        if (! empty($this->sedesSeleccionadas)) {
            $this->materias = Materia::whereHas('materiasPeriodo.horariosMateriaPeriodo.horarioBase.aula', function ($query) {
                $query->whereIn('sede_id', $this->sedesSeleccionadas);
            })->whereHas('materiasPeriodo', function ($q) {
                $q->where('periodo_id', $this->periodoSeleccionado);
            })->orderBy('nombre')->get();
            $this->dispatch('actualizar-materias-select2', materias: $this->materias->toArray());
        }
    }

    public function updatedSemanaSeleccionada()
    {
        $this->reporteConsultado = false;
        $this->datosReporte = [];
    }

    public function updatedMateriasSeleccionadas()
    {
        $this->reporteConsultado = false;
        $this->datosReporte = [];
    }

    private function calcularSemanasDelPeriodo($periodoId): void
    {
        $periodo = Periodo::with('escuela')->find($periodoId);
        if (! $periodo) {
            return;
        }
        $diaInicioSemana = $periodo->escuela->dia_inicio_semana ?? 0;
        $fechaInicio = Carbon::parse($periodo->fecha_inicio);
        $fechaFin = Carbon::parse($periodo->fecha_fin);
        $fechaInicioReal = $fechaInicio->copy()->startOfWeek($diaInicioSemana);
        $periodoDeFechas = CarbonPeriod::create($fechaInicioReal, '1 week', $fechaFin);
        $this->semanas = [];
        $contadorSemana = 1;
        foreach ($periodoDeFechas as $fecha) {
            $inicioSemana = $fecha->copy()->startOfWeek($diaInicioSemana);
            $finSemana = $fecha->copy()->endOfWeek($diaInicioSemana + 6);
            $this->semanas[] = [
                'valor' => $inicioSemana->toDateString().'|'.$finSemana->toDateString(),
                'texto' => "Semana {$contadorSemana}: ".$inicioSemana->isoFormat('D MMM').' - '.$finSemana->isoFormat('D MMM, YYYY'),
            ];
            $contadorSemana++;
        }
    }

    /**
     * Consulta y calcula las métricas y gráficos de asistencia para los filtros seleccionados.
     */
    public function consultarReporte(): void
    {
        $this->validate([
            'periodoSeleccionado' => 'required|integer|exists:periodos,id',
            'sedesSeleccionadas' => 'required|array|min:1',
            'sedesSeleccionadas.*' => 'integer|exists:sedes,id',
            'materiasSeleccionadas' => 'required|array|min:1',
            'materiasSeleccionadas.*' => 'integer|exists:materias,id',
            'semanaSeleccionada' => 'required|string',
        ]);

        $periodo = Periodo::find($this->periodoSeleccionado);
        if (! $periodo) {
            return;
        }

        $materias = Materia::whereIn('id', $this->materiasSeleccionadas)->orderBy('nombre')->get();
        [$inicioSemana, $finSemana] = explode('|', $this->semanaSeleccionada);
        $sedes = Sede::whereIn('id', $this->sedesSeleccionadas)->orderBy('nombre')->get();

        $this->infoSemanaTexto = Carbon::parse($inicioSemana)->isoFormat('D MMM').' al '.Carbon::parse($finSemana)->isoFormat('D MMM, YYYY');

        $datosParaReporte = [];
        $granTotal = [
            'totalMatriculas' => 0,
            'asistio' => 0,
            'ausente' => 0,
            'no_registrado' => 0,
            'desercion' => 0,
            'porcentajeAsistencia' => 0,
        ];

        $materiasStats = [];
        foreach ($materias as $mat) {
            $materiasStats[$mat->id] = [
                'id' => $mat->id,
                'nombre' => $mat->nombre,
                'legalizados' => 0,
                'asistio' => 0,
                'ausente' => 0,
                'no_registrado' => 0,
                'desercion' => 0,
            ];
        }

        $sedesStats = [];

        foreach ($sedes as $sede) {
            $filaSede = [
                'id' => $sede->id,
                'nombre' => $sede->nombre,
                'totalLegalizadosPeriodo' => Matricula::where('periodo_id', $periodo->id)
                    ->where('sede_id', $sede->id)
                    ->whereNotIn('estado_pago_matricula', ['anulada', 'rechazada'])
                    ->count(),
                'totales' => [
                    'legalizados' => 0,
                    'asistio' => 0,
                    'ausente' => 0,
                    'no_registrado' => 0,
                    'desercion' => 0,
                ],
                'materias' => [],
            ];

            foreach ($materias as $materia) {
                $matriculas = Matricula::where('periodo_id', $periodo->id)
                    ->where('sede_id', $sede->id)
                    ->whereNotIn('estado_pago_matricula', ['anulada', 'rechazada'])
                    ->whereHas('horarioMateriaPeriodo.materiaPeriodo', fn ($q) => $q->where('materia_id', $materia->id))
                    ->get();

                $legalizadosMateria = $matriculas->count();
                $contadores = ['asistio' => 0, 'ausente' => 0, 'no_registrado' => 0, 'desercion' => 0];

                if ($legalizadosMateria > 0) {
                    $alumnosDesertadosIds = $matriculas->where('bloqueado', true)
                        ->whereBetween('fecha_bloqueo', [$inicioSemana.' 00:00:00', $finSemana.' 23:59:59'])
                        ->pluck('user_id');
                    $contadores['desercion'] = $alumnosDesertadosIds->count();
                    $matriculasActivas = $matriculas->whereNotIn('user_id', $alumnosDesertadosIds);
                    $horariosIds = $matriculas->pluck('horario_materia_periodo_id')->unique();

                    $reportes = ReporteAsistenciaClase::whereIn('horario_materia_periodo_id', $horariosIds)
                        ->whereBetween('fecha_clase_reportada', [$inicioSemana, $finSemana])
                        ->with('detallesAsistencia')->get()->flatMap->detallesAsistencia->keyBy('user_id');

                    foreach ($matriculasActivas as $matricula) {
                        $detalle = $reportes->get($matricula->user_id);
                        if ($detalle) {
                            if ($detalle->asistio) {
                                $contadores['asistio']++;
                            } else {
                                $contadores['ausente']++;
                            }
                        } else {
                            $contadores['no_registrado']++;
                        }
                    }
                }

                $filaSede['totales']['legalizados'] += $legalizadosMateria;
                $filaSede['totales']['asistio'] += $contadores['asistio'];
                $filaSede['totales']['ausente'] += $contadores['ausente'];
                $filaSede['totales']['no_registrado'] += $contadores['no_registrado'];
                $filaSede['totales']['desercion'] += $contadores['desercion'];

                $filaSede['materias'][] = [
                    'id' => $materia->id,
                    'nombre' => $materia->nombre,
                    'legalizados' => $legalizadosMateria,
                    'contadores' => $contadores,
                    'porcentajeAsistencia' => ($contadores['asistio'] + $contadores['ausente']) > 0
                        ? round(($contadores['asistio'] * 100) / ($contadores['asistio'] + $contadores['ausente']), 1)
                        : null,
                ];

                $materiasStats[$materia->id]['legalizados'] += $legalizadosMateria;
                $materiasStats[$materia->id]['asistio'] += $contadores['asistio'];
                $materiasStats[$materia->id]['ausente'] += $contadores['ausente'];
                $materiasStats[$materia->id]['no_registrado'] += $contadores['no_registrado'];
                $materiasStats[$materia->id]['desercion'] += $contadores['desercion'];
            }

            $filaSede['porcentajeAsistencia'] = ($filaSede['totales']['asistio'] + $filaSede['totales']['ausente']) > 0
                ? round(($filaSede['totales']['asistio'] * 100) / ($filaSede['totales']['asistio'] + $filaSede['totales']['ausente']), 1)
                : null;

            $granTotal['totalMatriculas'] += $filaSede['totales']['legalizados'];
            $granTotal['asistio'] += $filaSede['totales']['asistio'];
            $granTotal['ausente'] += $filaSede['totales']['ausente'];
            $granTotal['no_registrado'] += $filaSede['totales']['no_registrado'];
            $granTotal['desercion'] += $filaSede['totales']['desercion'];

            $sedesStats[] = [
                'nombre' => $sede->nombre,
                'asistio' => $filaSede['totales']['asistio'],
                'ausente' => $filaSede['totales']['ausente'],
                'no_registrado' => $filaSede['totales']['no_registrado'],
                'desercion' => $filaSede['totales']['desercion'],
                'porcentajeAsistencia' => $filaSede['porcentajeAsistencia'],
            ];

            $datosParaReporte[] = $filaSede;
        }

        $granTotal['porcentajeAsistencia'] = ($granTotal['asistio'] + $granTotal['ausente']) > 0
            ? round(($granTotal['asistio'] * 100) / ($granTotal['asistio'] + $granTotal['ausente']), 1)
            : 0;

        $this->datosReporte = $datosParaReporte;
        $this->granTotalReporte = $granTotal;

        $this->graficoGeneral = [
            'labels' => ['Asistió', 'No asistió (Ausente)', 'Sin reporte tomado', 'Deserciones'],
            'series' => [
                $granTotal['asistio'],
                $granTotal['ausente'],
                $granTotal['no_registrado'],
                $granTotal['desercion'],
            ],
            'colors' => ['#28c76f', '#ea5455', '#ff9f43', '#7367f0'],
        ];

        $this->graficoSedes = [
            'categories' => array_column($sedesStats, 'nombre'),
            'asistio' => array_column($sedesStats, 'asistio'),
            'ausente' => array_column($sedesStats, 'ausente'),
            'no_registrado' => array_column($sedesStats, 'no_registrado'),
            'desercion' => array_column($sedesStats, 'desercion'),
        ];

        $materiasArray = array_values($materiasStats);
        $this->graficoMaterias = [
            'categories' => array_column($materiasArray, 'nombre'),
            'asistio' => array_column($materiasArray, 'asistio'),
            'ausente' => array_column($materiasArray, 'ausente'),
            'porcentajes' => array_map(function ($m) {
                return ($m['asistio'] + $m['ausente']) > 0
                    ? round(($m['asistio'] * 100) / ($m['asistio'] + $m['ausente']), 1)
                    : 0;
            }, $materiasArray),
        ];

        $this->reporteConsultado = true;

        $this->dispatch('reporteAsistenciaGenerado', [
            'general' => $this->graficoGeneral,
            'sedes' => $this->graficoSedes,
            'materias' => $this->graficoMaterias,
        ]);
    }

    public function render()
    {
        return view('livewire.reportes.filtros-asistencia-periodo');
    }
}
