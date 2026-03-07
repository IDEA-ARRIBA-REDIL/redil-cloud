<?php

namespace App\Livewire\Actividades;

use App\Models\ActividadAsistenciaInscripcion; // Usamos el nuevo modelo
use App\Models\Inscripcion;
use App\Models\Actividad;
use App\Models\CrecimientoUsuario;
use App\Models\TareaConsolidacionUsuario;
use App\Models\HistorialTareaConsolidacionUsuario;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use Carbon\Carbon;

use App\Exports\AsistenciasActividadExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;

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
    public function handleSuccessfulScan($qrText)
    {
        // 1. Decodificamos el JSON que viene del QR
        $datosQr = json_decode($qrText, true);

        // Validamos que el JSON sea válido y tenga las claves necesarias
        if (json_last_error() !== JSON_ERROR_NONE || !isset($datosQr['tipo'], $datosQr['id'])) {
            $this->dispatch('showAlert', ['title' => 'QR Inválido', 'text' => 'El formato del código QR no es correcto.', 'icon' => 'error']);
            return;
        }

        $inscripcion = null;

        // 2. Usamos un switch para manejar los diferentes tipos de QR
        switch ($datosQr['tipo']) {
            case 'verificar_asistencia_inscripcion_usuario':

                // Buscamos la inscripción del usuario para ESTA actividad
                $inscripcion = Inscripcion::find($datosQr['id']);

                break;

            case 'verificar_asistencia_inscripcion_invitado':
                $inscripcionId = $datosQr['id'];
                $inscripcion = Inscripcion::find($inscripcionId);
                // Verificamos que la inscripción encontrada pertenezca a la actividad actual
                if ($inscripcion && $inscripcion->categoriaActividad->actividad_id != $this->actividad->id) {
                    $inscripcion = null; // No es válida si no es de esta actividad
                }
                break;

            default:
                $this->dispatch('showAlert', ['title' => 'Tipo de QR Desconocido', 'text' => 'Este código QR no es para registro de asistencia.', 'icon' => 'warning']);
                return;
        }

        // 3. Validamos si se encontró una inscripción válida
        if (!isset($inscripcion->id)) {
            $this->dispatch('showIncorrectQrModal', [
                'title' => 'QR no registrado',
                'text'  => '<strong>Este codigo QR no esta registrado en nuestra plataforma, acercate a un personal administrativo para obtener mas información </strong>'
            ]);
        } elseif ($inscripcion->categoriaActividad->actividad->id != $this->actividad->id) {
            $inscripcionReal = Inscripcion::find($datosQr['id']);
            $this->dispatch('showIncorrectQrModal', [
                'title' => 'QR de Otra Actividad',
                'text'  => 'Este QR pertenece a la actividad: <strong>' . $inscripcionReal->categoriaActividad->actividad->nombre . ' sigue estas instrucciones:' . $inscripcionReal->categoriaActividad->actividad->detalles_finales . '</strong>'
            ]);
            /*
            $this->mount($this->actividad);
            $this->cargarAsistencias(); */
            // Recargamos el estado

        } else {
            // 4. Lógica de registro de asistencia (una por día)
            $this->registrarAsistencia($inscripcion);

            // 5. La alerta se maneja dentro de registrarAsistencia para verificar respuestas de formulario
            // $this->dispatch('showAlert', ['title' => '¡Asistencia Registrada!', 'text' => 'La asistencia se ha guardado correctamente.', 'icon' => 'success']);
        }
    }



    /**
     * MÉTODO AJUSTADO:
     * Ahora carga los IDs de INSCRIPCIÓN que ya tienen asistencia hoy.
     */
    private function cargarAsistencias()
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
    public function toggleAsistencia($inscripcionId)
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
    private function registrarAsistencia(Inscripcion $inscripcion)
    {
        if (!$this->actividad->activa) {
            return;
        }

        // --- Parte 1: Registro de la asistencia diaria (sin cambios) ---
        ActividadAsistenciaInscripcion::firstOrCreate(
            [
                'inscripcion_id' => $inscripcion->id,
                'fecha' => Carbon::today()->toDateString(),
            ],
            [
                'actividad_id'   => $this->actividad->id,
                'user_id'        => $inscripcion->user_id,
                'compra_id'      => $inscripcion->compra_id,
            ]
        );

        // --- Parte 2: Lógica para Culminar Procesos de Crecimiento (Sistema Dinámico) ---
        $procesosACulminar = $this->actividad->procesosCulminados;
        if ($procesosACulminar->isNotEmpty()) {
            if ($inscripcion->user_id) {
                $totalAsistencias = ActividadAsistenciaInscripcion::where('inscripcion_id', $inscripcion->id)->count();
                if ($totalAsistencias === 1) {
                    foreach ($procesosACulminar as $proceso) {
                        // Usar el nuevo campo FK dinámico si existe, sino usar el antiguo
                        $estadoAsignar = $proceso->pivot->estado_paso_crecimiento_usuario_id ?? $proceso->pivot->estado;
                        
                        CrecimientoUsuario::firstOrCreate(
                            [
                                'user_id' => $inscripcion->user_id,
                                'paso_crecimiento_id' => $proceso->id,
                            ],
                            [
                                'estado_id' => $estadoAsignar,
                                'fecha'     => Carbon::today(),
                                'detalle'   => 'Asistencia ' . $this->actividad->nombre,
                            ]
                        );
                    }
                }
            }
        }

        // --- Parte 2.5: Lógica para Cambio de Tipo Usuario y Roles (NUEVO) ---
        if ($this->actividad->tipo_usuario_objetivo_id && $inscripcion->user_id) {
            
            // Log de depuración
            Log::info("AsistenciaActividad: Iniciando lógica de cambio de tipo usuario/rol. Inscripcion ID: {$inscripcion->id}, User ID: {$inscripcion->user_id}");

            $totalAsistencias = ActividadAsistenciaInscripcion::where('inscripcion_id', $inscripcion->id)->count();
            Log::info("AsistenciaActividad: Total de asistencias encontradas: {$totalAsistencias}");
            
            // Solo ejecutar en la PRIMERA asistencia
            if ($totalAsistencias === 1) {
                $usuario = \App\Models\User::find($inscripcion->user_id);
                $tipoUsuarioObjetivo = $this->actividad->tipoUsuarioObjetivo;
                $tipoUsuarioActual = $usuario->tipoUsuario;

                // 1. Validar Jerarquía por Puntaje
                $puntajeActual = $tipoUsuarioActual ? $tipoUsuarioActual->puntaje : 0;
                $puntajeObjetivo = $tipoUsuarioObjetivo ? $tipoUsuarioObjetivo->puntaje : 0;

                Log::info("AsistenciaActividad: Comparando puntajes. Actual: {$puntajeActual}, Objetivo: {$puntajeObjetivo}");

                // SI el usuario tiene mayor rango (mayor puntaje) que el objetivo, NO hacemos nada.
                // SI es igual o menor, procedemos al cambio (actualización o ascenso).
                if ($puntajeActual <= $puntajeObjetivo) {
                    Log::info("AsistenciaActividad: El puntaje objetivo es mayor o igual. Procediendo al cambio de Tipo Usuario.");
                    
                    // 2. Actualizar Tipo de Usuario
                    $usuario->update(['tipo_usuario_id' => $tipoUsuarioObjetivo->id]);
                    Log::info("AsistenciaActividad: Tipo de usuario actualizado a ID: {$tipoUsuarioObjetivo->id}");

                    // 3. Gestión de Roles
                    // "Desactivarroles dependientes, mantener independientes, activar nuevo rol"
                    
                    $nuevoRolId = $tipoUsuarioObjetivo->id_rol_dependiente;
                    Log::info("AsistenciaActividad: ID del nuevo rol dependiente: " . ($nuevoRolId ?? 'NULO'));

                    if ($nuevoRolId) {
                        DB::transaction(function () use ($usuario, $nuevoRolId) {
                            Log::info("AsistenciaActividad: Iniciando transacción para cambio de roles (Reemplazo total de roles dependientes).");

                            // A. Desactivar ABSOLUTAMENTE TODOS los roles actuales para este usuario
                            // Esto garantiza que solo quedará uno activo al final (Regla 3)
                            DB::table('model_has_roles')
                                ->where('model_id', $usuario->id)
                                ->where('model_type', 'App\Models\User')
                                ->update(['activo' => false]);
                            Log::info("AsistenciaActividad: Todos los roles del usuario han sido desactivados.");

                            // B. Obtener todos los IDs de roles "dependientes" (jerárquicos) en el sistema
                            $rolesDependientesIds = \App\Models\Role::where('dependiente', true)
                                ->pluck('id')
                                ->toArray();

                            // C. ELIMINAR (Detach) todas las conexiones del usuario con roles dependientes
                            // Según la petición: "eliminar todos los registros de la tabla intermedia" (de roles dependientes)
                            if (!empty($rolesDependientesIds)) {
                                $deleted = DB::table('model_has_roles')
                                    ->where('model_id', $usuario->id)
                                    ->where('model_type', 'App\Models\User')
                                    ->whereIn('role_id', $rolesDependientesIds)
                                    ->delete();
                                Log::info("AsistenciaActividad: Conexiones con roles dependientes eliminadas ({$deleted} registros).");
                            }

                            // D. Crear la nueva conexión vinculando al nuevo rol y activándolo
                            // attach() creará el nuevo registro en model_has_has_roles
                            $usuario->roles()->attach($nuevoRolId, [
                                'activo' => true, 
                                'model_type' => 'App\Models\User'
                            ]);
                            Log::info("AsistenciaActividad: Nuevo rol dependiente ID {$nuevoRolId} asignado y activado como ÚNICO activo.");
                        });

                        // Limpiar caché de permisos
                        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
                        Log::info("AsistenciaActividad: Caché de permisos limpio. Proceso finalizado.");
                    } else {
                        Log::info("AsistenciaActividad: No hay rol nuevo para asignar (id_rol_dependiente nulo).");
                    }
                } else {
                    Log::info("AsistenciaActividad: El usuario tiene un puntaje mayor. NO se realizan cambios.");
                }
            } else {
                Log::info("AsistenciaActividad: No es la primera asistencia (Total: {$totalAsistencias}). No se realizan cambios.");
            }
        }

        // --- Parte 3: Lógica para Culminar Tareas de Consolidación (NUEVO) ---
        $tareasACulminar = $this->actividad->restriccion_por_categoria && $inscripcion->categoriaActividad
            ? $inscripcion->categoriaActividad->tareasCulminadas
            : $this->actividad->tareasCulminadas;

        if ($tareasACulminar->isNotEmpty()) {
            if ($inscripcion->user_id) {
                $totalAsistencias = ActividadAsistenciaInscripcion::where('inscripcion_id', $inscripcion->id)->count();
                
                // Solo ejecutar en la primera asistencia para evitar duplicados
                if ($totalAsistencias === 1) {
                    foreach ($tareasACulminar as $tarea) {
                        // Buscar si ya existe una asignación de esta tarea al usuario
                        $tareaUsuario = \App\Models\TareaConsolidacionUsuario::where('user_id', $inscripcion->user_id)
                            ->where('tarea_consolidacion_id', $tarea->tarea_consolidacion_id)
                            ->first();

                        if ($tareaUsuario) {
                            // Ya existe, actualizar el estado
                            $tareaUsuario->update([
                                'estado_tarea_consolidacion_id' => $tarea->estado_tarea_consolidacion_id,
                                'fecha' => Carbon::today(),
                            ]);
                        } else {
                            // No existe, crear nueva asignación
                            \App\Models\TareaConsolidacionUsuario::create([
                                'user_id' => $inscripcion->user_id,
                                'tarea_consolidacion_id' => $tarea->tarea_consolidacion_id,
                                'estado_tarea_consolidacion_id' => $tarea->estado_tarea_consolidacion_id,
                                'fecha' => Carbon::today(),
                            ]);
                        }
                        
                        // Opcional: Registrar en historial
                        \App\Models\HistorialTareaConsolidacionUsuario::create([
                            'tarea_consolidacion_usuario_id' => \App\Models\TareaConsolidacionUsuario::where('user_id', $inscripcion->user_id)
                                ->where('tarea_consolidacion_id', $tarea->tarea_consolidacion_id)
                                ->first()->id,
                            'fecha' => Carbon::today(),
                            'detalle' => 'Asistencia confirmada en actividad: ' . $this->actividad->nombre,
                            'usuario_creacion_id' => auth()->id() ?? $inscripcion->user_id,
                        ]);
                    }
                }
            }
        }

        // --- INICIO DE LA CORRECCIÓN ---
        // $this->mount($this->actividad); // <-- ELIMINADO
        // $this->cargarAsistencias(); // <-- ELIMINADO (y era un typo)

        // Simplemente actualizamos el array que usa la vista para los botones
        $this->cargarAsistenciasDeHoy();
        // --- FIN DE LA CORRECCIÓN ---

        // =========================================================================
        // NUEVA LÓGICA: Verificar y mostrar respuestas de formulario con 'visible_asistencia'
        // =========================================================================
        $this->_verificarYMostrarRespuestasAsistencia($inscripcion);
    }
    
    /**
     * Muestra una alerta con las respuestas del formulario si hay elementos visibles en asistencia.
     */
    private function _verificarYMostrarRespuestasAsistencia(Inscripcion $inscripcion)
    {
        // 1. Obtener elementos de formulario con 'visible_asistencia' activado
        $elementosVisibles = $this->actividad->elementos()
            ->where('visible_asistencia', true)
            ->orderBy('orden', 'asc')
            ->get();
            
        if ($elementosVisibles->isEmpty()) {
             // Si no hay preguntas visibles, mostramos la alerta estándar de éxito
            $this->dispatch('showAlert', ['title' => '¡Asistencia Registrada!', 'text' => 'La asistencia se ha guardado correctamente.', 'icon' => 'success']);
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
        
        foreach ($elementosVisibles as $elemento) {
            $respuesta = $respuestas->get($elemento->id);
            $valorTexto = $this->_obtenerTextoRespuesta($respuesta, $elemento);
            
            $htmlMensaje .= '<div class="mb-2">';
            $htmlMensaje .= '<strong class="d-block text-black">' . $elemento->titulo . '</strong>';
            $htmlMensaje .= '<span class="text-black">' . $valorTexto . '</span>';
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
            'confirmButtonText' => 'Cerrar' // Botón solicitado para dar tiempo de leer
        ]);
    }

    /**
     * Helper para formatear la respuesta (adaptado de DashboardFormularios)
     */
    private function _obtenerTextoRespuesta($respuesta, $elemento)
    {
        $configuracion = \App\Models\Configuracion::find(1);
        if (!$respuesta) {
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
                return '$' . number_format($respuesta->respuesta_moneda ?? 0, 2);
            case 'archivo': 
                return $respuesta->url_archivo
                    ? '<a href="' . asset('storage/' . $configuracion->ruta_almacenamiento . '/archivos/actividades/' . $respuesta->url_archivo) . '" target="_blank"><i class="fas fa-paperclip"></i> Ver Archivo</a>'
                    : 'Sin archivo';
            case 'imagen': 
                return $respuesta->url_foto
                    ? '<a href="' . asset('storage/' . $respuesta->url_foto) . '" target="_blank"><i class="fas fa-image"></i> Ver Imagen</a>'
                    : 'Sin imagen';
            default:
                return 'Dato registrado';
        }
    }

    public function exportarAsistencias()

    {
        $fileName = 'asistencias-' . Str::slug($this->actividad->nombre) . '.xlsx';

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
                ->with(['user', 'compra']);

            // 4. Aplicar la lógica de búsqueda si el término no está vacío
            $terminoBusquedaLimpio = trim($this->busqueda);

            if (!empty($terminoBusquedaLimpio)) {
                $terminoBusqueda = '%' . $terminoBusquedaLimpio . '%';

                $query->where(function ($q) use ($terminoBusqueda) {

                    // A. Buscar en usuarios registrados (tabla 'users')
                    $q->whereHas('user', function ($userQuery) use ($terminoBusqueda) {
                        $userQuery->where('identificacion', 'like', $terminoBusqueda)
                            ->orWhere(DB::raw("CONCAT(primer_nombre, ' ', primer_apellido)"), 'ilike', $terminoBusqueda);
                    })

                        // B. Buscar en invitados por 'nombre_inscrito' (tabla 'inscripciones')
                        ->orWhere(function ($guestQuery) use ($terminoBusqueda) {
                            $guestQuery->whereNull('user_id') // Asegurarnos de que es un invitado
                                ->where('nombre_inscrito', 'ilike', $terminoBusqueda);
                        })

                        // C. Buscar en invitados por datos de la compra (tabla 'compras')
                        // (Esto sirve como respaldo si 'nombre_inscrito' estuviera vacío)
                        ->orWhereHas('compra', function ($compraQuery) use ($terminoBusqueda) {
                            $compraQuery->where('identificacion_comprador', 'like', $terminoBusqueda)
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
