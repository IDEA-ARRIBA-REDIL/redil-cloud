<?php

namespace App\Livewire\Maestros;

use Livewire\Component;
use App\Models\HorarioMateriaPeriodo;
use App\Models\User;
use App\Models\Configuracion;
use App\Models\Periodo;
use App\Models\MatriculaHorarioMateriaPeriodo as EstadoAcademico;
use App\Models\CortePeriodo;
use App\Models\ItemCorteMateriaPeriodo;
use App\Models\AlumnoRespuestaItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Carbon\Carbon; // Necesario para comparar fechas

class CalificacionMultipleAlumnos extends Component
{
    // --- PROPIEDADES PÚBLICAS (ESTADO DEL COMPONENTE) ---
    public HorarioMateriaPeriodo $horarioAsignado;
    public Collection $alumnosConEstado;
    public string $busquedaAlumno = '';
    public $configuracion; // Necesaria para las reglas de validación y la vista
    public $periodo;
    public array $estructuraItemsPorAlumno = [];
    public array $notas = [];
    public array $calificacionesDelAlumno = [];
    public array $notasCalculadasPorCorte = [];
    public array $promedioGeneralMateria = [];
    public ?int $alumnoActivoIdParaCalificaciones = null;
    public array $activeTabId = [];
    public string $observacionMaestro = '';
    public Carbon $fechaActual;
    public $rolActivo;

    // Propiedades para el modal "Ver Respuesta"
    public bool $showRespuestaModal = false;
    public ?AlumnoRespuestaItem $respuestaSeleccionada = null;
    public array $cortesPeriodoInfo = [];
    public $puedeCalificarSinFecha;

    // --- MÉTODOS DE CICLO DE VIDA Y CONFIGURACIÓN ---

    protected function rules()
    {
        $rules = [];
        if ($this->configuracion) {
            foreach ($this->notas as $userId => $items) {
                foreach ($items as $itemId => $nota) {
                    $rules["notas.{$userId}.{$itemId}"] = ['nullable', 'numeric', 'min:' . ($this->configuracion->nota_minima ?? 0), 'max:' . ($this->configuracion->nota_maxima ?? 5)];
                }
            }
        }
        return $rules;
    }

    protected $messages = [
        'notas.*.*.numeric' => 'Debe ser un número.',
        'notas.*.*.min' => 'Nota muy baja.',
        'notas.*.*.max' => 'Nota muy alta.',
    ];

    // --- MÉTODO mount() (NO SE HA ELIMINADO) ---
    // Este método es crucial y se ejecuta una sola vez al cargar el componente.
    public function mount(HorarioMateriaPeriodo $horarioAsignado)
    {
        $user = Auth::user();
        $this->rolActivo = $user ? $user->roles()->wherePivot('activo', true)->first() : null;
        $this->puedeCalificarSinFecha = $this->rolActivo ? $this->rolActivo->hasPermissionTo('escuelas.calificar_cualquier_fecha') : false;
        $this->configuracion = Configuracion::find(1);
        $this->fechaActual = Carbon::now()->startOfDay();
        $this->horarioAsignado = $horarioAsignado->load([
            'materiaPeriodo.materia:id,nombre',
            'materiaPeriodo.periodo:id,nombre,escuela_id',
        ]);

        $this->periodo = $this->horarioAsignado->materiaPeriodo->periodo;
    }

    // --- LÓGICA PRINCIPAL ---

