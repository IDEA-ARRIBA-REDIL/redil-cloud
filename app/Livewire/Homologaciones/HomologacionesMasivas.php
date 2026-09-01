<?php

namespace App\Livewire\Homologaciones;

use App\Exports\PlantillaHomologacionesMasivasExport;
use App\Exports\ReporteErroresHomologacionExport;
use App\Imports\HomologacionesMasivasImport;
use App\Models\CrecimientoUsuario;
use App\Models\Escuela;
use App\Models\EstadoPasoCrecimientoUsuario;
use App\Models\EstadoTareaConsolidacion;
use App\Models\Materia;
use App\Models\MateriaAprobadaUsuario;
use App\Models\NivelAprobadoUsuario;
use App\Models\NivelEscuela;
use App\Models\TareaConsolidacionUsuario;
use App\Models\TipoUsuario;
use App\Models\User;
use App\Services\HitoTriggerService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

class HomologacionesMasivas extends Component
{
    use WithFileUploads;

    // --- PROPIEDADES DE SELECCIÓN Y CONFIGURACIÓN ---
    public $escuelaSeleccionadaId = null;

    public $itemSeleccionadoId = null; // ID de la materia o del nivel

    public $modo = 'materias'; // 'materias' o 'niveles'

    public $estadoHomologacionLote = null; // 1: Aprobado, 2: En proceso, 0: Reprobado

    public $escuelas = [];

    public $items = []; // Materias o Niveles de la escuela activa

    public $itemModel = null; // Instancia cargada con relaciones de la materia o nivel activo

    public $archivoExcel = null;

    // --- PROPIEDADES PARA AJUSTES / DEGRADACIÓN CUANDO ES "EN PROCESO" O "REPROBADO" ---
    public bool $aplicarAjustesAvance = false;

    public array $ajustesPasos = [];

    public array $ajustesTareas = [];

    public $ajusteTipoUsuarioId = null;

    public bool $forzarTipoUsuario = false;

    public $estadosPasosDisponibles = [];

    public $estadosTareasDisponibles = [];

    public $tiposUsuariosDisponibles = [];

    // --- CONTROL DE PASOS (WIZARD) ---
    public int $pasoActual = 1; // 1: Carga, 2: Previsualización / Diagnóstico, 3: Resumen Final

    // --- DATOS DE PREVISUALIZACIÓN Y DIAGNÓSTICO ---
    public array $filasDiagnostico = [];

    public array $filasConError = []; // Guarda las filas con error para el reporte descargable

    public array $metricas = [
        'total' => 0,
        'validos' => 0,
        'advertencias' => 0,
        'errores' => 0,
    ];

    // --- RESUMEN DE RESULTADOS TRAS EJECUCIÓN ---
    public array $resumenEjecucion = [];

    public function mount()
    {
        $this->escuelas = Escuela::orderBy('nombre')->get();
    }

    /**
     * Reacciona cuando cambia la escuela seleccionada.
     */
    public function updatedEscuelaSeleccionadaId($value)
    {
        $this->itemSeleccionadoId = null;
        $this->itemModel = null;
        $this->estadoHomologacionLote = null;
        $this->items = [];
        $this->limpiarAjustes();

        if (! empty($value)) {
            $escuela = Escuela::find($value);
            if ($escuela) {
                if ($escuela->tipo_matricula === 'niveles_agrupados' || ($escuela->tipo_matricula === null && $escuela->niveles()->exists())) {
                    $this->modo = 'niveles';
                    $this->items = NivelEscuela::where('escuela_id', $value)->orderBy('orden')->get();
                } else {
                    $this->modo = 'materias';
                    $this->items = Materia::where('escuela_id', $value)->orderBy('nombre')->get();
                }
            }
        }
    }

    /**
     * Reacciona cuando cambia el ítem (materia o nivel).
     */
    public function updatedItemSeleccionadoId($value)
    {
        $this->estadoHomologacionLote = null;
        $this->itemModel = null;
        $this->limpiarAjustes();

        if (! empty($value)) {
            $this->estadosPasosDisponibles = EstadoPasoCrecimientoUsuario::all()->keyBy('id');

            if ($this->modo === 'materias') {
                $this->itemModel = Materia::with([
                    'tipoUsuarioObjetivo',
                    'tareasCulminadas.tareaConsolidacion',
                    'tareasCulminadas.estadoTarea',
                    'pasosCrecimiento' => function ($q) {
                        $q->wherePivot('al_iniciar', false);
                    },
                ])->find($value);
            } else {
                $this->itemModel = NivelEscuela::with([
                    'tipoUsuarioObjetivo',
                    'tareasCulminadas.tareaConsolidacion',
                    'tareasCulminadas.estadoTarea',
                    'pasosCrecimiento' => function ($q) {
                        $q->wherePivot('al_iniciar', false);
                    },
                ])->find($value);
            }
        }
    }

