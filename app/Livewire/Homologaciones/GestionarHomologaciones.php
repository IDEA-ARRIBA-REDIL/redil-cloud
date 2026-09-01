<?php

namespace App\Livewire\Homologaciones;

use App\Models\CrecimientoUsuario;
use App\Models\Escuela;
use App\Models\EstadoPasoCrecimientoUsuario;
use App\Models\EstadoTareaConsolidacion;
use App\Models\Materia;
use App\Models\MateriaAprobadaUsuario;
use App\Models\NivelAprobadoUsuario;
use App\Models\NivelEscuela;
use App\Models\Sede;
use App\Models\TareaConsolidacionUsuario;
use App\Models\TipoUsuario;
use App\Models\User;
use App\Services\HitoTriggerService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class GestionarHomologaciones extends Component
{
    // --- PROPIEDADES PÚBLICAS (ESTADO DEL COMPONENTE) ---

    // Propiedades para guardar las selecciones del administrador en la interfaz.
    public $alumnoSeleccionadoId;

    public $escuelaSeleccionadaId;

    // Colecciones para poblar los menús desplegables.
    public $escuelas = [];

    public $sedes = [];

    // Almacena la lista de materias/niveles que se muestra después de la búsqueda.
    public $materias = [];

    // Propiedades para controlar el modal de homologación.
    public $showModal = false;

    public $modo = 'materias'; // 'materias' o 'niveles'

    public ?Materia $materiaParaHomologar = null;

    public ?NivelEscuela $nivelParaHomologar = null;

    public $sedeHomologacionId;

    public $observacionHomologacion;

    public $estadoHomologacion = 2; // Default: En proceso (2)

    public $notaHomologacion = null;

    public ?int $estadoPrevioHomologacion = null;

    public bool $esAjustePorCambioEstado = false;

    // Propiedades para el modal de eliminación y ajuste de automatizaciones
    public bool $showModalEliminar = false;

    public ?Materia $materiaAEliminar = null;

    public ?NivelEscuela $nivelAEliminar = null;

    public array $ajustesPasos = [];

    public array $ajustesTareas = [];

    public $ajusteTipoUsuarioId = null;

    public $estadosPasosDisponibles = [];

    public $estadosTareasDisponibles = [];

    public $tiposUsuariosDisponibles = [];

    // Reglas de validación para los formularios.
    protected $rules = [
        'sedeHomologacionId' => 'required|exists:sedes,id',
        'observacionHomologacion' => 'required|string|min:10',
        'alumnoSeleccionadoId' => 'required',
        'escuelaSeleccionadaId' => 'required',
        'estadoHomologacion' => 'required|in:0,1,2',
        'notaHomologacion' => 'required_if:estadoHomologacion,1|nullable|numeric|min:0|max:100',
    ];

    // Mensajes de error personalizados para una mejor experiencia de usuario.
    protected $messages = [
        'sedeHomologacionId.required' => 'Debes seleccionar una sede.',
        'observacionHomologacion.required' => 'La observación es obligatoria.',
        'observacionHomologacion.min' => 'La observación debe tener al menos 10 caracteres.',
        'alumnoSeleccionadoId.required' => 'Debes seleccionar un alumno.',
        'escuelaSeleccionadaId.required' => 'Debes seleccionar una escuela.',
        'estadoHomologacion.required' => 'Debes seleccionar el estado de la homologación.',
        'notaHomologacion.required_if' => 'La nota es obligatoria cuando el estado es Aprobado.',
        'notaHomologacion.numeric' => 'La nota debe ser un número válido.',
        'notaHomologacion.min' => 'La nota no puede ser menor a 0.',
        'notaHomologacion.max' => 'La nota no puede ser mayor a 100.',
    ];

    /**
     * Se ejecuta una sola vez cuando el componente se carga por primera vez.
     */
    public function mount()
    {
        $this->escuelas = Escuela::orderBy('nombre')->get();
        $this->sedes = Sede::orderBy('nombre')->get();
        $this->estadoHomologacion = 2;
    }

    public function updatedEscuelaSeleccionadaId($value)
    {
        $this->materias = [];
        if ($value) {
            $escuela = Escuela::find($value);
            if ($escuela) {
                if ($escuela->tipo_matricula === 'niveles_agrupados' || ($escuela->tipo_matricula === null && $escuela->niveles()->exists())) {
                    $this->modo = 'niveles';
                } else {
                    $this->modo = 'materias';
                }
            }
        }
    }

    /**
     * Se ejecuta al hacer clic en el botón "Buscar".
     */
    public function buscar($alumnoId)
    {
        $this->alumnoSeleccionadoId = $alumnoId;

        $this->validate([
            'alumnoSeleccionadoId' => 'required',
            'escuelaSeleccionadaId' => 'required',
        ], [
            'alumnoSeleccionadoId.required' => 'Debes seleccionar un alumno para poder buscar.',
            'escuelaSeleccionadaId.required' => 'Debes seleccionar una escuela para poder buscar.',
        ]);

        if ($this->escuelaSeleccionadaId) {
            $escuela = Escuela::find($this->escuelaSeleccionadaId);
            if ($escuela) {
                if ($escuela->tipo_matricula === 'niveles_agrupados' || ($escuela->tipo_matricula === null && $escuela->niveles()->exists())) {
                    $this->modo = 'niveles';
                } else {
                    $this->modo = 'materias';
                }
            }
        }

        if ($this->modo === 'materias') {
            $this->buscarMaterias();
        } else {
            $this->buscarNiveles();
        }
    }

    /**
     * Busca las materias de la escuela seleccionada y recupera la información clave de homologación.
     */
    public function buscarMaterias()
    {
        $materiasDeEscuela = Materia::where('escuela_id', $this->escuelaSeleccionadaId)
            ->orderBy('nombre')->get();

        $historialMap = MateriaAprobadaUsuario::where('user_id', $this->alumnoSeleccionadoId)
            ->get()
            ->keyBy('materia_id');

        $this->materias = $materiasDeEscuela->map(function ($materia) use ($historialMap) {
            $registro = $historialMap->get($materia->id);
            $materia->estado = $registro ? (string) $registro->aprobado : null;
            $materia->fecha_homologacion = $registro?->fecha_homologacion;
            $materia->fecha_homologacion_aprobacion = $registro?->fecha_homologacion_aprobacion;
            $materia->nota_final = $registro?->nota_final;
            $materia->creditos_aprobados = $registro?->creditos_aprobados;

            return $materia;
        });
    }

    /**
     * Busca los niveles de la escuela seleccionada y recupera la información clave de homologación.
     */
    public function buscarNiveles()
    {
        $nivelesDeEscuela = NivelEscuela::where('escuela_id', $this->escuelaSeleccionadaId)
            ->orderBy('orden')->get();

        $historialMap = NivelAprobadoUsuario::where('user_id', $this->alumnoSeleccionadoId)
            ->get()
            ->keyBy('nivel_id');

        $this->materias = $nivelesDeEscuela->map(function ($nivel) use ($historialMap) {
            $registro = $historialMap->get($nivel->id);
            $nivel->estado = $registro ? (string) $registro->aprobado : null;
            $nivel->fecha_homologacion = $registro?->fecha_homologacion;
            $nivel->fecha_homologacion_aprobacion = $registro?->fecha_homologacion_aprobacion;
            $nivel->nota_final = $registro?->nota_final;
            $nivel->materia_nombre = $nivel->nombre;

            return $nivel;
        });
    }

    /**
     * Prepara y abre el modal para realizar una homologación, precargando los datos si ya existe un registro.
     */
    public function abrirModalHomologacion(int $id)
    {
        if ($this->modo === 'materias') {
            $this->materiaParaHomologar = Materia::with([
                'tipoUsuarioObjetivo',
                'tareasCulminadas.tareaConsolidacion',
                'pasosCrecimiento' => function ($query) {
                    $query->wherePivot('al_iniciar', false);
                },
            ])->find($id);
            $this->nivelParaHomologar = null;

            $existente = MateriaAprobadaUsuario::where('user_id', $this->alumnoSeleccionadoId)
                ->where('materia_id', $id)
                ->first();

            $this->estadoPrevioHomologacion = $existente ? (int) $existente->aprobado : null;

            if ($existente) {
                $this->estadoHomologacion = (int) $existente->aprobado;
                $this->notaHomologacion = $existente->nota_final;
                $this->sedeHomologacionId = $existente->sede_id;
                $this->observacionHomologacion = $existente->observacion_homologacion;
            } else {
                $this->reset(['sedeHomologacionId', 'observacionHomologacion', 'notaHomologacion']);
                $this->estadoHomologacion = 2; // Default En proceso
            }
        } else {
            $this->nivelParaHomologar = NivelEscuela::with([
                'tipoUsuarioObjetivo',
                'tareasCulminadas.tareaConsolidacion',
                'pasosCrecimiento' => function ($query) {
                    $query->wherePivot('al_iniciar', false);
                },
            ])->find($id);
            $this->materiaParaHomologar = null;

            $existente = NivelAprobadoUsuario::where('user_id', $this->alumnoSeleccionadoId)
                ->where('nivel_id', $id)
                ->first();

            $this->estadoPrevioHomologacion = $existente ? (int) $existente->aprobado : null;

            if ($existente) {
                $this->estadoHomologacion = (int) $existente->aprobado;
                $this->notaHomologacion = $existente->nota_final;
                $this->sedeHomologacionId = $existente->sede_id;
                $this->observacionHomologacion = $existente->observacion_homologacion;
            } else {
                $this->reset(['sedeHomologacionId', 'observacionHomologacion', 'notaHomologacion']);
                $this->estadoHomologacion = 2; // Default En proceso
            }
        }

        $this->esAjustePorCambioEstado = false;
        $this->resetErrorBag();
        $this->showModal = true;
        $this->dispatch('abrir-offcanvas-homologacion');
    }

    public function updatedNotaHomologacion($value)
    {
        if ($value !== null && $value !== '') {
            $this->notaHomologacion = str_replace(',', '.', (string) $value);
        }
    }

    /**
     * Valida el formulario y dispara el evento del modal de confirmación SweetAlert2 o abre el panel de ajustes.
     */
    public function confirmarGuardado()
    {
        if ($this->notaHomologacion !== null && $this->notaHomologacion !== '') {
            $this->notaHomologacion = str_replace(',', '.', (string) $this->notaHomologacion);
        }

        $this->validate([
            'estadoHomologacion' => 'required|in:0,1,2',
            'notaHomologacion' => 'required_if:estadoHomologacion,1|nullable|numeric|min:0|max:100',
            'sedeHomologacionId' => 'required|exists:sedes,id',
            'observacionHomologacion' => 'required|string|min:10',
        ]);

        $alumno = User::find($this->alumnoSeleccionadoId);
        $sede = Sede::find($this->sedeHomologacionId);
        $item = $this->modo === 'materias' ? $this->materiaParaHomologar : $this->nivelParaHomologar;
        $nombreItem = $item?->nombre;
        $nombresEstados = ['0' => 'Reprobado', '1' => 'Aprobado', '2' => 'En proceso'];
        $estadoNombre = $nombresEstados[(string) $this->estadoHomologacion] ?? 'En proceso';

        // Si la homologación estaba aprobada y ahora cambia a "En proceso" (2) o "Reprobado" (0)
        if ($this->estadoPrevioHomologacion === 1 && (int) $this->estadoHomologacion !== 1 && $item && $alumno) {
            $hasPasos = $item->pasosCrecimiento->isNotEmpty();
            $hasTareas = $item->tareasCulminadas->isNotEmpty();
            $hasTipoUsuario = ! empty($item->tipo_usuario_objetivo_id);

            // Si tiene automatizaciones asociadas, abrir el panel de ajustes
            if ($hasPasos || $hasTareas || $hasTipoUsuario) {
                $this->esAjustePorCambioEstado = true;
                $this->materiaAEliminar = $this->modo === 'materias' ? $this->materiaParaHomologar : null;
                $this->nivelAEliminar = $this->modo === 'niveles' ? $this->nivelParaHomologar : null;

                $this->estadosPasosDisponibles = EstadoPasoCrecimientoUsuario::all();
                $this->estadosTareasDisponibles = EstadoTareaConsolidacion::all();
                $this->tiposUsuariosDisponibles = TipoUsuario::all();

                $this->ajustesPasos = [];
                foreach ($item->pasosCrecimiento as $paso) {
                    $crecimientoActual = CrecimientoUsuario::where('user_id', $alumno->id)
                        ->where('paso_crecimiento_id', $paso->id)
                        ->first();

                    $this->ajustesPasos[$paso->id] = $crecimientoActual ? $crecimientoActual->estado_id : '';
                }

                $this->ajustesTareas = [];
                foreach ($item->tareasCulminadas as $tareaConfig) {
                    $tareaUsuarioActual = TareaConsolidacionUsuario::where('user_id', $alumno->id)
                        ->where('tarea_consolidacion_id', $tareaConfig->tarea_consolidacion_id)
                        ->first();

                    $this->ajustesTareas[$tareaConfig->tarea_consolidacion_id] = $tareaUsuarioActual ? $tareaUsuarioActual->estado_tarea_consolidacion_id : '';
                }

                $this->ajusteTipoUsuarioId = $alumno->tipo_usuario_id;
                $this->showModal = false;
                $this->showModalEliminar = true;

                $this->dispatch('cerrar-offcanvas-homologacion');
                $this->dispatch('abrir-offcanvas-eliminar');

                return;
            }
        }

        $this->dispatch('confirmar-homologacion', [
            'alumno' => $alumno ? trim($alumno->nombre(4)) : 'Alumno seleccionado',
            'item' => $nombreItem,
            'tipo' => $this->modo === 'materias' ? 'Materia' : 'Nivel',
            'estado' => $estadoNombre,
            'estadoId' => (int) $this->estadoHomologacion,
            'nota' => (int) $this->estadoHomologacion === 1 ? $this->notaHomologacion : null,
            'sede' => $sede ? $sede->nombre : '',
        ]);
    }

    /**
     * Valida y guarda el nuevo registro de homologación.
     */
    public function guardarHomologacion()
    {
        if ($this->modo === 'materias') {
            $this->guardarHomologacionMateria();
        } else {
            $this->guardarHomologacionNivel();
        }
    }

    public function guardarHomologacionMateria()
    {
        if ($this->notaHomologacion !== null && $this->notaHomologacion !== '') {
            $this->notaHomologacion = str_replace(',', '.', (string) $this->notaHomologacion);
        }

        $this->validate([
            'estadoHomologacion' => 'required|in:0,1,2',
            'notaHomologacion' => 'required_if:estadoHomologacion,1|nullable|numeric|min:0|max:100',
            'sedeHomologacionId' => 'required|exists:sedes,id',
            'observacionHomologacion' => 'required|string|min:10',
        ]);

        DB::beginTransaction();
        try {
            $esAprobado = ((int) $this->estadoHomologacion === MateriaAprobadaUsuario::ESTADO_APROBADO);

            MateriaAprobadaUsuario::updateOrCreate(
                [
                    'user_id' => $this->alumnoSeleccionadoId,
                    'materia_id' => $this->materiaParaHomologar->id,
                ],
                [
                    'aprobado' => (int) $this->estadoHomologacion,
                    'nota_final' => $esAprobado ? $this->notaHomologacion : null,
                    'creditos_aprobados' => $esAprobado ? $this->materiaParaHomologar?->creditos : null,
                    'es_homologacion' => true,
                    'observacion_homologacion' => $this->observacionHomologacion,
                    'sede_id' => $this->sedeHomologacionId,
                    'fecha_homologacion' => now(),
                    'fecha_homologacion_aprobacion' => $esAprobado ? now() : null,
                    'homologado_por_user_id' => Auth::id(),
                ]
            );

            // --- EJECUCIÓN DE EFECTOS SECUNDARIOS SI ES APROBADO ---
            if ($esAprobado) {
                $alumnoId = (int) $this->alumnoSeleccionadoId;
                $materia = $this->materiaParaHomologar;

                // A. Tareas de Consolidación Culminadas
                foreach ($materia->tareasCulminadas as $tareaConfig) {
                    TareaConsolidacionUsuario::procesarTarea(
                        userId: $alumnoId,
                        tareaConsolidacionId: $tareaConfig->tarea_consolidacion_id,
                        estadoObjetivoId: $tareaConfig->estado_tarea_consolidacion_id,
                        observaciones: 'Culminación automática por homologación de la materia: '.$materia->nombre,
                        fecha: now(),
                        autorId: Auth::id()
                    );
                }

                // B. Pasos de Crecimiento Culminados
                foreach ($materia->pasosCrecimiento as $pasoConfig) {
                    $estadoObjetivoId = $pasoConfig->pivot->estado_paso_crecimiento_usuario_id;
                    if ($estadoObjetivoId) {
                        CrecimientoUsuario::procesarPaso(
                            userId: $alumnoId,
                            pasoCrecimientoId: $pasoConfig->id,
                            estadoObjetivoId: $estadoObjetivoId,
                            detalle: 'Culminación automática por homologación de la materia: '.$materia->nombre,
                            fecha: now(),
                            autorId: Auth::id()
                        );
                    }
                }

                // C. Promoción de Tipo de Usuario / Rol
                $usuario = User::find($alumnoId);
                if ($materia->tipo_usuario_objetivo_id && $usuario) {
                    $usuario->promoverTipoUsuario($materia->tipo_usuario_objetivo_id);
                }

                // D. Disparo de Hito Automático
                try {
                    app(HitoTriggerService::class)->onMateriaAprobada(
                        $alumnoId,
                        $materia->id,
                        $materia->escuela_id,
                        $materia->nivel_id,
                        null,
                        now()->toDateString()
                    );
                } catch (\Throwable $e) {
                    Log::error('Error disparando hito en homologación de materia: '.$e->getMessage());
                }
            }

            DB::commit();
            $this->showModal = false;
            $this->buscar($this->alumnoSeleccionadoId);
            $this->dispatch('cerrar-offcanvas-homologacion');
            $this->dispatch('notificacion', ['mensaje' => '¡Materia homologada con éxito!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error materia homologacion: '.$e->getMessage());
            $this->dispatch('notificacion', ['mensaje' => 'Error: '.$e->getMessage(), 'tipo' => 'error']);
        }
    }

    public function guardarHomologacionNivel()
    {
        if ($this->notaHomologacion !== null && $this->notaHomologacion !== '') {
            $this->notaHomologacion = str_replace(',', '.', (string) $this->notaHomologacion);
        }

        $this->validate([
            'estadoHomologacion' => 'required|in:0,1,2',
            'notaHomologacion' => 'required_if:estadoHomologacion,1|nullable|numeric|min:0|max:100',
            'sedeHomologacionId' => 'required|exists:sedes,id',
            'observacionHomologacion' => 'required|string|min:10',
        ]);

        DB::beginTransaction();
        try {
            $esAprobado = ((int) $this->estadoHomologacion === NivelAprobadoUsuario::ESTADO_APROBADO);

            NivelAprobadoUsuario::updateOrCreate(
                [
                    'user_id' => $this->alumnoSeleccionadoId,
                    'nivel_id' => $this->nivelParaHomologar->id,
                ],
                [
                    'aprobado' => (int) $this->estadoHomologacion,
                    'nota_final' => $esAprobado ? $this->notaHomologacion : null,
                    'es_homologacion' => true,
                    'observacion_homologacion' => $this->observacionHomologacion,
                    'sede_id' => $this->sedeHomologacionId,
                    'fecha_homologacion' => now(),
                    'fecha_homologacion_aprobacion' => $esAprobado ? now() : null,
                    'homologado_por_user_id' => Auth::id(),
                ]
            );

            // --- EJECUCIÓN DE EFECTOS SECUNDARIOS SI ES APROBADO ---
            if ($esAprobado) {
                $alumnoId = (int) $this->alumnoSeleccionadoId;
                $nivel = $this->nivelParaHomologar;

                // A. Tareas de Consolidación Culminadas del Nivel
                foreach ($nivel->tareasCulminadas as $tareaConfig) {
                    TareaConsolidacionUsuario::procesarTarea(
                        userId: $alumnoId,
                        tareaConsolidacionId: $tareaConfig->tarea_consolidacion_id,
                        estadoObjetivoId: $tareaConfig->estado_tarea_consolidacion_id,
                        observaciones: 'Culminación automática por homologación del nivel: '.$nivel->nombre,
                        fecha: now(),
                        autorId: Auth::id()
                    );
                }

                // B. Pasos de Crecimiento Culminados del Nivel
                foreach ($nivel->pasosCrecimiento as $pasoConfig) {
                    $estadoObjetivoId = $pasoConfig->pivot->estado_paso_crecimiento_usuario_id;
                    if ($estadoObjetivoId) {
                        CrecimientoUsuario::procesarPaso(
                            userId: $alumnoId,
                            pasoCrecimientoId: $pasoConfig->id,
                            estadoObjetivoId: $estadoObjetivoId,
                            detalle: 'Culminación automática por homologación del nivel: '.$nivel->nombre,
                            fecha: now(),
                            autorId: Auth::id()
                        );
                    }
                }

                // C. Promoción de Tipo de Usuario / Rol
                $usuario = User::find($alumnoId);
                if ($nivel->tipo_usuario_objetivo_id && $usuario) {
                    $usuario->promoverTipoUsuario($nivel->tipo_usuario_objetivo_id);
                }
            }

            DB::commit();
            $this->showModal = false;
            $this->buscar($this->alumnoSeleccionadoId);
            $this->dispatch('cerrar-offcanvas-homologacion');
            $this->dispatch('notificacion', ['mensaje' => '¡Nivel homologado con éxito!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error nivel homologacion: '.$e->getMessage());
            $this->dispatch('notificacion', ['mensaje' => 'Error: '.$e->getMessage(), 'tipo' => 'error']);
        }
    }

    /**
     * Prepara la eliminación de la homologación.
     * Si la materia o nivel tiene automatizaciones configuradas (pasos, tareas, tipo de usuario),
     * abre el modal de ajustes. Si no las tiene, dispara confirmación directa.
     */
    public function prepararEliminacion(int $id)
    {
        $this->esAjustePorCambioEstado = false;
        $alumno = User::find($this->alumnoSeleccionadoId);
        if (! $alumno) {
            return;
        }

        if ($this->modo === 'materias') {
            $materia = Materia::with([
                'tipoUsuarioObjetivo',
                'tareasCulminadas.tareaConsolidacion',
                'pasosCrecimiento' => function ($query) {
                    $query->wherePivot('al_iniciar', false);
                },
            ])->find($id);

            if (! $materia) {
                return;
            }

            $this->materiaAEliminar = $materia;
            $this->nivelAEliminar = null;

            $hasPasos = $materia->pasosCrecimiento->isNotEmpty();
            $hasTareas = $materia->tareasCulminadas->isNotEmpty();
            $hasTipoUsuario = ! empty($materia->tipo_usuario_objetivo_id);

            if (! $hasPasos && ! $hasTareas && ! $hasTipoUsuario) {
                $this->dispatch('confirmar-eliminacion-directa', [
                    'id' => $id,
                    'nombre' => $materia->nombre,
                    'tipo' => 'Materia',
                ]);

                return;
            }

            $this->estadosPasosDisponibles = EstadoPasoCrecimientoUsuario::all();
            $this->estadosTareasDisponibles = EstadoTareaConsolidacion::all();
            $this->tiposUsuariosDisponibles = TipoUsuario::all();

            $this->ajustesPasos = [];
            foreach ($materia->pasosCrecimiento as $paso) {
                $crecimientoActual = CrecimientoUsuario::where('user_id', $alumno->id)
                    ->where('paso_crecimiento_id', $paso->id)
                    ->first();

                $this->ajustesPasos[$paso->id] = $crecimientoActual ? $crecimientoActual->estado_id : '';
            }

            $this->ajustesTareas = [];
            foreach ($materia->tareasCulminadas as $tareaConfig) {
                $tareaUsuarioActual = TareaConsolidacionUsuario::where('user_id', $alumno->id)
                    ->where('tarea_consolidacion_id', $tareaConfig->tarea_consolidacion_id)
                    ->first();

                $this->ajustesTareas[$tareaConfig->tarea_consolidacion_id] = $tareaUsuarioActual ? $tareaUsuarioActual->estado_tarea_consolidacion_id : '';
            }

            $this->ajusteTipoUsuarioId = $alumno->tipo_usuario_id;
            $this->showModal = false;
            $this->showModalEliminar = true;
            $this->dispatch('abrir-offcanvas-eliminar');
        } else {
            $nivel = NivelEscuela::with([
                'tipoUsuarioObjetivo',
                'tareasCulminadas.tareaConsolidacion',
                'pasosCrecimiento' => function ($query) {
                    $query->wherePivot('al_iniciar', false);
                },
            ])->find($id);

            if (! $nivel) {
                return;
            }

            $this->nivelAEliminar = $nivel;
            $this->materiaAEliminar = null;

            $hasPasos = $nivel->pasosCrecimiento->isNotEmpty();
            $hasTareas = $nivel->tareasCulminadas->isNotEmpty();
            $hasTipoUsuario = ! empty($nivel->tipo_usuario_objetivo_id);

            if (! $hasPasos && ! $hasTareas && ! $hasTipoUsuario) {
                $this->dispatch('confirmar-eliminacion-directa', [
                    'id' => $id,
                    'nombre' => $nivel->nombre,
                    'tipo' => 'Nivel',
                ]);

                return;
            }

            $this->estadosPasosDisponibles = EstadoPasoCrecimientoUsuario::all();
            $this->estadosTareasDisponibles = EstadoTareaConsolidacion::all();
            $this->tiposUsuariosDisponibles = TipoUsuario::all();

            $this->ajustesPasos = [];
            foreach ($nivel->pasosCrecimiento as $paso) {
                $crecimientoActual = CrecimientoUsuario::where('user_id', $alumno->id)
                    ->where('paso_crecimiento_id', $paso->id)
                    ->first();

                $this->ajustesPasos[$paso->id] = $crecimientoActual ? $crecimientoActual->estado_id : '';
            }

            $this->ajustesTareas = [];
            foreach ($nivel->tareasCulminadas as $tareaConfig) {
                $tareaUsuarioActual = TareaConsolidacionUsuario::where('user_id', $alumno->id)
                    ->where('tarea_consolidacion_id', $tareaConfig->tarea_consolidacion_id)
                    ->first();

                $this->ajustesTareas[$tareaConfig->tarea_consolidacion_id] = $tareaUsuarioActual ? $tareaUsuarioActual->estado_tarea_consolidacion_id : '';
            }

            $this->ajusteTipoUsuarioId = $alumno->tipo_usuario_id;
            $this->showModal = false;
            $this->showModalEliminar = true;
            $this->dispatch('abrir-offcanvas-eliminar');
        }
    }

    /**
     * Elimina la homologación directamente sin mostrar el modal de ajustes.
     */
    public function eliminarHomologacionDirecta(int $id)
    {
        if ($this->modo === 'materias') {
            MateriaAprobadaUsuario::where('user_id', $this->alumnoSeleccionadoId)
                ->where('materia_id', $id)
                ->delete();
        } else {
            NivelAprobadoUsuario::where('user_id', $this->alumnoSeleccionadoId)
                ->where('nivel_id', $id)
                ->delete();
        }

        $this->buscar($this->alumnoSeleccionadoId);
        $this->dispatch('notificacion', ['mensaje' => 'Homologación eliminada con éxito.']);
    }

    /**
     * Ejecuta la eliminación o el ajuste de homologación y aplica los ajustes manuales seleccionados.
     */
    public function ejecutarEliminacionYReversion()
    {
        DB::beginTransaction();
        try {
            $alumnoId = (int) $this->alumnoSeleccionadoId;

            // 1. Guardar cambio de estado o eliminar la homologación
            if ($this->esAjustePorCambioEstado) {
                // Caso Ajuste por cambio de estado (de Aprobado a En proceso o Reprobado)
                if ($this->modo === 'materias' && $this->materiaAEliminar) {
                    MateriaAprobadaUsuario::updateOrCreate(
                        [
                            'user_id' => $alumnoId,
                            'materia_id' => $this->materiaAEliminar->id,
                        ],
                        [
                            'aprobado' => (int) $this->estadoHomologacion,
                            'nota_final' => null,
                            'creditos_aprobados' => null,
                            'es_homologacion' => true,
                            'observacion_homologacion' => $this->observacionHomologacion,
                            'sede_id' => $this->sedeHomologacionId,
                            'fecha_homologacion' => now(),
                            'fecha_homologacion_aprobacion' => null,
                            'homologado_por_user_id' => Auth::id(),
                        ]
                    );
                } elseif ($this->modo === 'niveles' && $this->nivelAEliminar) {
                    NivelAprobadoUsuario::updateOrCreate(
                        [
                            'user_id' => $alumnoId,
                            'nivel_id' => $this->nivelAEliminar->id,
                        ],
                        [
                            'aprobado' => (int) $this->estadoHomologacion,
                            'nota_final' => null,
                            'es_homologacion' => true,
                            'observacion_homologacion' => $this->observacionHomologacion,
                            'sede_id' => $this->sedeHomologacionId,
                            'fecha_homologacion' => now(),
                            'fecha_homologacion_aprobacion' => null,
                            'homologado_por_user_id' => Auth::id(),
                        ]
                    );
                }
            } else {
                // Caso Eliminación directa de la homologación
                if ($this->modo === 'materias' && $this->materiaAEliminar) {
                    MateriaAprobadaUsuario::where('user_id', $alumnoId)
                        ->where('materia_id', $this->materiaAEliminar->id)
                        ->delete();
                } elseif ($this->modo === 'niveles' && $this->nivelAEliminar) {
                    NivelAprobadoUsuario::where('user_id', $alumnoId)
                        ->where('nivel_id', $this->nivelAEliminar->id)
                        ->delete();
                }
            }

            // 2. Ajustar Pasos de Crecimiento
            foreach ($this->ajustesPasos as $pasoId => $estadoId) {
                if (empty($estadoId)) {
                    $crecimientoExistente = CrecimientoUsuario::where('user_id', $alumnoId)
                        ->where('paso_crecimiento_id', $pasoId)
                        ->first();

                    if ($crecimientoExistente) {
                        $crecimientoExistente->delete();
                    }
                } else {
                    $existente = CrecimientoUsuario::where('user_id', $alumnoId)
                        ->where('paso_crecimiento_id', $pasoId)
                        ->first();

                    $detalle = $this->esAjustePorCambioEstado
                        ? 'Ajuste manual por cambio de estado de homologación'
                        : 'Ajuste manual por eliminación de homologación';

                    if ($existente) {
                        $existente->update([
                            'estado_id' => $estadoId,
                            'fecha' => now(),
                            'detalle' => $detalle,
                        ]);
                    } else {
                        CrecimientoUsuario::create([
                            'user_id' => $alumnoId,
                            'paso_crecimiento_id' => $pasoId,
                            'estado_id' => $estadoId,
                            'fecha' => now(),
                            'detalle' => $detalle,
                        ]);
                    }
                }
            }

            // 3. Ajustar Tareas de Consolidación
            foreach ($this->ajustesTareas as $tareaConsolidacionId => $estadoId) {
                if (empty($estadoId)) {
                    $tareaExistente = TareaConsolidacionUsuario::where('user_id', $alumnoId)
                        ->where('tarea_consolidacion_id', $tareaConsolidacionId)
                        ->first();

                    if ($tareaExistente) {
                        // Eliminar dependencias en bitácora e historial para no violar la restricción de clave foránea
                        $tareaExistente->historial()->delete();
                        $tareaExistente->bitacora()->delete();
                        $tareaExistente->delete();
                    }
                } else {
                    $existente = TareaConsolidacionUsuario::where('user_id', $alumnoId)
                        ->where('tarea_consolidacion_id', $tareaConsolidacionId)
                        ->first();

                    if ($existente) {
                        $existente->update([
                            'estado_tarea_consolidacion_id' => $estadoId,
                            'fecha' => now(),
                        ]);
                    } else {
                        TareaConsolidacionUsuario::create([
                            'user_id' => $alumnoId,
                            'tarea_consolidacion_id' => $tareaConsolidacionId,
                            'estado_tarea_consolidacion_id' => $estadoId,
                            'fecha' => now(),
                        ]);
                    }
                }
            }

            // 4. Ajustar Tipo de Usuario / Roles
            if ($this->ajusteTipoUsuarioId) {
                $usuario = User::find($alumnoId);
                if ($usuario && $usuario->tipo_usuario_id != $this->ajusteTipoUsuarioId) {
                    $usuario->promoverTipoUsuario($this->ajusteTipoUsuarioId, forzar: true);
                }
            }

            DB::commit();

            $mensajeExito = $this->esAjustePorCambioEstado
                ? '¡Homologación actualizada y ajustes aplicados con éxito!'
                : 'Homologación eliminada y ajustes aplicados con éxito.';

            $this->showModalEliminar = false;
            $this->reset(['materiaAEliminar', 'nivelAEliminar', 'ajustesPasos', 'ajustesTareas', 'ajusteTipoUsuarioId', 'esAjustePorCambioEstado']);

            $this->buscar($alumnoId);
            $this->dispatch('cerrar-offcanvas-eliminar');
            $this->dispatch('notificacion', ['mensaje' => $mensajeExito]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en ajuste/eliminacion de homologacion: '.$e->getMessage());
            $this->dispatch('notificacion', ['mensaje' => 'Error: '.$e->getMessage(), 'tipo' => 'error']);
        }
    }

    /**
     * Renderiza la vista del componente.
     */
    public function render()
    {
        if ($this->alumnoSeleccionadoId && $this->escuelaSeleccionadaId) {
            if ($this->modo === 'materias') {
                $this->buscarMaterias();
            } else {
                $this->buscarNiveles();
            }
        }

        return view('livewire.homologaciones.gestionar-homologaciones');
    }
}