    public function cargarEstructuraCalificacion(int $userId)
    {
        if (!empty($this->estructuraItemsPorAlumno[$userId])) return;

        $this->alumnoActivoIdParaCalificaciones = $userId;
        $periodoId = $this->horarioAsignado->materiaPeriodo->periodo_id;


        // 1. Cargamos los cortes con sus ítems Y extraemos info de fechas
        $cortesDelPeriodo = CortePeriodo::where('periodo_id', $periodoId)
            ->with([
                'itemInstancias' => fn($query) => $query->where('horario_materia_periodo_id', $this->horarioAsignado->id)->orderBy('orden', 'asc'),
                'corteEscuela:id,nombre,orden'
            ])
            ->join('cortes_escuela', 'cortes_periodo.corte_escuela_id', '=', 'cortes_escuela.id')
            ->orderBy('cortes_escuela.orden', 'asc')
            ->select('cortes_periodo.*')
            ->get();

        if ($cortesDelPeriodo->isEmpty()) return;

        // ----> NUEVO/MODIFICADO: Guarda la información de fecha_fin de los cortes <----
        // Hacemos esto una sola vez para eficiencia
        if (empty($this->cortesPeriodoInfo)) {
            $this->cortesPeriodoInfo = $cortesDelPeriodo->mapWithKeys(function ($corteP) {
                return [$corteP->id => [
                    'fecha_fin' => $corteP->fecha_fin ? Carbon::parse($corteP->fecha_fin)->endOfDay() : null
                ]];
            })->toArray();
        }

        // 2. Cargamos las respuestas del alumno.
        $idsDeTodosLosItems = $cortesDelPeriodo->pluck('itemInstancias')->flatten()->pluck('id');
        $respuestasDeEsteAlumno = AlumnoRespuestaItem::where('user_id', $userId)
            ->whereIn('item_corte_materia_periodo_id', $idsDeTodosLosItems)
            ->get()->keyBy('item_corte_materia_periodo_id');

        $this->calificacionesDelAlumno[$userId] = $respuestasDeEsteAlumno;

        // 3. Construimos la estructura para la vista.
        $estructuraTemporal = [];
        foreach ($cortesDelPeriodo as $corte) {
            foreach ($corte->itemInstancias as $item) {
                $respuesta = $respuestasDeEsteAlumno->get($item->id);
                $item->respuestaDelAlumno = $respuesta;
                $this->notas[$userId][$item->id] = $respuesta ? (string)$respuesta->nota_obtenida : '';
            }
            // --- CORRECCIÓN CLAVE ---
            // Ahora la clave del array es 'itemInstancias', coincidiendo con el modelo y la vista.
            $estructuraTemporal[$corte->id] = [
                'corte_obj' => $corte,
                'corte_nombre_original' => $corte->corteEscuela?->nombre ?? 'Corte',
                'itemInstancias' => $corte->itemInstancias ?? collect(),
            ];
        }
        $this->estructuraItemsPorAlumno[$userId] = $estructuraTemporal;

        if (!isset($this->activeTabId[$userId])) {
            $this->activeTabId[$userId] = $cortesDelPeriodo->first()->id;
        }
        $this->recalcularTodasLasNotas($userId);
    }

    /**
     * Recalcula los promedios de un alumno.
     * VERSIÓN CORREGIDA Y CONSISTENTE.
     */
    public function recalcularTodasLasNotas(int $userId)
    {
        if (!isset($this->estructuraItemsPorAlumno[$userId])) return;

        $promedioGeneralCalculado = 0.0;
        foreach ($this->estructuraItemsPorAlumno[$userId] as $corteId => $dataCorte) {
            $notaAcumuladaDelCorte = 0.0;

            // --- CORRECCIÓN CLAVE ---
            // Usamos la clave correcta 'itemInstancias' para la comprobación y el bucle.
            if (!empty($dataCorte['itemInstancias'])) {
                foreach ($dataCorte['itemInstancias'] as $item) {
                    $notaItem = isset($this->notas[$userId][$item->id]) && $this->notas[$userId][$item->id] !== ''
                        ? (float) $this->notas[$userId][$item->id] : null;
                    if ($notaItem !== null && is_numeric($item->porcentaje)) {
                        $notaAcumuladaDelCorte += $notaItem * ($item->porcentaje / 100);
                    }
                }
            }
            $notaFinalCorte = $notaAcumuladaDelCorte;
            $this->notasCalculadasPorCorte[$userId][$corteId] = round($notaFinalCorte, 2);

            $porcentajeDelCorte = (float)($dataCorte['corte_obj']->porcentaje ?? 0);
            if ($porcentajeDelCorte > 0) {
                $promedioGeneralCalculado += $notaFinalCorte * ($porcentajeDelCorte / 100);
            }
        }
        $this->promedioGeneralMateria[$userId] = round($promedioGeneralCalculado, 2);
    }

    /**
     * Se dispara automáticamente cuando se modifica una nota en la vista.
     */
    public function updatedNotas($notaIngresada, $key)
    {
        $keys = explode('.', $key);
        if (count($keys) === 2 && is_numeric($keys[0]) && is_numeric($keys[1])) {
            $this->guardarCalificacionCompleta($keys[0], $keys[1]);
        }
    }