    /**
     * Reacciona cuando cambia el estado global de la carga.
     */
    public function updatedEstadoHomologacionLote($value)
    {
        $this->limpiarAjustes();

        if ($value !== null && $value !== '' && (int) $value !== 1) {
            $this->estadosPasosDisponibles = EstadoPasoCrecimientoUsuario::all()->keyBy('id');
            $this->estadosTareasDisponibles = EstadoTareaConsolidacion::all();
            $this->tiposUsuariosDisponibles = TipoUsuario::all();

            if ($this->itemModel) {
                foreach ($this->itemModel->pasosCrecimiento as $paso) {
                    $this->ajustesPasos[$paso->id] = ''; // Por defecto "Sin asignar"
                }

                foreach ($this->itemModel->tareasCulminadas as $tareaConf) {
                    $this->ajustesTareas[$tareaConf->tarea_consolidacion_id] = ''; // Por defecto "Sin asignar"
                }
            }
        }
    }

    protected function limpiarAjustes()
    {
        $this->aplicarAjustesAvance = false;
        $this->ajustesPasos = [];
        $this->ajustesTareas = [];
        $this->ajusteTipoUsuarioId = null;
        $this->forzarTipoUsuario = false;
        $this->estadosPasosDisponibles = [];
        $this->estadosTareasDisponibles = [];
        $this->tiposUsuariosDisponibles = [];
    }

    /**
     * Descarga la plantilla oficial de Excel adaptada al estado seleccionado.
     */
    public function descargarPlantilla()
    {
        $estado = $this->estadoHomologacionLote !== null && $this->estadoHomologacionLote !== ''
            ? (int) $this->estadoHomologacionLote
            : 1;

        return Excel::download(new PlantillaHomologacionesMasivasExport($estado), 'plantilla_homologaciones_masivas.xlsx');
    }

    /**
     * Descarga el reporte en Excel con las filas que tuvieron error durante la previsualización.
     */
    public function descargarReporteErrores()
    {
        if (empty($this->filasConError)) {
            $this->dispatch('notificacion', [
                'tipo' => 'info',
                'mensaje' => 'No hay filas con error registradas para descargar.',
            ]);

            return;
        }

        return Excel::download(new ReporteErroresHomologacionExport($this->filasConError), 'reporte_errores_homologacion_masiva.xlsx');
    }

