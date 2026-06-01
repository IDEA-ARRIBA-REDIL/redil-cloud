<?php

namespace App\Livewire\Cursos;

use App\Models\Curso;
use App\Models\CursoItem;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class CampusCurso extends Component
{
    /**
     * OPTIMIZACIÓN (Fix #10):
     * Guardamos solo el ID del curso como propiedad pública en lugar del objeto Eloquent completo.
     * Livewire serializa todas las propiedades públicas en el "snapshot" que viaja entre servidor
     * y cliente en cada request. Un Curso con módulos/items cargados puede pesar varios KB de JSON.
     * Almacenar solo el ID reduce ese payload a un número entero.
     * El objeto se hidrata bajo demanda con getCurso() usando el ID.
     */
    public $cursoData = null; // Cache privado del objeto Curso en memoria durante el request

    public $cursoId;

    public $itemActivoId = null;

    public $itemActivo = null;

    // Hilos del foro del ítem activo — se actualiza en seleccionarItem(), no en cada render()
    public $hilosForoItem = null;

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

    public $mostrarRespuestas = false;

    public $puedeVerRespuestasActual = false;

    #[Computed]
    public function evaluacionEstaCompleta(): bool
    {
        if (empty($this->preguntasEvaluacion)) {
            return false;
        }

        foreach ($this->preguntasEvaluacion as $pregunta) {
            if (empty($this->respuestasEvaluacion[$pregunta->id])) {
                return false;
            }
        }

        return true;
    }

    public function mount($slug)
    {
        // Cargamos el curso y lo guardamos en el cache privado del request
        $this->cursoData = Curso::where('slug', $slug)
            ->with(['modulos.items.tipo', 'rolesRestringidos', 'pasosRequisito', 'tareasRequisito', 'equipo.user'])
            ->firstOrFail();

        $this->cursoId = $this->cursoData->id;

        // Validar si el usuario está inscrito
        $user = Auth::user();
        if (! $this->cursoData->usuarios()->where('user_id', $user->id)->exists()) {
            abort(403, 'No estás inscrito en este curso.');
        }

        // Cargar y procesar el progreso general para bloquear/desbloquear items
        $this->cargarProgreso();
    }

    /**
     * OPTIMIZACIÓN (Fix #10):
     * Helper privado que devuelve el objeto Curso con sus relaciones cargadas.
     * Durante el mismo request PHP usa el cache en $cursoData (memoria).
     * Si el objeto no está en memoria (nuevo request Livewire), lo recarga desde BD usando
     * el ID que sí se persiste en el snapshot de Livewire.
     */
    private function getCurso(): Curso
    {
        if (! $this->cursoData) {
            $this->cursoData = Curso::with(['modulos.items.tipo', 'rolesRestringidos', 'pasosRequisito', 'tareasRequisito', 'equipo.user'])
                ->findOrFail($this->cursoId);
        }

        return $this->cursoData;
    }

    /**
     * Calcula el progreso actual del usuario basándose en la tabla curso_item_user.
     * Define qué ítems están bloqueados, iniciados y completados.
     */
    public function cargarProgreso()
    {
        $user = Auth::user();
        $curso = $this->getCurso();

        // Obtener todos los IDs de los ítems del curso en orden (Módulo -> Item)
        // Usamos getCurso() para que la colección de módulos/items venga de memoria (no genera queries)
        $this->itemsOrdenados = [];
        foreach ($curso->modulos as $modulo) {
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

        /*
         * OPTIMIZACIÓN (Fix #5):
         * Antes los hilos del foro se consultaban en render(), que se ejecuta en CADA acción de Livewire
         * (cada click, cada evaluación, cada avance). Eso significaba una query a foro_hilos por cada
         * interacción del estudiante en el campus.
         *
         * Ahora la query solo corre aquí, cuando el usuario realmente cambia de ítem. Si el estudiante
         * navega entre opciones de una evaluación o marca como completado, el foro no se re-consulta.
         */
        $this->hilosForoItem = \App\Models\CursoForoHilo::where('curso_item_id', $itemId)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Registramos en BD que el usuario "inició" la visualización de este ítem (si no lo había hecho ya)
        $user = Auth::user();
        \App\Models\CursoItemUser::firstOrCreate([
            'curso_item_id' => $itemId,
            'user_id' => $user->id,
        ], [
            'estado' => 'iniciado',
        ]);

        // Si el ítem es una evaluación, disparamos el flujo de carga
        if (in_array($this->itemActivo->tipo->codigo, ['evaluacion', 'quiz', 'evaluacion_final']) && $this->itemActivo->itemable) {
            $this->cargarEvaluacion();
        }

        // Emitimos un evento a Alpine/JavaScript para que reinicie los oyentes de progreso (scroll, video duration)
        $this->dispatch('item-cambiado', itemId: $itemId);
    }

    /**
     * Verifica si todas las lecciones de un módulo han sido completadas.
     */
    public function isModuloCompletado($modulo): bool
    {
        if (! $modulo || ! $modulo->items) {
            return false;
        }

        foreach ($modulo->items as $item) {
            $estado = $this->itemsProgreso[$item->id] ?? 'bloqueado';
            if ($estado !== 'completado') {
                return false;
            }
        }

        return true;
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
        $this->evaluacionResultado = $intentos->first();

        $this->evaluacionBloqueada = false;
        $this->horasRestantesDilatacion = 0;
        $this->puedeVerRespuestasActual = false;

        // Lógica para determinar si puede ver respuestas (basada en el último intento)
        $totalPermitidos = 1 + ($evaluacion->cantidad_repeticiones ?? 0);
        $sinMasIntentos = $this->intentosRealizados >= $totalPermitidos;

        if ($this->evaluacionResultado) {
            $haRevisado = $this->evaluacionResultado->respuestas_json['__revisado'] ?? false;

            if (! $haRevisado) {
                if ($this->evaluacionResultado->aprobado && $evaluacion->mostrar_respuestas_si_aprueba) {
                    $this->puedeVerRespuestasActual = true;
                } elseif (! $this->evaluacionResultado->aprobado && $sinMasIntentos && $evaluacion->mostrar_respuestas_si_pierde) {
                    $this->puedeVerRespuestasActual = true;
                }
            }
        }

        // 3. Cargar preguntas (siempre las cargamos para que el modal de revisión pueda usarlas)
        $preguntas = $evaluacion->preguntas()->with('opciones')->get();
        $this->preguntasEvaluacion = $preguntas->shuffle()->values()->all();
        $this->preguntaActualIndex = 0;
        $this->respuestasEvaluacion = [];

        foreach ($this->preguntasEvaluacion as $pregunta) {
            $this->respuestasEvaluacion[$pregunta->id] = [];
        }

        // 0. Si ya está aprobado o completado, omitimos la lógica de bloqueos y tiempos de examen activos
        if (isset($this->itemsProgreso[$this->itemActivo->id]) && $this->itemsProgreso[$this->itemActivo->id] === 'completado') {
            return;
        }

        // 2. Lógica de Dilatación e Intentos
        $totalPermitidos = 1 + ($evaluacion->cantidad_repeticiones ?? 0);

        if ($this->intentosRealizados > 0 && $this->intentosRealizados >= $totalPermitidos) {
            if ($this->evaluacionResultado) {
                // Ya cumplió todos los intentos. Verificar si ya pasó el tiempo de dilatación.
                $fechaUltimo = $this->evaluacionResultado->created_at;
                $horasTranscurridas = $fechaUltimo->diffInHours(now());

                if ($horasTranscurridas < $evaluacion->tiempo_dilatacion) {
                    $this->evaluacionBloqueada = true;
                    $this->horasRestantesDilatacion = $evaluacion->tiempo_dilatacion - $horasTranscurridas;

                    return; // Está bloqueado
                } else {
                    // Pasó el tiempo de dilatación. "Borramos" registros anteriores para reiniciar ciclo
                    \App\Models\CursoEvaluacionResultado::where('user_id', $user->id)
                        ->where('curso_item_id', $this->itemActivo->id)
                        ->delete();
                    $this->intentosRealizados = 0;
                }
            }
        }

        $sessionKey = "eval_start_{$user->id}_{$this->itemActivo->id}";
        if (! session()->has($sessionKey)) {
            session()->put($sessionKey, now()->timestamp);
        }
        $this->inicioExamen = session($sessionKey);
        $this->inicioExamen = session($sessionKey);
        $this->mostrarRespuestas = false;
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
        if (! $this->evaluacionEstaCompleta) {
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
        if ($totalPreguntas === 0) {
            return;
        }

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

        // Lógica de visualización de respuestas (Configuración)
        $totalPermitidos = 1 + ($evaluacion->cantidad_repeticiones ?? 0);
        $sinMasIntentos = $intentoActual >= $totalPermitidos;

        if ($aprobado && $evaluacion->mostrar_respuestas_si_aprueba) {
            $this->puedeVerRespuestasActual = true;
        } elseif (! $aprobado && $sinMasIntentos && $evaluacion->mostrar_respuestas_si_pierde) {
            $this->puedeVerRespuestasActual = true;
        }

        if ($aprobado) {
            $this->dispatch('evaluacion-aprobada', nota: round($notaFinal, 2), puedeVerRespuestas: $this->puedeVerRespuestasActual);
            $this->marcarCompletado($this->itemActivo->id, false);
        } else {
            $this->intentosRealizados = $intentoActual;

            if ($sinMasIntentos) {
                $this->preguntasEvaluacion = [];
                $this->evaluacionBloqueada = true;
                $this->horasRestantesDilatacion = $evaluacion->tiempo_dilatacion;

                // Si es Quiz y perdió todos los intentos, lo dejamos continuar marcándolo como completado
                if ($this->itemActivo->tipo->codigo === 'quiz') {
                    $this->marcarCompletado($this->itemActivo->id, false); // false para no avanzar automáticamente aún si queremos mostrar respuestas
                    $this->dispatch('evaluacion-reprobada-finalizar-quiz', nota: round($notaFinal, 2), puedeVerRespuestas: $this->puedeVerRespuestasActual);
                } else {
                    // Evaluación Final u otro: Bloqueo total y notificación
                    $this->dispatch('evaluacion-reprobada-bloqueada',
                        nota: round($notaFinal, 2),
                        horas: $evaluacion->tiempo_dilatacion,
                        puedeVerRespuestas: $this->puedeVerRespuestasActual,
                        esFinal: $this->itemActivo->tipo->codigo === 'evaluacion_final'
                    );
                }
            } else {
                $restantes = $totalPermitidos - $this->intentosRealizados;
                $this->dispatch('evaluacion-reprobada', nota: round($notaFinal, 2), restantes: $restantes);
                // Recargamos para que pueda volver a intentarlo de inmediato si quiere
                $this->cargarEvaluacion();
            }
        }
    }

    public function verRespuestas(): void
    {
        if ($this->puedeVerRespuestasActual) {
            $this->mostrarRespuestas = true;
        }
    }

    public function cerrarRespuestas(): void
    {
        $this->mostrarRespuestas = false;

        // Marcar como revisado para que no pueda volver a entrar (salvaguarda de una sola vez)
        if ($this->evaluacionResultado) {
            $respuestas = $this->evaluacionResultado->respuestas_json;
            if (! isset($respuestas['__revisado'])) {
                $respuestas['__revisado'] = true;
                $this->evaluacionResultado->update(['respuestas_json' => $respuestas]);
                $this->puedeVerRespuestasActual = false; // Ocultar botón inmediatamente
            }
        }

        // Si era una evaluación final y perdió sin más intentos, redireccionar al catálogo al cerrar respuestas
        $evaluacion = $this->evaluacionConfig;
        $totalPermitidos = 1 + ($evaluacion->cantidad_repeticiones ?? 0);
        $sinMasIntentos = $this->intentosRealizados >= $totalPermitidos;

        if ($this->itemActivo->tipo->codigo === 'evaluacion_final' && ! $this->evaluacionResultado->aprobado && $sinMasIntentos) {
            $this->redirect(route('cursos.catalogo'), navigate: true);
        }
    }

    /**
     * Llamado por el botón "Hecho" cuando el JavaScript valida que el estudiante consumió el contenido.
     */
    public function marcarCompletado($itemId, $avanzar = true)
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

        if ($avanzar) {
            session()->flash('successItems', '¡Excelente! Has completado esta lección.');
            // Automáticamente intentamos avanzar al siguiente ítem disponible
            $this->avanzarSiguiente();
        }
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

        if ($currentIndex === false || $currentIndex >= count($this->itemsOrdenados) - 1) {
            return null; // Era el último o no se encontró
        }

        $siguienteId = $this->itemsOrdenados[$currentIndex + 1];

        /*
         * OPTIMIZACIÓN (Fix #3):
         * Antes se hacía CursoItem::find($id) aquí, que lanza una query SELECT a la BD
         * cada vez que el estudiante avanza de lección.
         *
         * El curso y todos sus módulos/items ya están en memoria desde el mount().
         * Buscamos el ítem directamente en esa colección usando flatMap — sin tocar la BD.
         */
        $curso = $this->getCurso();
        $itemEnMemoria = $curso->modulos
            ->flatMap(fn ($modulo) => $modulo->items)
            ->firstWhere('id', $siguienteId);

        // Si por alguna razón no está en la colección cargada (ítem muy nuevo), hacemos fallback a BD
        return $itemEnMemoria ?? CursoItem::with('tipo', 'itemable')->find($siguienteId);
    }

    public function render()
    {
        /*
         * OPTIMIZACIÓN (Fix #5):
         * La consulta de hilos del foro se movió a seleccionarItem().
         * render() se ejecuta en CADA interacción de Livewire; seleccionarItem() solo cuando
         * el estudiante cambia de lección. Esto elimina queries innecesarias al foro.
         *
         * $hilosForoItem es una propiedad pública del componente actualizada en seleccionarItem().
         */
        return view('livewire.cursos.campus-curso', [
            'progresoPorcentaje' => $this->progresoPorcentaje,
            'hilosForo' => $this->hilosForoItem ?? collect(),
            'evaluacionBloqueada' => $this->evaluacionBloqueada,
            'horasRestantesDilatacion' => $this->horasRestantesDilatacion,
            'intentosRealizados' => $this->intentosRealizados,
        ]);
    }
}