    /**
     * Guarda una calificación individual en la base de datos.
     */
    public function guardarCalificacionCompleta(int $userId, int $itemId)
    {
        $this->validateOnly("notas.{$userId}.{$itemId}");
        $nota = $this->notas[$userId][$itemId];
        $notaAjustada = ($nota === '' || $nota === null) ? null : (float) str_replace(',', '.', $nota);

        AlumnoRespuestaItem::updateOrCreate(
            ['user_id' => $userId, 'item_corte_materia_periodo_id' => $itemId],
            ['nota_obtenida' => $notaAjustada, 'calificador_user_id' => Auth::id(), 'fecha_calificacion' => now()]
        );

        $this->recalcularTodasLasNotas($userId);
        $this->dispatch('mostrarExitoConSweetAlert', ['texto' => 'Nota guardada.']);
    }

    public function guardarCalificacionIndividual(int $userId, int $itemId, $notaIngresada)
    {
        // Encuentra el corte al que pertenece el ítem
        $corteId = null;
        foreach ($this->estructuraItemsPorAlumno[$userId] ?? [] as $cId => $dataCorte) {
            if ($dataCorte['itemInstancias']->contains('id', $itemId)) {
                $corteId = $cId;
                break;
            }
        }

        // --- INICIO VALIDACIÓN DE FECHA ---
        if (!$this->puedeCalificarSinFecha && $corteId) {
            $corteInfo = $this->cortesPeriodoInfo[$corteId] ?? null;
            $fechaFinCorte = $corteInfo['fecha_fin'] ?? null;

            if ($fechaFinCorte && $this->fechaActual->gt($fechaFinCorte)) {
                $this->dispatch('mostrarErrorConSweetAlert', ['texto' => 'El plazo para calificar este ítem ha vencido.']);
                // Revertir visualmente el valor en el input
                $originalValue = AlumnoRespuestaItem::where('user_id', $userId)->where('item_corte_materia_periodo_id', $itemId)->value('nota_obtenida');
                $this->notas[$userId][$itemId] = $originalValue !== null ? (string)$originalValue : '';
                return; // Detiene la ejecución
            }
        }
        // --- FIN VALIDACIÓN DE FECHA ---

        // Valida solo la nota específica que cambió
        $this->validateOnly("notas.{$userId}.{$itemId}");

        // Prepara la nota para guardar
        $notaAjustada = ($notaIngresada === '' || $notaIngresada === null) ? null : (float) str_replace(',', '.', $notaIngresada);

        // Guarda o actualiza en la base de datos
        try {
            AlumnoRespuestaItem::updateOrCreate(
                ['user_id' => $userId, 'item_corte_materia_periodo_id' => $itemId],
                ['nota_obtenida' => $notaAjustada, 'calificador_user_id' => Auth::id(), 'fecha_calificacion' => now()]
            );

            // Actualiza la respuesta en la colección en memoria
            $respuesta = $this->calificacionesDelAlumno[$userId]->get($itemId);
            if ($respuesta) {
                $respuesta->nota_obtenida = $notaAjustada;
            } else {
                $nuevaRespuesta = new AlumnoRespuestaItem(['user_id' => $userId, 'item_corte_materia_periodo_id' => $itemId, 'nota_obtenida' => $notaAjustada]);
                $this->calificacionesDelAlumno[$userId]->put($itemId, $nuevaRespuesta);
            }

            $this->recalcularTodasLasNotas($userId);
            // $this->dispatch('mostrarExitoConSweetAlert', ['texto' => 'Nota guardada.']); // Opcional: Feedback
        } catch (\Exception $e) {
            $this->dispatch('mostrarErrorConSweetAlert', ['texto' => 'Error al guardar la nota. Intenta de nuevo.']);
            Log::error("Error guardando calificación individual (U:{$userId}, I:{$itemId}): " . $e->getMessage());
            // Revertir visualmente si falla el guardado
            $originalValue = AlumnoRespuestaItem::where('user_id', $userId)->where('item_corte_materia_periodo_id', $itemId)->value('nota_obtenida');
            $this->notas[$userId][$itemId] = $originalValue !== null ? (string)$originalValue : '';
        }
    }



    /**
     * Prepara el modal para mostrar la respuesta de un alumno.
     */
    public function verRespuesta(int $userId, int $itemId)
    {
        $respuesta = AlumnoRespuestaItem::where('user_id', $userId)
            ->where('item_corte_materia_periodo_id', $itemId)
            ->with(['alumno:id,primer_nombre,primer_apellido', 'itemCalificado:id,nombre'])
            ->first();

        if ($respuesta) {
            $this->respuestaSeleccionada = $respuesta;

            // --- CAMBIO: Cargamos la observación existente en nuestra nueva propiedad ---
            $this->observacionMaestro = $respuesta->observaciones_maestro ?? '';

            $this->showRespuestaModal = true;
        } else {
            $this->dispatch('mostrarError', ['texto' => 'No se encontró la respuesta para este ítem.']);
        }
    }

