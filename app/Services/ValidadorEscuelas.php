<?php

namespace App\Services;

use App\Models\Actividad;
use App\Models\AlumnoRespuestaItem;
use App\Models\Calificaciones;
use App\Models\CorteMateriaPeriodo;
use App\Models\ItemCorteMateriaPeriodo;
use App\Models\Materia;
use App\Models\MateriaAprobadaUsuario;
use App\Models\Matricula;
use App\Models\ReporteAsistenciaAlumnos;
use App\Models\User;

/**
 * Servicio ValidadorEscuelas (Versión 2.3 - Corregida)
 * Centraliza toda la lógica de negocio compleja para determinar si un usuario
 * puede matricularse en una o más materias de una actividad de tipo "Escuelas".
 * Es capaz de manejar validaciones para períodos cerrados (matrícula normal)
 * y períodos activos (pre-matrícula basada en progreso en tiempo real),
 * además de una lógica secuencial para guiar al estudiante.
 */
class ValidadorEscuelas
{
    /**
     * Método principal. Filtra las categorías de una actividad para mostrar solo las materias que el usuario
     * puede matricular secuencialmente, excluyendo las que ya aprobó o está cursando.
     */
    public function filtrarCategoriasDisponibles(Actividad $actividad, User $usuario): array
    {
        // 1. OBTENER ESTADO ACTUAL DEL ESTUDIANTE
        $materiasAprobadasIds = MateriaAprobadaUsuario::where('user_id', $usuario->id)
            ->where('aprobado', MateriaAprobadaUsuario::ESTADO_APROBADO)
            ->pluck('materia_id')->toArray();

        // Se obtienen las materias que el usuario está cursando AHORA en períodos activos (excluyendo matrículas anuladas/rechazadas).
        $materiasEnCurso = Materia::whereHas('materiasPeriodo.horariosMateriaPeriodo.matriculasDeAlumnos', function ($query) use ($usuario) {
            $query->where('user_id', $usuario->id)
                ->where('estado_pago_matricula', '!=', 'anulada')
                ->where('estado_pago_matricula', '!=', 'rechazada')
                ->whereHas('periodo', fn ($q) => $q->where('estado', true));
        })->get();
        $materiasEnCursoIds = $materiasEnCurso->pluck('id')->toArray();

        $categoriasDisponibles = collect();
        $primerErrorEncontrado = null;

        // 2. ITERAR Y VALIDAR CADA CATEGORÍA/MATERIA OFRECIDA
        foreach ($actividad->categorias as $categoria) {
            if (! $categoria->materiaPeriodo?->materia) {
                continue;
            }

            $materiaObjetivo = $categoria->materiaPeriodo->materia;

            // --- RESTRICCIONES DE CATEGORÍA (Género, Edad, Tipo Usuario, Sedes, Pasos Categoría, Tareas Categoría) ---
            $resCategoria = $actividad->validarUsuarioEnCategoria($usuario, $categoria);
            if ($resCategoria->estado !== 'DISPONIBLE') {
                if (is_null($primerErrorEncontrado)) {
                    $primerErrorEncontrado = "Restricción en '{$categoria->nombre}': ".implode(', ', $resCategoria->motivos);
                }

                continue;
            }

            // --- RESTRICCIONES DE PROCESOS (PASOS DE CRECIMIENTO) DE LA MATERIA OBJETIVO ---
            $motivosProcesosMateria = [];
            if (! $this->_validarProcesosPrerrequisitoMateria($usuario, $materiaObjetivo, $motivosProcesosMateria)) {
                if (is_null($primerErrorEncontrado)) {
                    $primerErrorEncontrado = implode('. ', $motivosProcesosMateria);
                }

                continue;
            }

            // --- TAREAS REQUISITO DE LA MATERIA OBJETIVO ---
            $motivosTareasMateria = [];
            if (! $actividad->validarTareasRequisitoCualquiera($usuario, $materiaObjetivo->tareasRequisito, $motivosTareasMateria)) {
                if (is_null($primerErrorEncontrado)) {
                    $primerErrorEncontrado = implode(', ', $motivosTareasMateria);
                }

                continue;
            }

            // REGLA 1: No mostrar materias ya aprobadas o que está cursando actualmente.
            if (in_array($materiaObjetivo->id, $materiasAprobadasIds) || in_array($materiaObjetivo->id, $materiasEnCursoIds)) {
                continue;
            }

            $prerrequisitos = $materiaObjetivo->prerrequisitosMaterias;

            // REGLA 2: Si no tiene prerrequisitos de materia, está disponible (ya pasó procesos/tareas de la materia).
            if ($prerrequisitos->isEmpty()) {
                $categoriasDisponibles->push($categoria);

                continue;
            }

            // REGLA 3: Lógica secuencial.
            $todosPrerrequisitosAprobados = $prerrequisitos->every(fn ($req) => in_array($req->id, $materiasAprobadasIds));
            $prerrequisitoEnCurso = $prerrequisitos->first(fn ($req) => in_array($req->id, $materiasEnCursoIds));

            if ($todosPrerrequisitosAprobados) {
                // Caso Post-Período: Ya aprobó todos los prerrequisitos académicos y cumple procesos/tareas.
                $categoriasDisponibles->push($categoria);
            } elseif ($prerrequisitoEnCurso) {
                // Caso Pre-Matrícula: Está cursando un prerrequisito en un período activo.
                $otrosPrerrequisitos = $prerrequisitos->where('id', '!=', $prerrequisitoEnCurso->id);
                $otrosAprobados = $otrosPrerrequisitos->every(fn ($req) => in_array($req->id, $materiasAprobadasIds));

                if ($otrosAprobados) {
                    $matriculaActiva = Matricula::where('user_id', $usuario->id)
                        ->whereHas('horarioMateriaPeriodo.materiaPeriodo.materia', fn ($q) => $q->where('id', $prerrequisitoEnCurso->id))
                        ->with('periodo')->latest('id')->first();

                    if ($matriculaActiva) {
                        $resultadoProgreso = $this->_validarProgresoEnTiempoReal($usuario, $prerrequisitoEnCurso, $matriculaActiva);

                        if ($resultadoProgreso['elegible']) {
                            $categoriasDisponibles->push($categoria);
                        } elseif (is_null($primerErrorEncontrado)) {
                            if (isset($resultadoProgreso['error_config'])) {
                                $primerErrorEncontrado = $resultadoProgreso['error_config'];
                            } else {
                                $mensajeError = "Para matricular '{$materiaObjetivo->nombre}', tu progreso en '{$prerrequisitoEnCurso->nombre}' no es suficiente. ";
                                if ($prerrequisitoEnCurso->habilitar_calificaciones) {
                                    $mensajeError .= ' <b> Nota actual: '.number_format($resultadoProgreso['nota_actual'], 2).' (requerida: '.number_format($resultadoProgreso['nota_requerida'], 2).').</b>';
                                }
                                if ($prerrequisitoEnCurso->habilitar_asistencias) {
                                    $mensajeError .= "<b> Asistencias: {$resultadoProgreso['asistencias_actuales']} (requeridas: {$resultadoProgreso['asistencias_requeridas']}).</b>";
                                }
                                $primerErrorEncontrado = $mensajeError;
                            }
                        }
                    }
                }
            } else {
                if (is_null($primerErrorEncontrado)) {
                    $primerErrorEncontrado = "<b> Para matricular '{$materiaObjetivo->nombre}', primero debes cursar y aprobar sus prerrequisitos. </b>";
                }
            }
        }

        if ($categoriasDisponibles->isNotEmpty()) {
            return ['success' => true, 'message' => null, 'categorias' => $categoriasDisponibles];
        }

        return ['success' => false, 'message' => $primerErrorEncontrado, 'categorias' => collect()];
    }

