<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Periodo;
use App\Models\Sede;
use App\Models\Materia;
use App\Models\HorarioMateriaPeriodo;
use App\Models\Matricula;
use App\Models\ReporteAsistenciaClase;
use Carbon\Carbon;
use App\Exports\ReporteAsistenciaExport; // <-- Asegúrate de añadir esta
use App\Exports\ReporteResumenSedeExport; // <-- Asegúrate de añadir esta
use Maatwebsite\Excel\Facades\Excel;   // <-- Y esta también

class ReporteEscuelaController extends Controller
{
    //
    public function vistaFiltros()
    {
        // Esta vista simplemente carga el componente de Livewire
        return view('contenido.paginas.escuelas.reportes.vista-filtros-asistencia');
    }

    /**
     * Genera el reporte de asistencia basado en los filtros enviados.
     * Este método calcula todas las estadísticas y las pasa a la vista de resultados.
     *
     * @param Request $request La petición entrante con los datos de los filtros.
     * @return \Illuminate\View\View
     */
    public function generarReporte(Request $request)
    {
        // 1. Valida la petición entrante para asegurar que todos los filtros requeridos están presentes.
        $validados = $request->validate([
            'periodoSeleccionado' => 'required|integer|exists:periodos,id',
            'sedesSeleccionadas' => 'required|array|min:1',
            'sedesSeleccionadas.*' => 'integer|exists:sedes,id',
            'materiaSeleccionada' => 'required|integer|exists:materias,id',
            'semanaSeleccionada' => 'required|string',
        ]);

        // 2. Prepara las variables principales a partir de los datos validados.
        $periodo = Periodo::find($validados['periodoSeleccionado']);
        $materia = Materia::find($validados['materiaSeleccionada']);
        list($inicioSemana, $finSemana) = explode('|', $validados['semanaSeleccionada']);
        $sedesIds = $validados['sedesSeleccionadas'];
        $materiaId = $validados['materiaSeleccionada'];

        // 3. Obtiene los datos base: todos los horarios de clase para la materia, periodo y sedes seleccionadas.
        // Se agrupan los resultados por el nombre de la sede para un procesamiento estructurado.
        $horariosDeLaMateria = HorarioMateriaPeriodo::with(['horarioBase.aula.sede', 'materiaPeriodo.materia'])
            ->whereHas('materiaPeriodo', function ($q) use ($periodo, $materiaId) {
                $q->where('periodo_id', $periodo->id)->where('materia_id', $materiaId);
            })
            ->whereHas('horarioBase.aula', function ($q) use ($sedesIds) {
                $q->whereIn('sede_id', $sedesIds);
            })
            ->get()
            ->groupBy('horarioBase.aula.sede.nombre');

        $datosParaReporte = [];
        $granTotalReporte = ['asistio' => 0, 'ausente' => 0, 'no_registrado' => 0, 'desercion' => 0];

        // 4. Itera a través de cada sede y sus horarios de clase correspondientes.
        foreach ($horariosDeLaMateria as $nombreSede => $horariosEnSede) {
            $reporteSede = [
                'nombre' => $nombreSede,
                'horarios' => [],
                'totales' => ['asistio' => 0, 'ausente' => 0, 'no_registrado' => 0, 'desercion' => 0]
            ];

            foreach ($horariosEnSede as $horario) {
                $matriculasDelHorario = Matricula::where('horario_materia_periodo_id', $horario->id)->get();
                if ($matriculasDelHorario->isEmpty()) {
                    continue; // Omite si no hay estudiantes matriculados en este horario.
                }

                $contadoresHorario = ['asistio' => 0, 'ausente' => 0, 'no_registrado' => 0, 'desercion' => 0];

                // A. Primero, identifica las deserciones de la semana. Tienen la máxima prioridad.
                $alumnosDesertadosIds = $matriculasDelHorario
                    ->where('bloqueado', true)
                    ->where('fecha_bloqueo', '>=', $inicioSemana . ' 00:00:00')
                    ->where('fecha_bloqueo', '<=', $finSemana . ' 23:59:59')
                    ->pluck('user_id');

                $contadoresHorario['desercion'] = $alumnosDesertadosIds->count();

                // B. Verifica si el maestro creó un reporte de asistencia para este horario durante la semana.
                $reporteDeLaSemana = ReporteAsistenciaClase::where('horario_materia_periodo_id', $horario->id)
                    ->whereBetween('fecha_clase_reportada', [$inicioSemana, $finSemana])
                    ->with('detallesAsistencia')
                    ->first();

                // C. Filtra los alumnos desertores para procesar a los que están activos.
                $matriculasActivas = $matriculasDelHorario->whereNotIn('user_id', $alumnosDesertadosIds);

                if (!$reporteDeLaSemana) {
                    // Si no se creó un reporte, todos los alumnos activos se marcan como "No Reportado".
                    $contadoresHorario['no_registrado'] = $matriculasActivas->count();
                } else {
                    // Si existe un reporte, se verifica el estado de cada alumno activo.
                    $detallesAsistencia = $reporteDeLaSemana->detallesAsistencia->keyBy('user_id');

                    foreach ($matriculasActivas as $matricula) {
                        $detalle = $detallesAsistencia->get($matricula->user_id);
                        if ($detalle) {
                            if ($detalle->asistio) {
                                $contadoresHorario['asistio']++;
                            } else {
                                $contadoresHorario['ausente']++;
                            }
                        } else {
                            // El alumno está activo pero no fue incluido en el reporte de la semana.
                            $contadoresHorario['no_registrado']++;
                        }
                    }
                }

                // D. Almacena los resultados del horario y actualiza los totales de la sede.
                $reporteSede['horarios'][] = [
                    'info' => $horario->horarioBase->dia_semana . ' ' . $horario->horarioBase->hora_inicio_formato . ' - Aula: ' . $horario->horarioBase->aula->nombre,
                    'contadores' => $contadoresHorario
                ];

                $reporteSede['totales']['asistio'] += $contadoresHorario['asistio'];
                $reporteSede['totales']['ausente'] += $contadoresHorario['ausente'];
                $reporteSede['totales']['no_registrado'] += $contadoresHorario['no_registrado'];
                $reporteSede['totales']['desercion'] += $contadoresHorario['desercion'];
            }

            // Acumula los totales de la sede en el gran total.
            $granTotalReporte['asistio'] += $reporteSede['totales']['asistio'];
            $granTotalReporte['ausente'] += $reporteSede['totales']['ausente'];
            $granTotalReporte['no_registrado'] += $reporteSede['totales']['no_registrado'];
            $granTotalReporte['desercion'] += $reporteSede['totales']['desercion'];

            $datosParaReporte[] = $reporteSede;
        }

        // 5. Devuelve la vista de resultados con todos los datos procesados.
        return view('contenido.paginas.escuelas.reportes.resultado-reporte-asistencia', [
            'datosReporte' => $datosParaReporte,
            'granTotalReporte' => $granTotalReporte,
            'periodo' => $periodo,
            'materia' => $materia,
            'infoSemana' => Carbon::parse($inicioSemana)->isoFormat('D MMM') . ' a ' . Carbon::parse($finSemana)->isoFormat('D MMM, YYYY')
        ]);
    }