    /**
     * Procesa y analiza el archivo Excel real subido por el usuario.
     */
    public function analizarArchivoReal()
    {
        $this->validate([
            'escuelaSeleccionadaId' => 'required',
            'itemSeleccionadoId' => 'required',
            'estadoHomologacionLote' => 'required|in:0,1,2',
            'archivoExcel' => 'required|file|mimes:xlsx,xls,csv|max:10240', // Máx 10 MB
        ], [
            'escuelaSeleccionadaId.required' => 'Debes seleccionar una escuela.',
            'itemSeleccionadoId.required' => 'Debes seleccionar una materia o nivel.',
            'estadoHomologacionLote.required' => 'Debes seleccionar el estado en que se guardará la carga.',
            'archivoExcel.required' => 'Debes seleccionar un archivo Excel o CSV.',
            'archivoExcel.mimes' => 'El archivo debe tener formato .xlsx, .xls o .csv.',
            'archivoExcel.max' => 'El archivo no debe pesar más de 10 MB.',
        ]);

        try {
            $datosExcel = Excel::toArray(new HomologacionesMasivasImport, $this->archivoExcel);

            if (empty($datosExcel) || empty($datosExcel[0])) {
                $this->dispatch('notificacion', [
                    'tipo' => 'error',
                    'mensaje' => 'El archivo subido está vacío o no contiene filas con datos.',
                ]);

                return;
            }

            $filasRaw = $datosExcel[0];
            $filasNormalizadas = [];

            foreach ($filasRaw as $fila) {
                // Normalizar claves ignorando mayúsculas, tildes y espacios
                $filaLimpia = [];
                foreach ($fila as $clave => $valor) {
                    $claveNormalizada = mb_strtolower(trim($clave));
                    $claveNormalizada = str_replace(['á', 'é', 'í', 'ó', 'ú', ' '], ['a', 'e', 'i', 'o', 'u', '_'], $claveNormalizada);
                    $filaLimpia[$claveNormalizada] = is_string($valor) ? trim($valor) : $valor;
                }

                // Mapear campos tolerando alias
                $identificacion = $filaLimpia['identificacion_alumno'] ?? ($filaLimpia['identificacion'] ?? ($filaLimpia['documento'] ?? ($filaLimpia['cedula'] ?? '')));
                $email = $filaLimpia['email'] ?? ($filaLimpia['correo'] ?? ($filaLimpia['correo_electronico'] ?? ''));
                $notaFinal = $filaLimpia['nota_final'] ?? ($filaLimpia['nota'] ?? ($filaLimpia['calificacion'] ?? ''));
                $observacion = $filaLimpia['observacion'] ?? ($filaLimpia['observaciones'] ?? ($filaLimpia['motivo'] ?? ''));

                // Omitir filas totalmente vacías
                if (empty($identificacion) && empty($email) && empty($notaFinal) && empty($observacion)) {
                    continue;
                }

                $filasNormalizadas[] = [
                    'identificacion' => (string) $identificacion,
                    'email' => (string) $email,
                    'nota_final' => (string) $notaFinal,
                    'observacion' => (string) $observacion,
                ];
            }

            if (empty($filasNormalizadas)) {
                $this->dispatch('notificacion', [
                    'tipo' => 'error',
                    'mensaje' => 'No se encontraron filas con información válida en el archivo.',
                ]);

                return;
            }

            $this->procesarFilasParaDiagnostico($filasNormalizadas);
            $this->pasoActual = 2;
        } catch (\Exception $e) {
            Log::error('Error leyendo archivo excel masivo: '.$e->getMessage());
            $this->dispatch('notificacion', [
                'tipo' => 'error',
                'mensaje' => 'Error al leer el archivo Excel: '.$e->getMessage(),
            ]);
        }
    }