    /**
     * Valida si el usuario cumple con los Pasos de Crecimiento (procesos) requeridos por la materia objetivo.
     */
    private function _validarProcesosPrerrequisitoMateria(User $usuario, Materia $materia, array &$motivos): bool
    {
        $cumple = true;
        foreach ($materia->procesosPrerrequisito as $procesoReq) {
            $estadoRequerido = $procesoReq->pivot->estado_paso_crecimiento_usuario_id ?? $procesoReq->pivot->estado_proceso;
            $pasoUsuario = $usuario->pasosCrecimiento()->where('paso_crecimiento_id', $procesoReq->id)->first();

            if (! $pasoUsuario || $pasoUsuario->pivot->estado_id != $estadoRequerido) {
                $cumple = false;
                $motivos[] = "Necesitas haber completado el proceso '{$procesoReq->nombre}' para matricular '{$materia->nombre}'";
            }
        }

        return $cumple;
    }

    /**
     * Valida el progreso EN TIEMPO REAL de un estudiante en una materia prerrequisito.
     */
    private function _validarProgresoEnTiempoReal(User $usuario, Materia $materia, Matricula $matricula): array
    {
        $aprobadoPorNota = true;
        $aprobadoPorAsistencia = true;
        $notaActual = 0.0;
        $notaRequerida = 0.0;
        $asistenciasActuales = 0;
        $asistenciasRequeridas = $materia->asistencias_minimas ?? 0;

        if ($materia->habilitar_calificaciones) {
            $notaActual = $this->_calcularNotaActualPonderada($matricula);

            $calificacionAprobatoria = Calificaciones::where('sistema_calificacion_id', $matricula->periodo->sistema_calificaciones_id)
                ->where('aprobado', true)->orderBy('nota_minima', 'asc')->first();

            if (! $calificacionAprobatoria) {
                return [
                    'elegible' => false,
                    'nota_actual' => $notaActual,
                    'nota_requerida' => 0,
                    'asistencias_actuales' => 0,
                    'asistencias_requeridas' => $asistenciasRequeridas,
                    'error_config' => 'Error de configuración: No se encontró una nota aprobatoria para el período.',
                ];
            }
            $notaRequerida = (float) $calificacionAprobatoria->nota_minima;

            if ($notaActual < $notaRequerida) {
                $aprobadoPorNota = false;
            }
        }

        if ($materia->habilitar_asistencias) {
            $asistenciasActuales = $this->_contarAsistenciasActuales($matricula);
            if ($asistenciasActuales < $asistenciasRequeridas) {
                $aprobadoPorAsistencia = false;
            }
        }

        return [
            'elegible' => ($aprobadoPorNota && $aprobadoPorAsistencia),
            'nota_actual' => $notaActual,
            'nota_requerida' => $notaRequerida,
            'asistencias_actuales' => $asistenciasActuales,
            'asistencias_requeridas' => $asistenciasRequeridas,
        ];
    }