    // --- INICIO: MÉTODO NUEVO PARA GUARDAR ---
    /**
     * Guarda o actualiza la observación del maestro para la respuesta seleccionada.
     */
    public function guardarObservacion()
    {
        // 1. Verificación: Nos aseguramos de que haya una respuesta seleccionada.
        if (!$this->respuestaSeleccionada) {
            $this->dispatch('mostrarError', ['texto' => 'No hay una respuesta seleccionada para guardar la observación.']);
            return;
        }

        // 2. Validación (opcional pero recomendado)
        $this->validate([
            'observacionMaestro' => 'nullable|string|max:5000'
        ]);

        // 3. Actualizamos el registro en la base de datos.
        $this->respuestaSeleccionada->update([
            'observaciones_maestro' => $this->observacionMaestro
        ]);

        // 4. Cerramos el modal.
        $this->showRespuestaModal = false;

        // 5. Enviamos una notificación de éxito.
        $this->dispatch('mostrarExitoConSweetAlert', ['texto' => 'Observación guardada correctamente.']);
    }

    public function setActiveTab($userId, $cortePeriodoId)
    {
        $this->activeTabId[$userId] = $cortePeriodoId;
    }
    public function isTabActive($userId, $cortePeriodoId, $isFirst)
    {
        return isset($this->activeTabId[$userId]) ? $this->activeTabId[$userId] == $cortePeriodoId : $isFirst;
    }

    /**
     * Renderiza la vista del componente y busca los alumnos.
     */
    public function render()
    {
        $query = EstadoAcademico::query()
            ->where('horario_materia_periodo_id', $this->horarioAsignado->id)
            ->with(['user:id,primer_nombre,segundo_nombre,primer_apellido,segundo_apellido,identificacion,email,telefono_movil,sede_id,foto'])
            ->join('users', 'matricula_horario_materia_periodo.user_id', '=', 'users.id')
            ->select('matricula_horario_materia_periodo.*');

        if (!empty(trim($this->busquedaAlumno))) {
            $terminoBusqueda = '%' . trim($this->busquedaAlumno) . '%';
            $query->whereHas('user', function ($q_user_search) use ($terminoBusqueda) {
                $q_user_search->where(DB::raw("CONCAT(users.primer_nombre, ' ', users.primer_apellido)"), 'ilike', $terminoBusqueda)
                    ->orWhere('users.identificacion', 'like', $terminoBusqueda)
                    ->orWhere('users.email', 'like', $terminoBusqueda);
            });
        }

        $this->alumnosConEstado = $query->orderBy('users.primer_apellido', 'asc')
            ->orderBy('users.primer_nombre', 'asc')
            ->get();

        // --- INICIO DE LA LÍNEA AÑADIDA ---
        // Si hay un acordeón de alumno abierto, nos aseguramos de que sus datos
        // estén "rehidratados" con las respuestas antes de renderizar la vista.
        if ($this->alumnoActivoIdParaCalificaciones) {
            $this->rehidratarRespuestas($this->alumnoActivoIdParaCalificaciones);
        }
        // --- FIN DE LA LÍNEA AÑADIDA ---

        return view('livewire.maestros.calificacion-multiple-alumnos');
    }

    private function rehidratarRespuestas(int $userId)
    {
        // Si no tenemos la estructura o las calificaciones para este alumno, no hay nada que hacer.
        if (empty($this->estructuraItemsPorAlumno[$userId]) || empty($this->calificacionesDelAlumno[$userId])) {
            return;
        }

        // Obtenemos la colección de respuestas que ya habíamos guardado.
        $respuestasDeEsteAlumno = $this->calificacionesDelAlumno[$userId];

        // Recorremos la estructura que ya está en memoria...
        foreach ($this->estructuraItemsPorAlumno[$userId] as $corteId => $dataCorte) {
            if (!empty($dataCorte['itemInstancias'])) {
                // ...y volvemos a añadir la propiedad 'respuestaDelAlumno' a cada ítem.
                foreach ($dataCorte['itemInstancias'] as $item) {
                    $item->respuestaDelAlumno = $respuestasDeEsteAlumno->get($item->id);
                }
            }
        }
    }
}