    /**
     * Analiza las filas y genera la tabla de diagnóstico contra la base de datos real.
     */
    public function procesarFilasParaDiagnostico(array $filas)
    {
        $this->filasDiagnostico = [];
        $this->filasConError = [];
        $validos = 0;
        $advertencias = 0;
        $errores = 0;

        $itemId = (int) $this->itemSeleccionadoId;
        $estadoId = (int) $this->estadoHomologacionLote;
        $esAprobado = ($estadoId === 1);

        // Registro de estudiantes procesados en este lote para detectar duplicados dentro del mismo Excel
        $usuariosVistos = [];

        foreach ($filas as $indice => $fila) {
            $numeroFila = $indice + 2; // Considerando cabecera en fila 1
            $identificacion = trim($fila['identificacion'] ?? '');
            $email = trim($fila['email'] ?? '');
            $notaRaw = trim($fila['nota_final'] ?? '');
            $observacion = trim($fila['observacion'] ?? '');

            // Búsqueda del estudiante: Prioridad 1: Identificación -> Prioridad 2: Email
            $usuario = null;
            if (! empty($identificacion)) {
                $usuario = User::with('sede')->where('identificacion', $identificacion)->first();
            }
            if (! $usuario && ! empty($email)) {
                $usuario = User::with('sede')->where('email', $email)->first();
            }

            $tipoDiagnostico = 'valido';
            $mensajesError = [];
            $mensajesAdv = [];

            // 1. Validar existencia del usuario
            if (! $usuario) {
                $tipoDiagnostico = 'error';
                $mensajesError[] = 'Estudiante no encontrado con el documento o correo indicado.';
            } else {
                // 2. Validar sede
                if (empty($usuario->sede_id)) {
                    $tipoDiagnostico = 'error';
                    $mensajesError[] = 'El estudiante no tiene una sede asignada en su perfil.';
                }

                // 3. Validar duplicados en el mismo archivo
                if (isset($usuariosVistos[$usuario->id])) {
                    $tipoDiagnostico = 'error';
                    $mensajesError[] = "El estudiante ya aparece en la fila #{$usuariosVistos[$usuario->id]} de este mismo archivo.";
                } else {
                    $usuariosVistos[$usuario->id] = $numeroFila;
                }
            }

            // 4. Normalizar y validar Nota Final SOLO si el lote es Aprobado
            $notaFloat = null;
            if ($esAprobado) {
                $notaLimpia = trim(str_replace(',', '.', $notaRaw));
                if ($notaLimpia === '' || ! is_numeric($notaLimpia) || (float) $notaLimpia < 0 || (float) $notaLimpia > 100) {
                    $tipoDiagnostico = 'error';
                    $mensajesError[] = "Nota final inválida ('{$notaRaw}'). Es obligatoria para estado Aprobado y debe estar entre 0.00 y 100.00.";
                } else {
                    $notaFloat = round((float) $notaLimpia, 2);
                }
            }

            // 5. Verificar si ya existe registro previo en la materia/nivel
            if ($usuario && $tipoDiagnostico !== 'error') {
                if ($this->modo === 'materias') {
                    $existente = MateriaAprobadaUsuario::where('user_id', $usuario->id)->where('materia_id', $itemId)->first();
                } else {
                    $existente = NivelAprobadoUsuario::where('user_id', $usuario->id)->where('nivel_id', $itemId)->first();
                }

                if ($existente) {
                    $tipoDiagnostico = 'advertencia';
                    $nombresEstados = ['0' => 'Reprobado', '1' => 'Aprobado', '2' => 'En proceso'];
                    $estActual = $nombresEstados[(string) $existente->aprobado] ?? 'Registrado';
                    $mensajesAdv[] = "El alumno ya posee un registro previo en estado {$estActual}. Se sobreescribirá.";
                }
            }

            if ($tipoDiagnostico === 'valido') {
                $validos++;
            } elseif ($tipoDiagnostico === 'advertencia') {
                $advertencias++;
            } else {
                $errores++;
            }

            $estadosNombres = [1 => 'Aprobado', 2 => 'En proceso', 0 => 'Reprobado'];
            $mensajeFinal = $tipoDiagnostico === 'error'
                ? implode(' | ', $mensajesError)
                : (implode(' | ', $mensajesAdv) ?: 'Registro listo para procesar.');

            $filaRegistrada = [
                'fila_numero' => $numeroFila,
                'identificacion' => $identificacion,
                'email' => $email,
                'usuario_id' => $usuario?->id,
                'usuario_nombre' => $usuario ? trim($usuario->nombre(4)) : 'No identificado',
                'sede_id' => $usuario?->sede_id,
                'sede_nombre' => $usuario?->sede?->nombre ?? 'Sin sede',
                'estado_id' => $estadoId,
                'estado_texto' => $estadosNombres[$estadoId] ?? 'Sin estado',
                'nota_final' => $notaFloat !== null ? number_format($notaFloat, 2) : ($esAprobado ? $notaRaw : '-'),
                'observacion' => $observacion ?: 'Homologación masiva',
                'tipo_diagnostico' => $tipoDiagnostico,
                'mensaje_diagnostico' => $mensajeFinal,
            ];

            $this->filasDiagnostico[] = $filaRegistrada;

            if ($tipoDiagnostico === 'error') {
                $this->filasConError[] = $filaRegistrada;
            }
        }

        $this->metricas = [
            'total' => count($this->filasDiagnostico),
            'validos' => $validos,
            'advertencias' => $advertencias,
            'errores' => $errores,
        ];
    }

    /**
     * Dispara la confirmación interactiva con SweetAlert2 antes de procesar.
     */
    public function confirmarEjecucion()
    {
        $procesables = $this->metricas['validos'] + $this->metricas['advertencias'];

        if ($procesables === 0) {
            $this->dispatch('notificacion', [
                'tipo' => 'warning',
                'titulo' => 'Sin registros procesables',
                'mensaje' => 'No hay registros válidos para procesar en este lote.',
            ]);

            return;
        }

        $nombreItem = $this->itemModel?->nombre ?? '';
        $estadosNombres = [1 => 'Aprobado', 2 => 'En proceso', 0 => 'Reprobado'];
        $estadoNombre = $estadosNombres[(int) $this->estadoHomologacionLote] ?? 'En proceso';

        $this->dispatch('confirmar-cargue-masivo', [
            'totalProcesables' => $procesables,
            'totalErrores' => $this->metricas['errores'],
            'itemNombre' => $nombreItem,
            'tipo' => $this->modo === 'materias' ? 'Materia' : 'Nivel',
            'estadoNombre' => $estadoNombre,
        ]);
    }

