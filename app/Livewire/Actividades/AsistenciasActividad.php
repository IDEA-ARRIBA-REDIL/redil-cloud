<?php

namespace App\Livewire\Actividades;

use App\Exports\AsistenciasActividadExport; // Usamos el nuevo modelo
use App\Models\Actividad;
use App\Models\ActividadAsistenciaInscripcion;
use App\Models\CrecimientoUsuario;
use App\Models\Inscripcion;
use App\Models\TareaConsolidacionUsuario;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class AsistenciasActividad extends Component
{
    public Actividad $actividad;

    public string $busqueda = '';

    // --- INICIO DE PROPIEDADES NUEVAS ---
    /**
     * Almacena la contraseña que el usuario ingresa en el modal.
     */
    public string $contrasenaIngresada = '';

    /**
     * Controla si el módulo está bloqueado o no.
     * Se inicializa en 'false'.
     */
    public bool $desbloqueado = false;

    /**
     * Indica si la actividad requiere una contraseña para acceder.
     */
    public bool $requiereContrasena = false;

    // --- FIN DE PROPIEDADES NUEVAS ---
    public array $asistenciasRegistradasHoy = []; // Renombrado para mayor claridad

    public int $totalDiasActividad = 1; // Nueva propiedad para la duración del evento

    /**
     * MÉTODO EDITADO:
     * Ahora calcula la duración total del evento en días.
     */
    public function mount(Actividad $actividad)
    {
        $this->actividad = $actividad;
        // --- INICIO DE LA LÓGICA DE CONTRASEÑA ---
        // Asumimos que el campo se llama 'contrasena_asistencia'
        if (empty($this->actividad->password)) {
            // No hay contraseña, el módulo es de libre acceso.
            $this->requiereContrasena = false;
            $this->desbloqueado = true;
        } else {
            // Sí hay contraseña, el módulo debe ser bloqueado.
            $this->requiereContrasena = true;
            $this->desbloqueado = false;
        }
        // --- FIN DE LA LÓGICA DE CONTRASEÑA ---

        // Calculamos la duración total del evento en días.
        $fechaInicio = Carbon::parse($this->actividad->fecha_inicio);
        $fechaFin = Carbon::parse($this->actividad->fecha_finalizacion);
        // diffInDays cuenta los días completos entre fechas, por eso sumamos 1.
        $this->totalDiasActividad = $fechaInicio->diffInDays($fechaFin) + 1;

        $this->cargarAsistenciasDeHoy();
    }

    public function validarContrasena()
    {
        // Asumimos que el campo se llama 'contrasena_asistencia'
        if ($this->contrasenaIngresada === $this->actividad->password) {
            // Contraseña correcta: desbloqueamos el módulo y cerramos el modal.
            $this->desbloqueado = true;
            $this->resetErrorBag('contrasenaIngresada');
            $this->dispatch('contrasena-correcta');
        } else {
            // Contraseña incorrecta: mostramos un error en el modal.
            $this->addError('contrasenaIngresada', 'Contraseña incorrecta.');
        }
    }

    /**
     * MÉTODO EDITADO:
     * El nombre ahora es más claro. Su función sigue siendo la misma:
     * cargar un mapa de las asistencias del DÍA DE HOY para el switch.
     */
    private function cargarAsistenciasDeHoy()
    {
        $this->asistenciasRegistradasHoy = ActividadAsistenciaInscripcion::where('actividad_id', $this->actividad->id)
            ->whereDate('fecha', Carbon::today())
            ->pluck('inscripcion_id')
            ->flip()
            ->toArray();
    }

    /**
     * MÉTODO RECONSTRUIDO:
     * Maneja el escaneo del nuevo QR basado en JSON.
     */
    #[On('qrCodeScanned')]
    public function handleSuccessfulScan($qrText): void
    {
        // 1. Decodificamos el JSON que viene del QR
        $datosQr = json_decode($qrText, true);

        // Validamos que el JSON sea válido y tenga las claves necesarias
        if (json_last_error() !== JSON_ERROR_NONE || ! isset($datosQr['tipo'], $datosQr['id'])) {
            $this->dispatch('showAlert', [
                'title' => 'QR Inválido',
                'text' => 'El formato del código QR no es correcto.',
                'icon' => 'error',
                'interactive' => true,
            ]);

            return;
        }

        $inscripcion = null;

        // 2. Usamos un switch para manejar los diferentes tipos de QR
        switch ($datosQr['tipo']) {
            case 'verificar_asistencia_inscripcion_usuario':
                // Buscamos la inscripción del usuario para ESTA actividad
                $inscripcion = Inscripcion::with(['user', 'categoriaActividad.actividad'])->find($datosQr['id']);
                break;

            case 'verificar_asistencia_inscripcion_invitado':
                $inscripcionId = $datosQr['id'];
                $inscripcion = Inscripcion::with(['categoriaActividad.actividad'])->find($inscripcionId);
                // Verificamos que la inscripción encontrada pertenezca a la actividad actual
                if ($inscripcion && $inscripcion->categoriaActividad && $inscripcion->categoriaActividad->actividad_id != $this->actividad->id) {
                    $inscripcion = null; // No es válida si no es de esta actividad
                }
                break;

            default:
                $this->dispatch('showAlert', [
                    'title' => 'Tipo de QR Desconocido',
                    'text' => 'Este código QR no es para registro de asistencia en actividades.',
                    'icon' => 'warning',
                    'interactive' => true,
                ]);

                return;
        }

        // 3. Validamos si se encontró una inscripción válida
        if (! isset($inscripcion->id)) {
            $this->dispatch('showAlert', [
                'title' => 'QR no registrado',
                'html' => 'Este código QR no está registrado en nuestra plataforma. Por favor acércate al personal administrativo.',
                'icon' => 'error',
                'interactive' => true,
            ]);
        } elseif ($inscripcion->categoriaActividad && $inscripcion->categoriaActividad->actividad_id != $this->actividad->id) {
            $inscripcionReal = Inscripcion::with('categoriaActividad.actividad')->find($datosQr['id']);
            $nombreActividadReal = $inscripcionReal->categoriaActividad->actividad->nombre ?? 'otra actividad';
            $detalles = $inscripcionReal->categoriaActividad->actividad->detalles_finales ?? '';
            $this->dispatch('showAlert', [
                'title' => 'QR de Otra Actividad',
                'html' => 'Este QR pertenece a la actividad: <strong>'.e($nombreActividadReal).'</strong>'.($detalles ? '<br><small class="text-muted">'.e($detalles).'</small>' : ''),
                'icon' => 'warning',
                'interactive' => true,
            ]);
        } else {
            // 4. Lógica de registro de asistencia (una por día)
            $this->registrarAsistencia($inscripcion);
        }
    }

    /**
     * MÉTODO AJUSTADO:
     * Ahora carga los IDs de INSCRIPCIÓN que ya tienen asistencia hoy.
     */
    private function cargarAsistencias(): void
    {
        $this->asistenciasRegistradas = ActividadAsistenciaInscripcion::where('actividad_id', $this->actividad->id)
            ->whereDate('fecha', Carbon::today())
            ->pluck('inscripcion_id')
            ->flip()
            ->toArray();
    }

    /**
     * MÉTODO AJUSTADO:
     * Ahora opera con el ID de la inscripción en lugar del ID del usuario.
     */
    public function toggleAsistencia($inscripcionId): void
    {
        if (isset($this->asistenciasRegistradasHoy[$inscripcionId])) {
            $this->eliminarAsistencia($inscripcionId);
        } else {
            $inscripcion = Inscripcion::find($inscripcionId);
            if ($inscripcion) {
                $this->registrarAsistencia($inscripcion);
            }
        }
    }

    /**
     * MÉTODO RECONSTRUIDO:
     * Registra la asistencia para una inscripción específica, solo si no existe una para el día de hoy.
     */
    private function registrarAsistencia(Inscripcion $inscripcion): void
    {
        if (! $this->actividad->activa) {
            $this->dispatch('showAlert', [
                'title' => 'Actividad Inactiva',
                'text' => 'Esta actividad se encuentra inactiva o finalizada.',
                'icon' => 'warning',
                'interactive' => true,
            ]);

            return;
        }

        $nombreAsistente = $inscripcion->user ? $inscripcion->user->nombre(3) : ($inscripcion->nombre_inscrito ?? 'Invitado');

        // Verificamos si ya tenía asistencia registrada el día de hoy
        $yaRegistradoHoy = ActividadAsistenciaInscripcion::where('inscripcion_id', $inscripcion->id)
            ->whereDate('fecha', Carbon::today())
            ->exists();

        if ($yaRegistradoHoy) {
            $this->cargarAsistenciasDeHoy();
            $this->dispatch('showAlert', [
                'title' => '¡Ya Registrado!',
                'text' => $nombreAsistente.' ya tiene asistencia registrada el día de hoy.',
                'icon' => 'info',
                'interactive' => false,
            ]);

            return;
        }

        // --- Parte 1: Registro de la asistencia diaria ---
        ActividadAsistenciaInscripcion::firstOrCreate(
            [
                'inscripcion_id' => $inscripcion->id,
                'fecha' => Carbon::today()->toDateString(),
            ],
            [
                'actividad_id' => $this->actividad->id,
                'user_id' => $inscripcion->user_id,
                'compra_id' => $inscripcion->compra_id,
            ]
        );

        // --- Parte 2: Lógica para Culminar Procesos de Crecimiento (Sistema Dinámico) ---
        $procesosACulminar = $this->actividad->procesosCulminados;
        if ($procesosACulminar->isNotEmpty() && $inscripcion->user_id) {
            $totalAsistencias = ActividadAsistenciaInscripcion::where('inscripcion_id', $inscripcion->id)->count();
            if ($totalAsistencias === 1) {
                foreach ($procesosACulminar as $proceso) {
                    $estadoAsignar = $proceso->pivot->estado_paso_crecimiento_usuario_id ?? $proceso->pivot->estado;
                    CrecimientoUsuario::procesarPaso(
                        userId: $inscripcion->user_id,
                        pasoCrecimientoId: $proceso->id,
                        estadoObjetivoId: $estadoAsignar,
                        detalle: 'Asistencia '.$this->actividad->nombre,
                        fecha: Carbon::today()
                    );
                }
            }
        }

        // --- Parte 2.5: Lógica para Cambio de Tipo Usuario y Roles ---
        if ($this->actividad->tipo_usuario_objetivo_id && $inscripcion->user_id) {
            $totalAsistencias = ActividadAsistenciaInscripcion::where('inscripcion_id', $inscripcion->id)->count();
            if ($totalAsistencias === 1) {
                $usuario = \App\Models\User::find($inscripcion->user_id);
                if ($usuario) {
                    $usuario->promoverTipoUsuario($this->actividad->tipo_usuario_objetivo_id);
                }
            }
        }

        // --- Parte 3: Lógica para Culminar Tareas de Consolidación ---
        $tareasACulminar = $this->actividad->restriccion_por_categoria && $inscripcion->categoriaActividad
            ? $inscripcion->categoriaActividad->tareasCulminadas
            : $this->actividad->tareasCulminadas;

        if ($tareasACulminar->isNotEmpty() && $inscripcion->user_id) {
            $totalAsistencias = ActividadAsistenciaInscripcion::where('inscripcion_id', $inscripcion->id)->count();
            if ($totalAsistencias === 1) {
                foreach ($tareasACulminar as $tarea) {
                    TareaConsolidacionUsuario::procesarTarea(
                        userId: $inscripcion->user_id,
                        tareaConsolidacionId: $tarea->tarea_consolidacion_id,
                        estadoObjetivoId: $tarea->estado_tarea_consolidacion_id,
                        observaciones: 'Asistencia confirmada en actividad: '.$this->actividad->nombre,
                        fecha: Carbon::today()
                    );
                }
            }
        }

        // Simplemente actualizamos el array que usa la vista para los botones
        $this->cargarAsistenciasDeHoy();

        // =========================================================================
        // NUEVA LÓGICA: Verificar y mostrar respuestas de formulario con 'visible_asistencia'
        // =========================================================================
        $this->_verificarYMostrarRespuestasAsistencia($inscripcion);
    }

    /**
     * Muestra una alerta con las respuestas del formulario si hay elementos visibles en asistencia.
     */
    private function _verificarYMostrarRespuestasAsistencia(Inscripcion $inscripcion): void
    {
        $nombreAsistente = $inscripcion->user ? $inscripcion->user->nombre(3) : ($inscripcion->nombre_inscrito ?? 'Invitado');

        // 1. Obtener elementos de formulario con 'visible_asistencia' activado
        $elementosVisibles = $this->actividad->elementos()
            ->where('visible_asistencia', true)
            ->orderBy('orden', 'asc')
            ->get();

        if ($elementosVisibles->isEmpty()) {
            // Si no hay preguntas visibles, mostramos la alerta estándar de éxito con el nombre del participante
            $this->dispatch('showAlert', [
                'title' => '¡Asistencia Registrada!',
                'text' => 'Se registró la asistencia de: '.$nombreAsistente,
                'icon' => 'success',
                'interactive' => false,
            ]);

            return;
        }

        // 2. Obtener las respuestas asociadas a la compra de esta inscripción
        // Asumimos que las respuestas están ligadas a la compra
        $respuestas = \App\Models\RespuestaElementoFormulario::where('compra_id', $inscripcion->compra_id)
            ->whereIn('elemento_formulario_actividad_id', $elementosVisibles->pluck('id'))
            ->get()
            ->keyBy('elemento_formulario_actividad_id');

        // 3. Construir el mensaje HTML
        $htmlMensaje = '<div class="text-start">';
        $htmlMensaje .= '<p class="fw-bold mb-2 text-primary fs-5"><i class="ti ti-check me-1"></i>'.e($nombreAsistente).'</p>';

        foreach ($elementosVisibles as $elemento) {
            $respuesta = $respuestas->get($elemento->id);
            $valorTexto = $this->_obtenerTextoRespuesta($respuesta, $elemento);

            $htmlMensaje .= '<div class="mb-2">';
            $htmlMensaje .= '<strong class="d-block text-black">'.e($elemento->titulo).'</strong>';
            $htmlMensaje .= '<span class="text-black">'.$valorTexto.'</span>';
            $htmlMensaje .= '</div>';
            $htmlMensaje .= '<hr class="my-1 border-light">';
        }

        $htmlMensaje .= '</div>';

        // 4. Disparar SweetAlert personalizado
        $this->dispatch('showFormAlert', [
            'title' => 'Información de Asistencia',
            'html' => $htmlMensaje,
            'icon' => 'info',
            'interactive' => true, // Importante para que no se cierre solo
            'confirmButtonText' => 'Aceptar y continuar',
        ]);
    }

    /**
     * Helper para formatear la respuesta (adaptado de DashboardFormularios)
     */
    private function _obtenerTextoRespuesta($respuesta, $elemento)
    {
        $configuracion = \App\Models\Configuracion::find(1);
        if (! $respuesta) {
            return '<span class="text-danger fst-italic">Sin respuesta</span>';
        }

        switch ($elemento->tipoElemento->clase) {
            case 'corta':
                return e($respuesta->respuesta_texto_corto);
            case 'larga':
                return nl2br(e($respuesta->respuesta_texto_largo));
            case 'si_no':
                return $respuesta->respuesta_si_no == 1 ? 'Sí' : 'No';
            case 'unica_respuesta':
                // Idealmente buscar el texto de la opción si está disponible, sino mostrar el valor directo
                return e($respuesta->respuesta_unica);
            case 'multiple_respuesta':
                return e($respuesta->respuesta_multiple);
            case 'fecha':
                return e($respuesta->respuesta_fecha);
            case 'numero':
                return e($respuesta->respuesta_numero);
            case 'moneda':
                return '$'.number_format($respuesta->respuesta_moneda ?? 0, 2);
            case 'archivo':
                return $respuesta->url_archivo
                    ? '<a href="'.tenant_asset('archivos/actividades/'.$respuesta->url_archivo).'" target="_blank"><i class="fas fa-paperclip"></i> Ver Archivo</a>'
                    : 'Sin archivo';
            case 'imagen':
                return $respuesta->url_foto
                    ? '<a href="'.tenant_asset('img/actividades/respuesta-formularios/'.$respuesta->url_foto).'" target="_blank"><i class="fas fa-image"></i> Ver Imagen</a>'
                    : 'Sin imagen';
            default:
                return 'Dato registrado';
        }
    }

    public function exportarAsistencias()
    {
        $fileName = 'asistencias-'.Str::slug($this->actividad->nombre).'.xlsx';

        return Excel::download(new AsistenciasActividadExport($this->actividad), $fileName);
    }

    /**
     * MÉTODO AJUSTADO:
     * Elimina la asistencia de una inscripción para el día de hoy.
     */
    private function eliminarAsistencia(string $inscripcionId)
    {
        if ($this->actividad->activa) {
            ActividadAsistenciaInscripcion::where('inscripcion_id', $inscripcionId)
                ->whereDate('fecha', Carbon::today())
                ->delete();

            // --- INICIO DE LA CORRECCIÓN ---
            // $this->mount($this->actividad); // <-- ELIMINADO
            // $this->cargarAsistencias(); // <-- ELIMINADO (y era un typo)

            // Simplemente actualizamos el array que usa la vista para los botones
            $this->cargarAsistenciasDeHoy();
            // --- FIN DE LA CORRECCIÓN ---
        }
    }

    /**
     * MÉTODO EDITADO:
     * Ahora la consulta es mucho más potente. Carga el conteo de asistencias
     * y las relaciones necesarias para mostrar tanto usuarios como invitados.
     */
    public function render()
    {
        // 1. Inicializar $inscritos como una colección vacía.
        // Si el módulo no está desbloqueado, esto es lo que se enviará a la vista.
        $inscritos = collect();

        // 2. Solo ejecutar la lógica de consulta si el módulo está desbloqueado.
        if ($this->desbloqueado) {

            // 3. Iniciar la consulta base
            // Usamos la relación 'inscripciones()' definida en el modelo Actividad.
            $query = $this->actividad->inscripciones()
                // withCount es la clave: cuenta las asistencias de forma eficiente.
                ->withCount('asistencias')
                // Precargamos las relaciones para evitar consultas N+1.
                ->with(['user', 'compra', 'inscripcionPrincipal.user', 'inscripcionPrincipal.compra']);

            // 4. Aplicar la lógica de búsqueda si el término no está vacío
            $terminoBusquedaLimpio = trim($this->busqueda);

            if (! empty($terminoBusquedaLimpio)) {
                $terminoBusqueda = '%'.$terminoBusquedaLimpio.'%';

                $query->where(function ($q) use ($terminoBusqueda) {

                    // A. Buscar por el correo propio de la inscripción
                    $q->where('email', 'ilike', $terminoBusqueda)

                    // B. Buscar en usuarios registrados (tabla 'users')
                        ->orWhereHas('user', function ($userQuery) use ($terminoBusqueda) {
                            $userQuery->where('identificacion', 'like', $terminoBusqueda)
                                ->orWhere('email', 'ilike', $terminoBusqueda)
                                ->orWhere(DB::raw("CONCAT(primer_nombre, ' ', primer_apellido)"), 'ilike', $terminoBusqueda);
                        })

                        // C. Buscar en invitados por 'nombre_inscrito' (tabla 'inscripciones')
                        ->orWhere(function ($guestQuery) use ($terminoBusqueda) {
                            $guestQuery->whereNull('user_id') // Asegurarnos de que es un invitado
                                ->where('nombre_inscrito', 'ilike', $terminoBusqueda);
                        })

                        // D. Buscar por datos de la compra (tabla 'compras')
                        // (Esto sirve como respaldo si 'nombre_inscrito' estuviera vacío)
                        ->orWhereHas('compra', function ($compraQuery) use ($terminoBusqueda) {
                            $compraQuery->where('identificacion_comprador', 'like', $terminoBusqueda)
                                ->orWhere('email_comprador', 'ilike', $terminoBusqueda)
                                ->orWhere('nombre_completo_comprador', 'ilike', $terminoBusqueda);
                        });
                });
            }

            // 5. Obtener los resultados finales
            $inscritos = $query->get();
        }

        // 6. Retornar la vista con los datos
        // (La colección $inscritos estará vacía si no está desbloqueado)
        return view('livewire.actividades.asistencias-actividad', [
            'inscritos' => $inscritos,
        ]);
    }
}
