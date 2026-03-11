<?php

namespace App\Livewire\Cursos;

use App\Models\Curso;
use App\Models\CursoItem;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CampusCurso extends Component
{
    public $curso;

    public $cursoId;

    public $itemActivoId = null;

    public $itemActivo = null;

    // Propiedades para el tracking de progreso
    public $progresoPorcentaje = 0;

    public $itemsProgreso = []; // Array de itemId => 'bloqueado', 'iniciado', 'completado'

    public $itemsOrdenados = []; // Arreglo unidimensional de IDs para saber el orden secuencial

    // --- Propiedades para Evaluaciones ---
    public $preguntasEvaluacion = []; // Almacenamos las preguntas ordenadas aleatoriamente

    public $preguntaActualIndex = 0; // Índice de la pregunta visible actualmente en el frontend

    public $respuestasEvaluacion = []; // Almacena las respuestas del estudiante: [pregunta_id => [opcion_id1, opcion_id2...]]

    public $evaluacionResultado = null; // Último resultado obtenido

    public $intentosRealizados = 0;

    public $evaluacionBloqueada = false;

    public $horasRestantesDilatacion = 0;

    public $evaluacionConfig = null;

    public $inicioExamen = null;

    public function mount($slug)
    {
        $this->curso = Curso::where('slug', $slug)
            ->with(['modulos.items.tipo', 'rolesRestringidos', 'pasosRequisito', 'tareasRequisito', 'equipo.user'])
            ->firstOrFail();

        $this->cursoId = $this->curso->id;

        // Validar si el usuario está inscrito
        $user = Auth::user();
        if (! $this->curso->usuarios()->where('user_id', $user->id)->exists()) {
            abort(403, 'No estás inscrito en este curso.');
        }

        // Cargar y procesar el progreso general para bloquear/desbloquear items
        $this->cargarProgreso();
    }

    /**
     * Calcula el progreso actual del usuario basándose en la tabla curso_item_user.
     * Define qué ítems están bloqueados, iniciados y completados.
     */
    public function cargarProgreso()
    {
        $user = Auth::user();

        // Obtener todos los IDs de los ítems del curso en orden (Módulo -> Item)
        $this->itemsOrdenados = [];
        foreach ($this->curso->modulos as $modulo) {
            foreach ($modulo->items as $item) {
                $this->itemsOrdenados[] = $item->id;
            }
        }

        // Cargar registros de progreso del estudiante desde la BD
        $progresos = \App\Models\CursoItemUser::where('user_id', $user->id)
            ->whereIn('curso_item_id', $this->itemsOrdenados)
            ->get()
            ->keyBy('curso_item_id');

        $totalItems = count($this->itemsOrdenados);
        $itemsCompletados = 0;
        $this->itemsProgreso = [];

        // Lógica de validación estricta de orden: Un ítem requiere que el anterior esté completado
        $anteriorCompletado = true; // El primer ítem de todos siempre está desbloqueado
        $primerItemPendienteId = null;

        foreach ($this->itemsOrdenados as $itemId) {
            $registro = $progresos->get($itemId);
            $estadoItem = 'bloqueado';

            if ($registro && $registro->estado === 'completado') {
                $estadoItem = 'completado';
                $itemsCompletados++;
                $anteriorCompletado = true; // Permite que el próximo se desbloquee
            } else {
                if ($anteriorCompletado) {
                    $estadoItem = 'iniciado'; // Desbloqueado porque el anterior se completó
                    if (! $primerItemPendienteId) {
                        $primerItemPendienteId = $itemId;
                    }
                }
                $anteriorCompletado = false; // Como este NO está completo, bloquea obligatoriamente a todos los siguientes
            }

            $this->itemsProgreso[$itemId] = $estadoItem;
        }

        // Si no hay item activo seleccionado, seleccionamos el primero que está pendiente
        if (! $this->itemActivoId) {
            if ($primerItemPendienteId) {
                $this->seleccionarItem($primerItemPendienteId, true);
            } else {
                // Si completó todo (no hay pendientes), seleccionamos el primer ítem del curso
                $this->seleccionarItem($this->itemsOrdenados[0] ?? null, true);
            }
        } else {
            // Actualizamos el objeto del ítem activo por si sus datos cambiaron (ej: carga de relaciones)
            $this->itemActivo = CursoItem::with('tipo', 'itemable')->find($this->itemActivoId);
        }

        // Calculamos el porcentaje general del curso
        $this->progresoPorcentaje = $totalItems > 0 ? round(($itemsCompletados / $totalItems) * 100) : 0;

        // Actualizamos la tabla general curso_users para reflejar el progreso global del curso
        \App\Models\CursoUser::where('curso_id', $this->cursoId)
            ->where('user_id', $user->id)
            ->update(['porcentaje_progreso' => $this->progresoPorcentaje]);
    }

    /**
     * Selecciona un ítem de la lista (temario). Revisa si está bloqueado.
     */
    public function seleccionarItem($itemId, $forzar = false)
    {
        if (! $itemId) {
            return;
        }

        // Verifica si está bloqueado, a menos que se fuerce internamente (ej: al cargar la página)
        if (! $forzar && isset($this->itemsProgreso[$itemId]) && $this->itemsProgreso[$itemId] === 'bloqueado') {
            session()->flash('errorItem', 'Debes completar las lecciones anteriores para acceder a esta.');

            return; // Aborta la selección
        }

        $this->itemActivoId = $itemId;
        $this->itemActivo = CursoItem::with('tipo', 'itemable')->find($itemId);

        // Registramos en BD que el usuario "inició" la visualización de este ítem (si no lo había hecho ya)
        $user = Auth::user();
        \App\Models\CursoItemUser::firstOrCreate([
            'curso_item_id' => $itemId,
            'user_id' => $user->id,
        ], [
            'estado' => 'iniciado',
        ]);

        // Si el ítem es una evaluación, disparamos el flujo de carga
        if (in_array($this->itemActivo->tipo->codigo, ['evaluacion', 'quiz', 'final']) && $this->itemActivo->itemable) {
            $this->cargarEvaluacion();
        }

        // Emitimos un evento a Alpine/JavaScript para que reinicie los oyentes de progreso (scroll, video duration)
        $this->dispatch('item-cambiado', itemId: $itemId);
    }

    /**
     * Inicializa el estado para presentar la evaluación al estudiante.
     * Carga las preguntas, las mezcla (aleatorio) y reinicia índices y respuestas.
     */
    public function cargarEvaluacion()
    {
        $user = Auth::user();
        $this->evaluacionConfig = $this->itemActivo->itemable;
        $evaluacion = $this->evaluacionConfig;

        // 1. Verificar historia de intentos
        $intentos = \App\Models\CursoEvaluacionResultado::where('user_id', $user->id)
            ->where('curso_item_id', $this->itemActivo->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $this->intentosRealizados = $intentos->count();
        $ultimoIntento = $intentos->first();

        $this->evaluacionBloqueada = false;
        $this->horasRestantesDilatacion = 0;

        // 0. Si ya está aprobado, no aplicamos bloqueos de intentos
        if (isset($this->itemsProgreso[$this->itemActivo->id]) && $this->itemsProgreso[$this->itemActivo->id] === 'completado') {
            return;
        }

        // 2. Lógica de Dilatación e Intentos
        $totalPermitidos = 1 + ($evaluacion->cantidad_repeticiones ?? 0);

        if ($this->intentosRealizados > 0 && $this->intentosRealizados >= $totalPermitidos) {
            if ($ultimoIntento) {
                // Ya cumplió todos los intentos. Verificar si ya pasó el tiempo de dilatación.
                $fechaUltimo = $ultimoIntento->created_at;
                $horasTranscurridas = $fechaUltimo->diffInHours(now());

                if ($horasTranscurridas < $evaluacion->tiempo_dilatacion) {
                    $this->evaluacionBloqueada = true;
                    $this->horasRestantesDilatacion = $evaluacion->tiempo_dilatacion - $horasTranscurridas;

                    return; // No cargamos preguntas, está bloqueado
                } else {
                    // Pasó el tiempo de dilatación. "Borramos" registros anteriores para reiniciar ciclo
                    \App\Models\CursoEvaluacionResultado::where('user_id', $user->id)
                        ->where('curso_item_id', $this->itemActivo->id)
                        ->delete();
                    $this->intentosRealizados = 0;
                }
            }
        }

        // 3. Cargar preguntas si no está bloqueado
        $preguntas = $evaluacion->preguntas()->with('opciones')->get();
        $this->preguntasEvaluacion = $preguntas->shuffle()->values()->all();
        $this->preguntaActualIndex = 0;
        $this->respuestasEvaluacion = [];

        foreach ($this->preguntasEvaluacion as $pregunta) {
            $this->respuestasEvaluacion[$pregunta->id] = [];
        }

        $sessionKey = "eval_start_{$user->id}_{$this->itemActivo->id}";
        if (!session()->has($sessionKey)) {
            session()->put($sessionKey, now()->timestamp);
        }
        $this->inicioExamen = session($sessionKey);
    }

    /**
     * Navegación de preguntas en la Evaluación
     */
    public function irAPregunta($index)
    {
        if (isset($this->preguntasEvaluacion[$index])) {
            $this->preguntaActualIndex = $index;
        }
    }

    public function preguntaAnterior()
    {
        if ($this->preguntaActualIndex > 0) {
            $this->preguntaActualIndex--;
        }
    }

    public function siguientePregunta()
    {
        if ($this->preguntaActualIndex < (count($this->preguntasEvaluacion) - 1)) {
            $this->preguntaActualIndex++;
        }
    }

    /**
     * Método invocado desde la UI para seleccionar una opción de una pregunta.
     * Soporta single-choice y multi-choice.
     */
    public function seleccionarRespuesta($preguntaId, $opcionId, $tipoPregunta)
    {
        if ($tipoPregunta === 'unica' || $tipoPregunta === 'verdadero_falso') {
            // Reemplaza cualquier respuesta previa con la nueva (sólo un elemento en el array)
            $this->respuestasEvaluacion[$preguntaId] = [$opcionId];
        } elseif ($tipoPregunta === 'multiple') {
            // Hacemos toggle: si ya está, la quitamos; si no, la agregamos
            if (in_array($opcionId, $this->respuestasEvaluacion[$preguntaId])) {
                $this->respuestasEvaluacion[$preguntaId] = array_values(array_diff($this->respuestasEvaluacion[$preguntaId], [$opcionId]));
            } else {
                $this->respuestasEvaluacion[$preguntaId][] = $opcionId;
            }
        }
    }

    /**
     * Verifica que antes de enviar, TODAS las preguntas tengan al menos 1 respuesta.
     */
    public function validarYEnviarEvaluacion()
    {
        $todasRespondidas = true;

        foreach ($this->preguntasEvaluacion as $pregunta) {
            // Si el array de esta pregunta está vacío, es que no seleccionó nada
            if (empty($this->respuestasEvaluacion[$pregunta->id])) {
                $todasRespondidas = false;
                break;
            }
        }

        if (! $todasRespondidas) {
            // Emitimos evento de Fire SweetAlert (el script en la vista lo captura)
            $this->dispatch('evaluacion-incompleta');
        } else {
            // Si todas tienen respuesta, emitimos evento para preguntar "¿Estás seguro de enviar?"
            $this->dispatch('confirmar-envio-evaluacion');
        }
    }

    /**
     * Este evento se llama después de que el usuario acepta el modal "¿Estás seguro?".
     * Según instrucciones, por AHORA no se evalúa calificación real, esto es un Placeholder.
     */
    public function procesarEnvioEvaluacion()
    {
        $user = Auth::user();
        $evaluacion = $this->evaluacionConfig;

        // Validar Tiempo Límite si existe
        if ($evaluacion->limite_tiempo > 0 && $this->inicioExamen) {
            $segundosTranscurridos = now()->timestamp - $this->inicioExamen;
            $segundosMaximos = $evaluacion->limite_tiempo * 60;

            if ($segundosTranscurridos > ($segundosMaximos + 10)) { // 10 seg de margen
                $this->dispatch('tiempo-agotado');
                return;
            }
        }

        $totalPreguntas = count($this->preguntasEvaluacion);
        if ($totalPreguntas === 0) return;

        $puntosTotales = 0;

        foreach ($this->preguntasEvaluacion as $pregunta) {
            $respuestasUsuario = $this->respuestasEvaluacion[$pregunta->id] ?? [];

            if ($pregunta->tipo_respuesta === 'multiple') {
                $opcionesCorrectas = $pregunta->opciones->where('es_correcta', true);
                $totalCorrectas = $opcionesCorrectas->count();

                if ($totalCorrectas > 0) {
                    $hits = 0;
                    foreach ($respuestasUsuario as $opcId) {
                        if ($opcionesCorrectas->pluck('id')->contains($opcId)) {
                            $hits++;
                        }
                    }
                    // Calificación proporcional: (aciertos / total_correctas)
                    $puntosPregunta = $hits / $totalCorrectas;
                    $puntosTotales += $puntosPregunta;
                }
            } else {
                // Única o Verdadero/Falso: Comparar si la opción marcada es la correcta
                $opcionCorrecta = $pregunta->opciones->where('es_correcta', true)->first();
                if ($opcionCorrecta && in_array($opcionCorrecta->id, $respuestasUsuario)) {
                    $puntosTotales += 1;
                }
            }
        }

        $notaFinal = ($puntosTotales / $totalPreguntas) * 100;
        $aprobado = $notaFinal >= $evaluacion->minimo_aprobacion;

        // Registrar intento
        $intentoActual = \App\Models\CursoEvaluacionResultado::where('user_id', $user->id)
            ->where('curso_item_id', $this->itemActivo->id)
            ->count() + 1;

        $this->evaluacionResultado = \App\Models\CursoEvaluacionResultado::create([
            'user_id' => $user->id,
            'curso_id' => $this->cursoId,
            'curso_item_id' => $this->itemActivo->id,
            'curso_evaluacion_id' => $evaluacion->id,
            'nota' => $notaFinal,
            'aprobado' => $aprobado,
            'intento' => $intentoActual,
            'respuestas_json' => $this->respuestasEvaluacion,
        ]);

        // Limpiar tiempo de inicio en sesión al terminar
        session()->forget("eval_start_{$user->id}_{$this->itemActivo->id}");

        if ($aprobado) {
            $this->dispatch('evaluacion-aprobada', nota: round($notaFinal, 2));
            $this->marcarCompletado($this->itemActivo->id);
        } else {
            $this->intentosRealizados = $intentoActual;
            $totalPermitidos = 1 + ($evaluacion->cantidad_repeticiones ?? 0);

            if ($this->intentosRealizados >= $totalPermitidos) {
                $this->evaluacionBloqueada = true;
                $this->horasRestantesDilatacion = $evaluacion->tiempo_dilatacion;
                $this->dispatch('evaluacion-reprobada-bloqueada', nota: round($notaFinal, 2), horas: $evaluacion->tiempo_dilatacion);
            } else {
                $restantes = $totalPermitidos - $this->intentosRealizados;
                $this->dispatch('evaluacion-reprobada', nota: round($notaFinal, 2), restantes: $restantes);
                // Recargamos para que pueda volver a intentarlo de inmediato si quiere
                $this->cargarEvaluacion();
            }
        }
    }

    /**
     * Llamado por el botón "Hecho" cuando el JavaScript valida que el estudiante consumió el contenido.
     */
    public function marcarCompletado($itemId)
    {
        $user = Auth::user();

        // Marcamos en la BD como completado
        \App\Models\CursoItemUser::updateOrCreate([
            'curso_item_id' => $itemId,
            'user_id' => $user->id,
        ], [
            'estado' => 'completado',
            'fecha_completado' => now(),
        ]);

        // Recargamos el estado general para recalcular el porcentaje y desbloquear el siguiente ítem
        $this->cargarProgreso();

        session()->flash('successItems', '¡Excelente! Has completado esta lección.');

        // Automáticamente intentamos avanzar al siguiente ítem disponible
        $this->avanzarSiguiente();
    }

    /**
     * Avanza al siguiente ítem cronológico.
     */
    public function avanzarSiguiente()
    {
        $siguienteItem = $this->obtenerSiguienteItem($this->itemActivoId);
        if ($siguienteItem) {
            $this->seleccionarItem($siguienteItem->id);
        } else {
            session()->flash('successItems', '¡Felicidades! Has terminado todos los contenidos de este curso.');
        }
    }

    /**
     * Busca el siguiente elemento en el arreglo unidimensional
     */
    private function obtenerSiguienteItem($actualItemId)
    {
        $currentIndex = array_search($actualItemId, $this->itemsOrdenados);
        if ($currentIndex !== false && $currentIndex < count($this->itemsOrdenados) - 1) {
            return CursoItem::find($this->itemsOrdenados[$currentIndex + 1]);
        }

        return null; // Era el último
    }

    public function render()
    {
        // Consultar los hilos del foro correspondientes a este ítem específico
        $hilosForo = collect();
        if ($this->itemActivo) {
            $hilosForo = \App\Models\CursoForoHilo::where('curso_item_id', $this->itemActivo->id)
                ->with('user')
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();
        }

        return view('livewire.cursos.campus-curso', [
            'progresoPorcentaje' => $this->progresoPorcentaje,
            'hilosForo' => $hilosForo,
            'evaluacionBloqueada' => $this->evaluacionBloqueada,
            'horasRestantesDilatacion' => $this->horasRestantesDilatacion,
            'intentosRealizados' => $this->intentosRealizados,
        ]);
    }
}