    /**
     * Ejecuta el procesamiento atómico de las homologaciones masivas en base de datos.
     */
    public function ejecutarProcesamiento()
    {
        $item = $this->itemModel;
        if (! $item) {
            $this->dispatch('notificacion', ['tipo' => 'error', 'mensaje' => 'El ítem seleccionado no está cargado.']);

            return;
        }

        $estadoId = (int) $this->estadoHomologacionLote;
        $esAprobado = ($estadoId === 1);

        $procesadosExito = 0;
        $actualizados = 0;
        $omitidosPorError = 0;
        $tareasEjecutadas = 0;
        $pasosEjecutados = 0;
        $rolesPromovidos = 0;

        DB::beginTransaction();
        try {
            foreach ($this->filasDiagnostico as $fila) {
                if ($fila['tipo_diagnostico'] === 'error') {
                    $omitidosPorError++;

                    continue;
                }

                $alumnoId = (int) $fila['usuario_id'];
                $notaFinal = $esAprobado ? (float) $fila['nota_final'] : null;

                // 1. Guardar la homologación
                if ($this->modo === 'materias') {
                    MateriaAprobadaUsuario::updateOrCreate(
                        [
                            'user_id' => $alumnoId,
                            'materia_id' => $item->id,
                        ],
                        [
                            'aprobado' => $estadoId,
                            'nota_final' => $notaFinal,
                            'creditos_aprobados' => $esAprobado ? $item->creditos : null,
                            'es_homologacion' => true,
                            'observacion_homologacion' => $fila['observacion'],
                            'sede_id' => $fila['sede_id'],
                            'fecha_homologacion' => now(),
                            'fecha_homologacion_aprobacion' => $esAprobado ? now() : null,
                            'homologado_por_user_id' => Auth::id(),
                        ]
                    );
                } else {
                    NivelAprobadoUsuario::updateOrCreate(
                        [
                            'user_id' => $alumnoId,
                            'nivel_id' => $item->id,
                        ],
                        [
                            'aprobado' => $estadoId,
                            'nota_final' => $notaFinal,
                            'es_homologacion' => true,
                            'observacion_homologacion' => $fila['observacion'],
                            'sede_id' => $fila['sede_id'],
                            'fecha_homologacion' => now(),
                            'fecha_homologacion_aprobacion' => $esAprobado ? now() : null,
                            'homologado_por_user_id' => Auth::id(),
                        ]
                    );
                }

                if ($fila['tipo_diagnostico'] === 'advertencia') {
                    $actualizados++;
                } else {
                    $procesadosExito++;
                }

                // 2. Aplicar automatizaciones si es Aprobado
                if ($esAprobado) {
                    foreach ($item->tareasCulminadas as $tConf) {
                        TareaConsolidacionUsuario::procesarTarea(
                            userId: $alumnoId,
                            tareaConsolidacionId: $tConf->tarea_consolidacion_id,
                            estadoObjetivoId: $tConf->estado_tarea_consolidacion_id,
                            observaciones: 'Cargue masivo por homologación de '.($this->modo === 'materias' ? 'materia' : 'nivel').': '.$item->nombre,
                            fecha: now(),
                            autorId: Auth::id()
                        );
                        $tareasEjecutadas++;
                    }

                    foreach ($item->pasosCrecimiento as $pConf) {
                        $estObj = $pConf->pivot->estado_paso_crecimiento_usuario_id;
                        if ($estObj) {
                            CrecimientoUsuario::procesarPaso(
                                userId: $alumnoId,
                                pasoCrecimientoId: $pConf->id,
                                estadoObjetivoId: $estObj,
                                detalle: 'Cargue masivo por homologación de '.($this->modo === 'materias' ? 'materia' : 'nivel').': '.$item->nombre,
                                fecha: now(),
                                autorId: Auth::id()
                            );
                            $pasosEjecutados++;
                        }
                    }

                    $usuarioModel = User::find($alumnoId);
                    if ($item->tipo_usuario_objetivo_id && $usuarioModel) {
                        if ($usuarioModel->promoverTipoUsuario($item->tipo_usuario_objetivo_id)) {
                            $rolesPromovidos++;
                        }
                    }

                    if ($this->modo === 'materias') {
                        try {
                            app(HitoTriggerService::class)->onMateriaAprobada(
                                $alumnoId,
                                $item->id,
                                $item->escuela_id,
                                $item->nivel_id,
                                null,
                                now()->toDateString()
                            );
                        } catch (\Throwable $e) {
                            Log::error('Error masivo hito materia: '.$e->getMessage());
                        }
                    }
                } else {
                    // 3. Aplicar ajustes manuales solo si el switch está activado
                    if ($this->aplicarAjustesAvance) {
                        // Ajustar Pasos de Crecimiento
                        foreach ($this->ajustesPasos as $pasoId => $estPasoId) {
                            if (empty($estPasoId)) {
                                $crec = CrecimientoUsuario::where('user_id', $alumnoId)->where('paso_crecimiento_id', $pasoId)->first();
                                if ($crec) {
                                    $crec->delete();
                                }
                            } else {
                                $crec = CrecimientoUsuario::where('user_id', $alumnoId)->where('paso_crecimiento_id', $pasoId)->first();
                                if ($crec) {
                                    $crec->update(['estado_id' => $estPasoId, 'fecha' => now(), 'detalle' => 'Ajuste por cargue masivo de homologación']);
                                } else {
                                    CrecimientoUsuario::create(['user_id' => $alumnoId, 'paso_crecimiento_id' => $pasoId, 'estado_id' => $estPasoId, 'fecha' => now(), 'detalle' => 'Ajuste por cargue masivo de homologación']);
                                }
                                $pasosEjecutados++;
                            }
                        }

                        // Ajustar Tareas de Consolidación
                        foreach ($this->ajustesTareas as $tareaConsolidacionId => $estTareaId) {
                            if (empty($estTareaId)) {
                                $tar = TareaConsolidacionUsuario::where('user_id', $alumnoId)->where('tarea_consolidacion_id', $tareaConsolidacionId)->first();
                                if ($tar) {
                                    $tar->historial()->delete();
                                    $tar->bitacora()->delete();
                                    $tar->delete();
                                }
                            } else {
                                $tar = TareaConsolidacionUsuario::where('user_id', $alumnoId)->where('tarea_consolidacion_id', $tareaConsolidacionId)->first();
                                if ($tar) {
                                    $tar->update(['estado_tarea_consolidacion_id' => $estTareaId, 'fecha' => now()]);
                                } else {
                                    TareaConsolidacionUsuario::create(['user_id' => $alumnoId, 'tarea_consolidacion_id' => $tareaConsolidacionId, 'estado_tarea_consolidacion_id' => $estTareaId, 'fecha' => now()]);
                                }
                                $tareasEjecutadas++;
                            }
                        }

                        // Ajustar Tipo de Usuario
                        if ($this->ajusteTipoUsuarioId) {
                            $usuarioModel = User::find($alumnoId);
                            if ($usuarioModel && $usuarioModel->tipo_usuario_id != $this->ajusteTipoUsuarioId) {
                                if ($usuarioModel->promoverTipoUsuario($this->ajusteTipoUsuarioId, forzar: $this->forzarTipoUsuario)) {
                                    $rolesPromovidos++;
                                }
                            }
                        }
                    }
                }
            }

            DB::commit();

            $estadosNombres = [1 => 'Aprobado', 2 => 'En proceso', 0 => 'Reprobado'];

            $this->resumenEjecucion = [
                'item_nombre' => $item->nombre,
                'tipo' => $this->modo === 'materias' ? 'Materia' : 'Nivel',
                'estado_nombre' => $estadosNombres[$estadoId] ?? 'Registrado',
                'nuevos' => $procesadosExito,
                'actualizados' => $actualizados,
                'omitidos_error' => $omitidosPorError,
                'tareas_ejecutadas' => $tareasEjecutadas,
                'pasos_ejecutados' => $pasosEjecutados,
                'roles_promovidos' => $rolesPromovidos,
            ];

            $this->pasoActual = 3;
            $this->dispatch('notificacion', [
                'titulo' => '¡Procesamiento Completado!',
                'mensaje' => 'Se completó la homologación masiva exitosamente.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en ejecución masiva: '.$e->getMessage());
            $this->dispatch('notificacion', [
                'tipo' => 'error',
                'mensaje' => 'Error al procesar: '.$e->getMessage(),
            ]);
        }
    }

    /**
     * Reinicia el flujo para cargar un nuevo archivo.
     */
    public function reiniciar()
    {
        $this->reset([
            'pasoActual',
            'filasDiagnostico',
            'filasConError',
            'metricas',
            'resumenEjecucion',
            'archivoExcel',
            'escuelaSeleccionadaId',
            'itemSeleccionadoId',
            'itemModel',
            'estadoHomologacionLote',
            'items',
            'aplicarAjustesAvance',
            'ajustesPasos',
            'ajustesTareas',
            'ajusteTipoUsuarioId',
            'forzarTipoUsuario',
            'estadosPasosDisponibles',
            'estadosTareasDisponibles',
            'tiposUsuariosDisponibles',
        ]);
    }

    public function render()
    {
        return view('livewire.homologaciones.homologaciones-masivas');
    }
}