    public function exportarReporte(Request $request)
    {
        $validados = $request->validate([
            'periodoSeleccionado' => 'required|integer|exists:periodos,id',
            'sedesSeleccionadas' => 'required|array|min:1',
            'sedesSeleccionadas.*' => 'integer|exists:sedes,id',
            // CAMBIO: Acepta un array de materias
            'materiasSeleccionadas' => 'required|array|min:1',
            'materiasSeleccionadas.*' => 'integer|exists:materias,id',
            'semanaSeleccionada' => 'required|string',
        ]);

        $periodo = Periodo::find($validados['periodoSeleccionado']);
        // CAMBIO: Obtiene la colección de materias
        $materias = Materia::whereIn('id', $validados['materiasSeleccionadas'])->get();
        list($inicioSemana, $finSemana) = explode('|', $validados['semanaSeleccionada']);
        $sedesIds = $validados['sedesSeleccionadas'];
        // CAMBIO: Usa el array de IDs
        $materiaIds = $validados['materiasSeleccionadas'];

        // CAMBIO: La consulta ahora agrupa por Sede Y por Materia
        $horariosAgrupados = HorarioMateriaPeriodo::with(['horarioBase.aula.sede', 'materiaPeriodo.materia'])
            ->whereHas('materiaPeriodo', function ($q) use ($periodo, $materiaIds) {
                $q->where('periodo_id', $periodo->id)->whereIn('materia_id', $materiaIds);
            })
            ->whereHas('horarioBase.aula', function ($q) use ($sedesIds) {
                $q->whereIn('sede_id', $sedesIds);
            })
            ->get()
            ->groupBy(['horarioBase.aula.sede.nombre', 'materiaPeriodo.materia.nombre']);

        $infoAdicional = [
            'periodo' => $periodo->nombre,
            // CAMBIO: Indica que son varias materias
            'materia' => 'Varias Materias Seleccionadas',
            'semana'  => Carbon::parse($inicioSemana)->isoFormat('D MMM') . ' a ' . Carbon::parse($finSemana)->isoFormat('D MMM, YYYY'),
            'inicioSemana' => $inicioSemana,
            'finSemana' => $finSemana,
        ];

        $nombreArchivo = 'Reporte_Detallado_Asistencia_' . date('Y-m-d') . '.xlsx';
        return Excel::download(new ReporteAsistenciaExport($horariosAgrupados, $infoAdicional), $nombreArchivo);
    }

