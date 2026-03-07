<?php

namespace App\Services;

use App\Models\Actividad;
use App\Models\User;
use App\Models\Materia;
use App\Models\Periodo;
use App\Models\Matricula;
use App\Models\MateriaAprobadaUsuario;
use App\Models\AlumnoRespuestaItem;
use App\Models\ReporteAsistenciaAlumnos;
// CORRECCIÓN: Se ajusta el nombre del modelo a 'Calificaciones' (plural).
use App\Models\Calificaciones;
use App\Models\ItemCorteMateriaPeriodo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;

/**
 * Servicio ValidadorEscuelas (Versión 2.2 - Final)
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
     *
     * @param Actividad $actividad
     * @param User $usuario
     * @return array
     */
    public function filtrarCategoriasDisponibles(Actividad $actividad, User $usuario): array
    {
        // 1. OBTENER ESTADO ACTUAL DEL ESTUDIANTE
        $materiasAprobadasIds = MateriaAprobadaUsuario::where('user_id', $usuario->id)
            ->where('aprobado', true)
            ->pluck('materia_id')->toArray();

        // Se obtienen las materias que el usuario está cursando AHORA en períodos activos.
        $materiasEnCurso = Materia::whereHas('materiasPeriodo.horariosMateriaPeriodo.matriculasDeAlumnos', function ($query) use ($usuario) {
            $query->where('user_id', $usuario->id)
                ->whereHas('periodo', fn($q) => $q->where('estado', true));
        })->get();
        $materiasEnCursoIds = $materiasEnCurso->pluck('id')->toArray();


        $categoriasDisponibles = collect();
        $primerErrorEncontrado = null;

        // 2. ITERAR Y VALIDAR CADA CATEGORÍA/MATERIA OFRECIDA
        foreach ($actividad->categorias as $categoria) {
            if (!$categoria->materiaPeriodo?->materia) continue;

            $materiaObjetivo = $categoria->materiaPeriodo->materia;

            // --- NUEVA VALIDACIÓN: RESTRICCIONES DE CATEGORÍA (Género, Edad, Tipo Usuario, Procesos, Tareas) ---
            $resCategoria = $actividad->validarUsuarioEnCategoria($usuario, $categoria);
            if ($resCategoria->estado !== 'DISPONIBLE') {
                if (is_null($primerErrorEncontrado)) {
                    $primerErrorEncontrado = "Restricción en '{$categoria->nombre}': " . implode(', ', $resCategoria->motivos);
                }
                continue;
            }

            // --- NUEVA VALIDACIÓN: TAREAS REQUISITO DE LA MATERIA ---
            $motivosTareasMateria = [];
            // Usamos el método interno de actividad si es accesible, o el global.
            // Actividad tiene el helper _validarTareasRequisito pero es privado.
            // Sin embargo, podemos usar validarUsuarioEnCategoria o implementar el check aquí.
            // Mejor: Actividad tiene tareasRequisito relationship.
            if (!$actividad->validarTareasRequisitoCualquiera($usuario, $materiaObjetivo->tareasRequisito, $motivosTareasMateria)) {
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

            // REGLA 2: Si no tiene prerrequisitos, es una materia de inicio y siempre está disponible.
            if ($prerrequisitos->isEmpty()) {
                $categoriasDisponibles->push($categoria);
                continue;
            }

            // REGLA 3: Lógica secuencial.
            $todosPrerrequisitosAprobados = $prerrequisitos->every(fn($req) => in_array($req->id, $materiasAprobadasIds));
            $prerrequisitoEnCurso = $prerrequisitos->first(fn($req) => in_array($req->id, $materiasEnCursoIds));

            if ($todosPrerrequisitosAprobados) {
                // Caso Post-Período: Ya aprobó todo lo necesario, puede matricular esta materia.
                $categoriasDisponibles->push($categoria);
            } elseif ($prerrequisitoEnCurso) {
                // Caso Pre-Matrícula: Está cursando un prerrequisito.
                $otrosPrerrequisitos = $prerrequisitos->where('id', '!=', $prerrequisitoEnCurso->id);
                $otrosAprobados = $otrosPrerrequisitos->every(fn($req) => in_array($req->id, $materiasAprobadasIds));

                if ($otrosAprobados) {
                    $matriculaActiva = Matricula::where('user_id', $usuario->id)
                        ->whereHas('horarioMateriaPeriodo.materiaPeriodo.materia', fn($q) => $q->where('id', $prerrequisitoEnCurso->id))
                        ->with('periodo')->latest('id')->first();

                    $resultadoProgreso = $this->_validarProgresoEnTiempoReal($usuario, $prerrequisitoEnCurso, $matriculaActiva);

                    if ($resultadoProgreso['elegible']) {
                        $categoriasDisponibles->push($categoria);
                    } elseif (is_null($primerErrorEncontrado)) {
                        if (isset($resultadoProgreso['error_config'])) {
                            $primerErrorEncontrado = $resultadoProgreso['error_config'];
                        } else {
                            $mensajeError = "Para matricular '{$materiaObjetivo->nombre}', tu progreso en '{$prerrequisitoEnCurso->nombre}' no es suficiente. ";
                            if ($prerrequisitoEnCurso->habilitar_calificaciones) {
                                $mensajeError .= " <b> Nota actual: " . number_format($resultadoProgreso['nota_actual'], 2) . " (requerida: " . number_format($resultadoProgreso['nota_requerida'], 2) . ").</b>";
                            }
                            if ($prerrequisitoEnCurso->habilitar_asistencias) {
                                $mensajeError .= "<b> Asistencias: {$resultadoProgreso['asistencias_actuales']} (requeridas: {$resultadoProgreso['asistencias_requeridas']}).</b>";
                            }
                            $primerErrorEncontrado = $mensajeError;
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

        return ['success'  => false, 'message'  => $primerErrorEncontrado, 'categorias' => collect()];
    }

    /**
     * Valida el progreso EN TIEMPO REAL de un estudiante en una materia.
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

            // CORRECCIÓN: Se utiliza el modelo 'Calificaciones' (plural) como fue especificado.
            $calificacionAprobatoria = Calificaciones::where('sistema_calificacion_id', $matricula->periodo->sistema_calificaciones_id)
                ->where('aprobado', true)->orderBy('nota_minima', 'asc')->first();

            if (!$calificacionAprobatoria) {
                return [
                    'elegible' => false,
                    'nota_actual' => $notaActual,
                    'nota_requerida' => 0,
                    'asistencias_actuales' => 0,
                    'asistencias_requeridas' => $asistenciasRequeridas,
                    'error_config' => 'Error de configuración: No se encontró una nota aprobatoria para el período.'
                ];
            }
            $notaRequerida = $calificacionAprobatoria->nota_minima;

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
     * Valida si un usuario aprobó una materia consultando la tabla de resultados finales.
     */
    private function _validarResultadoFinal(User $usuario, Materia $materia): bool
    {
        return MateriaAprobadaUsuario::where('user_id', $usuario->id)
            ->where('materia_id', $materia->id)
            ->where('aprobado', true)
            ->exists();
    }

    /**
     * Helper de cálculo: Obtiene la nota ponderada actual de un estudiante.
     */
    private function _calcularNotaActualPonderada(Matricula $matricula): float
    {
        $horario = $matricula->horarioMateriaPeriodo()->with('materiaPeriodo.periodo.cortesPeriodo')->first();
        if (!$horario) return 0.0;

        $cortesDelPeriodo = $horario->materiaPeriodo->periodo->cortesPeriodo;
        $notaFinalPonderada = 0.0;

        foreach ($cortesDelPeriodo as $corte) {
            $itemsDelCorte = ItemCorteMateriaPeriodo::where('corte_periodo_id', $corte->id)
                ->where('horario_materia_periodo_id', $horario->id)
                ->get();
            if ($itemsDelCorte->isEmpty()) continue;

            $sumaNotasPonderadasDelCorte = 0.0;
            foreach ($itemsDelCorte as $item) {
                $calificacionEstudiante = AlumnoRespuestaItem::where('user_id', $matricula->user_id)
                    ->where('item_corte_materia_periodo_id', $item->id)->first();
                if ($calificacionEstudiante && !is_null($calificacionEstudiante->nota_obtenida)) {
                    $sumaNotasPonderadasDelCorte += $calificacionEstudiante->nota_obtenida * ($item->porcentaje / 100);
                }
            }
            $porcentajeDelCorteEnLaMateria = $corte->porcentaje ?? 0;
            $notaFinalPonderada += $sumaNotasPonderadasDelCorte * ($porcentajeDelCorteEnLaMateria / 100);
        }
        return round($notaFinalPonderada, 2);
    }

    /**
     * Helper de cálculo: Cuenta las asistencias de un estudiante.
     */
    private function _contarAsistenciasActuales(Matricula $matricula): int
    {
        return ReporteAsistenciaAlumnos::where('user_id', $matricula->user_id)
            ->where('asistio', true)
            ->whereHas('reporteClase', fn($q) => $q->where('horario_materia_periodo_id', $matricula->horario_materia_periodo_id))
            ->count();
    }
}