    /**
     * Helper de cálculo: Obtiene la nota acumulada normalizada sobre el peso evaluado a la fecha.
     */
    private function _calcularNotaActualPonderada(Matricula $matricula): float
    {
        $horarioId = $matricula->horario_materia_periodo_id;
        $usuarioId = $matricula->user_id;

        $horario = $matricula->horarioMateriaPeriodo()->with('materiaPeriodo.periodo.cortesPeriodo')->first();
        if (! $horario || ! $horario->materiaPeriodo) {
            return 0.0;
        }

        $cortesMateria = CorteMateriaPeriodo::where('materia_periodo_id', $horario->materia_periodo_id)->get();
        if ($cortesMateria->isEmpty()) {
            return 0.0;
        }

        $sumaPonderadaFinal = 0.0;
        $pesoTotalEvaluadoFinal = 0.0;

        foreach ($cortesMateria as $corteMateria) {
            $items = ItemCorteMateriaPeriodo::where('corte_periodo_id', $corteMateria->corte_periodo_id)
                ->where('horario_materia_periodo_id', $horarioId)
                ->get();

            if ($items->isEmpty()) {
                continue;
            }

            $sumaPonderadaCorte = 0.0;
            $pesoTotalItemsCorte = 0.0;
            $hayNotasEnCorte = false;

            foreach ($items as $item) {
                $respuesta = AlumnoRespuestaItem::where('user_id', $usuarioId)
                    ->where('item_corte_materia_periodo_id', $item->id)
                    ->first();

                if ($respuesta && ! is_null($respuesta->nota_obtenida)) {
                    $sumaPonderadaCorte += ($respuesta->nota_obtenida * $item->porcentaje);
                    $pesoTotalItemsCorte += $item->porcentaje;
                    $hayNotasEnCorte = true;
                }
            }

            if ($hayNotasEnCorte && $pesoTotalItemsCorte > 0) {
                // Nota del corte normalizada
                $notaCorte = $sumaPonderadaCorte / $pesoTotalItemsCorte;

                // Sumar al cálculo global usando el peso del corte en la materia
                $sumaPonderadaFinal += ($notaCorte * $corteMateria->porcentaje);
                $pesoTotalEvaluadoFinal += $corteMateria->porcentaje;
            }
        }

        // CORRECCIÓN MATEMÁTICA: Normalizar sobre el total del porcentaje de cortes evaluados a la fecha
        if ($pesoTotalEvaluadoFinal > 0) {
            return round($sumaPonderadaFinal / $pesoTotalEvaluadoFinal, 2);
        }

        return 0.0;
    }

    /**
     * Helper de cálculo: Cuenta las asistencias positivas de un estudiante.
     */
    private function _contarAsistenciasActuales(Matricula $matricula): int
    {
        return ReporteAsistenciaAlumnos::where('user_id', $matricula->user_id)
            ->where('asistio', true)
            ->whereHas('reporteClase', fn ($q) => $q->where('horario_materia_periodo_id', $matricula->horario_materia_periodo_id))
            ->count();
    }
}