    public function exportarReporteResumen(Request $request)
    {
        $validados = $request->validate([
            'periodoSeleccionado' => 'required|integer|exists:periodos,id',
            'sedesSeleccionadas' => 'required|array|min:1',
            'sedesSeleccionadas.*' => 'integer|exists:sedes,id',
            // CAMBIO: Acepta un array de materias
            'materiasSeleccionadas' => 'required|array|min:1',
            'materiasSeleccionadas.*' => 'integer|exists:materias,id',
            'semanaSeleccionada' => 'required|string',
        ]);

        $periodo = Periodo::find($validados['periodoSeleccionado']);
        // CAMBIO: Obtiene la colección de materias
        $materias = Materia::whereIn('id', $validados['materiasSeleccionadas'])->orderBy('nombre')->get();
        list($inicioSemana, $finSemana) = explode('|', $validados['semanaSeleccionada']);
        $sedes = Sede::whereIn('id', $validados['sedesSeleccionadas'])->orderBy('nombre')->get();

        $datosParaExportar = [];
        $granTotal = []; // El gran total ahora es por materia

        foreach ($sedes as $sede) {
            $filaSede = ['nombre' => $sede->nombre, 'stats' => []];

            // Total Legalizados de la Sede (sin cambios)
            $filaSede['totalLegalizadosPeriodo'] = Matricula::where('periodo_id', $periodo->id)->where('sede_id', $sede->id)->count();

            foreach ($materias as $materia) {
                if (!isset($granTotal[$materia->id])) {
                    $granTotal[$materia->id] = ['legalizadosMateria' => 0, 'asistio' => 0, 'ausente' => 0, 'no_registrado' => 0, 'desercion' => 0];
                }

                $matriculas = Matricula::where('periodo_id', $periodo->id)
                    ->where('sede_id', $sede->id)
                    ->whereHas('horarioMateriaPeriodo.materiaPeriodo', fn($q) => $q->where('materia_id', $materia->id))
                    ->get();

                $legalizadosMateria = $matriculas->count();
                $granTotal[$materia->id]['legalizadosMateria'] += $legalizadosMateria;

                $contadores = ['asistio' => 0, 'ausente' => 0, 'no_registrado' => 0, 'desercion' => 0];

                if ($legalizadosMateria > 0) {
                    $alumnosDesertadosIds = $matriculas->where('bloqueado', true)->whereBetween('fecha_bloqueo', [$inicioSemana, $finSemana])->pluck('user_id');
                    $contadores['desercion'] = $alumnosDesertadosIds->count();
                    $matriculasActivas = $matriculas->whereNotIn('user_id', $alumnosDesertadosIds);
                    $horariosIds = $matriculas->pluck('horario_materia_periodo_id')->unique();
                    $reportes = ReporteAsistenciaClase::whereIn('horario_materia_periodo_id', $horariosIds)
                        ->whereBetween('fecha_clase_reportada', [$inicioSemana, $finSemana])
                        ->with('detallesAsistencia')->get()->flatMap->detallesAsistencia->keyBy('user_id');

                    foreach ($matriculasActivas as $matricula) {
                        $detalle = $reportes->get($matricula->user_id);
                        if ($detalle) {
                            if ($detalle->asistio) $contadores['asistio']++;
                            else $contadores['ausente']++;
                        } else {
                            $contadores['no_registrado']++;
                        }
                    }
                }

                $filaSede['stats'][$materia->id] = ['legalizados' => $legalizadosMateria, 'contadores' => $contadores];

                $granTotal[$materia->id]['asistio'] += $contadores['asistio'];
                $granTotal[$materia->id]['ausente'] += $contadores['ausente'];
                $granTotal[$materia->id]['no_registrado'] += $contadores['no_registrado'];
                $granTotal[$materia->id]['desercion'] += $contadores['desercion'];
            }
            $datosParaExportar[] = $filaSede;
        }

        $nombreArchivo = 'Resumen_Gerencial_Multi-Materia_' . date('Y-m-d') . '.xlsx';

        $infoAdicional = [
            'periodo' => $periodo,
            'semana'  => Carbon::parse($inicioSemana)->isoFormat('D MMM') . ' a ' . Carbon::parse($finSemana)->isoFormat('D MMM, YYYY'),
        ];

        return Excel::download(new ReporteResumenSedeExport($datosParaExportar, $materias, $granTotal, $infoAdicional), $nombreArchivo);
    }
}
