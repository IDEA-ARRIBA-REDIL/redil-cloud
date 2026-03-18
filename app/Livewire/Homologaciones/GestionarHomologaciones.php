<?php

namespace App\Livewire\Homologaciones;

use App\Models\CrecimientoUsuario;
use App\Models\Escuela;
use App\Models\Materia;
use App\Models\MateriaAprobadaUsuario;
use App\Models\NivelAprobadoUsuario;
use App\Models\NivelEscuela;
use App\Models\Sede;
use App\Models\TareaConsolidacionUsuario;
use App\Models\User;
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

    // Almacena la lista de materias que se muestra después de la búsqueda.
    public $materias = [];

    // Propiedades para controlar el modal de homologación.
    public $showModal = false;

    public $modo = 'materias'; // 'materias' o 'niveles'

    public ?Materia $materiaParaHomologar = null;

    public ?NivelEscuela $nivelParaHomologar = null;

    public $sedeHomologacionId;

    public $observacionHomologacion;

    // Reglas de validación para los formularios.
    protected $rules = [
        'sedeHomologacionId' => 'required|exists:sedes,id',
        'observacionHomologacion' => 'required|string|min:10',
        'alumnoSeleccionadoId' => 'required',
        'escuelaSeleccionadaId' => 'required',
    ];

    // Mensajes de error personalizados para una mejor experiencia de usuario.
    protected $messages = [
        'sedeHomologacionId.required' => 'Debes seleccionar una sede.',
        'observacionHomologacion.required' => 'La observación es obligatoria.',
        'alumnoSeleccionadoId.required' => 'Debes seleccionar un alumno.',
        'escuelaSeleccionadaId.required' => 'Debes seleccionar una escuela.',
    ];

    /**
     * Se ejecuta una sola vez cuando el componente se carga por primera vez.
     * Prepara los datos iniciales necesarios para la vista.
     */
    public function mount()
    {
        $this->escuelas = Escuela::orderBy('nombre')->get();
        $this->sedes = Sede::orderBy('nombre')->get();
        $this->sedeHomologacionId;
        $this->observacionHomologacion;
    }

    /**
     * Se ejecuta al hacer clic en el botón "Buscar Materias".
     * Recibe el ID del alumno seleccionado desde la vista.
     *
     * @param  int  $alumnoId  El ID del usuario seleccionado en el buscador.
     */
    /**
     * Se ejecuta al hacer clic en el botón "Buscar".
     * Recibe el ID del alumno seleccionado desde la vista.
     *
     * @param  int  $alumnoId  El ID del usuario seleccionado en el buscador.
     */
    public function buscar($alumnoId)
    {
        // --- PASO 1: ACTUALIZAR EL ESTADO DEL COMPONENTE ---
        $this->alumnoSeleccionadoId = $alumnoId;

        // --- PASO 2: VALIDACIÓN ---
        $this->validate([
            'alumnoSeleccionadoId' => 'required',
            'escuelaSeleccionadaId' => 'required',
        ], [
            'alumnoSeleccionadoId.required' => 'Debes seleccionar un alumno para poder buscar.',
            'escuelaSeleccionadaId.required' => 'Debes seleccionar una escuela para poder buscar.',
        ]);

        if ($this->modo === 'materias') {
            $this->buscarMaterias();
        } else {
            $this->buscarNiveles();
        }
    }

    /**
     * Busca las materias de la escuela seleccionada.
     */
    public function buscarMaterias()
    {
        // --- PASO 3: OBTENER LISTA MAESTRA DE MATERIAS ---
        $materiasDeEscuela = Materia::where('escuela_id', $this->escuelaSeleccionadaId)
            ->orderBy('nombre')->get();

        // --- PASO 4: OBTENER HISTORIAL DEL ALUMNO ---
        $historialIds = MateriaAprobadaUsuario::where('user_id', $this->alumnoSeleccionadoId)
            ->pluck('materia_id')->toArray();

        // --- PASO 5: COMPARAR Y ASIGNAR ESTADO ---
        $this->materias = $materiasDeEscuela->map(function ($materia) use ($historialIds) {
            $materia->estado = in_array($materia->id, $historialIds) ? '1' : '0';

            return $materia;
        });
    }

    /**
     * Busca los niveles de la escuela seleccionada.
     */
    public function buscarNiveles()
    {
        // --- PASO 3: OBTENER LISTA MAESTRA DE NIVELES ---
        $nivelesDeEscuela = NivelEscuela::where('escuela_id', $this->escuelaSeleccionadaId)
            ->orderBy('orden')->get();

        // --- PASO 4: OBTENER HISTORIAL DEL ALUMNO ---
        $historialIds = NivelAprobadoUsuario::where('user_id', $this->alumnoSeleccionadoId)
            ->pluck('nivel_id')->toArray();

        // --- PASO 5: COMPARAR Y ASIGNAR ESTADO ---
        $this->materias = $nivelesDeEscuela->map(function ($nivel) use ($historialIds) {
            $nivel->estado = in_array($nivel->id, $historialIds) ? '1' : '0';
            // Sobrescribimos 'nombre' para que la vista genérica funcione
            $nivel->materia_nombre = $nivel->nombre;

            return $nivel;
        });
    }

    /**
     * Prepara y abre el modal para realizar una homologación.
     *
     * @param  int  $id  El ID de la materia o nivel que se va a homologar.
     */
    public function abrirModalHomologacion(int $id)
    {
        if ($this->modo === 'materias') {
            $this->materiaParaHomologar = Materia::find($id);
            $this->nivelParaHomologar = null;
        } else {
            $this->nivelParaHomologar = NivelEscuela::find($id);
            $this->materiaParaHomologar = null;
        }

        $this->reset(['sedeHomologacionId', 'observacionHomologacion']);
        $this->resetErrorBag();
        $this->showModal = true;
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
        $this->validate([
            'sedeHomologacionId' => 'required|exists:sedes,id',
            'observacionHomologacion' => 'required|string|min:10',
        ]);

        DB::beginTransaction();
        try {
            MateriaAprobadaUsuario::create([
                'user_id' => $this->alumnoSeleccionadoId,
                'materia_id' => $this->materiaParaHomologar->id,
                'aprobado' => true,
                'es_homologacion' => true,
                'observacion_homologacion' => $this->observacionHomologacion,
                'sede_id' => $this->sedeHomologacionId,
                'fecha_homologacion' => now(),
                'homologado_por_user_id' => Auth::id(),
            ]);

            $pasoACulminar = $this->materiaParaHomologar->pasosCrecimiento()
                ->wherePivot('al_iniciar', false)
                ->first();

            if ($pasoACulminar) {
                CrecimientoUsuario::updateOrCreate(
                    ['user_id' => $this->alumnoSeleccionadoId, 'paso_crecimiento_id' => $pasoACulminar->id],
                    ['estado_id' => 3, 'fecha' => now(), 'detalle' => 'Aprobado por homologación de la materia: '.$this->materiaParaHomologar->nombre]
                );
            }

            DB::commit();
            $this->showModal = false;
            $this->buscar($this->alumnoSeleccionadoId);
            $this->dispatch('notificacion', ['mensaje' => '¡Materia homologada con éxito!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error materia homologacion: '.$e->getMessage());
            $this->dispatch('notificacion', ['mensaje' => 'Error: '.$e->getMessage(), 'tipo' => 'error']);
        }
    }

    public function guardarHomologacionNivel()
    {
        $this->validate([
            'sedeHomologacionId' => 'required|exists:sedes,id',
            'observacionHomologacion' => 'required|string|min:10',
        ]);

        DB::beginTransaction();
        try {
            // 1. Registrar Nivel Aprobado
            NivelAprobadoUsuario::create([
                'user_id' => $this->alumnoSeleccionadoId,
                'nivel_id' => $this->nivelParaHomologar->id,
                'aprobado' => true,
                'es_homologacion' => true,
                'observacion_homologacion' => $this->observacionHomologacion,
                'sede_id' => $this->sedeHomologacionId,
                'fecha_homologacion' => now(),
                'homologado_por_user_id' => Auth::id(),
            ]);

            // 2. Culminar Pasos de Crecimiento del Nivel
            $pasosACulminar = $this->nivelParaHomologar->pasosCrecimiento()
                ->wherePivot('al_iniciar', false)
                ->get();

            foreach ($pasosACulminar as $paso) {
                CrecimientoUsuario::updateOrCreate(
                    ['user_id' => $this->alumnoSeleccionadoId, 'paso_crecimiento_id' => $paso->id],
                    ['estado_id' => 3, 'fecha' => now(), 'detalle' => 'Homologación de Nivel: '.$this->nivelParaHomologar->nombre]
                );
            }

            // 3. Culminar Tareas del Nivel
            $tareasACulminar = $this->nivelParaHomologar->tareasCulminadas;
            foreach ($tareasACulminar as $tareaNivel) {
                TareaConsolidacionUsuario::updateOrCreate(
                    ['user_id' => $this->alumnoSeleccionadoId, 'tarea_consolidacion_id' => $tareaNivel->tarea_consolidacion_id],
                    ['estado_tarea_consolidacion_id' => 3, 'fecha' => now()]
                );
            }

            // 4. Actualizar Tipo de Usuario si aplica
            if ($this->nivelParaHomologar->tipo_usuario_objetivo_id) {
                $user = User::find($this->alumnoSeleccionadoId);
                $user->tipo_usuario_id = $this->nivelParaHomologar->tipo_usuario_objetivo_id;
                $user->save();
            }

            DB::commit();
            $this->showModal = false;
            $this->buscar($this->alumnoSeleccionadoId);
            $this->dispatch('notificacion', ['mensaje' => '¡Nivel homologado con éxito!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error nivel homologacion: '.$e->getMessage());
            $this->dispatch('notificacion', ['mensaje' => 'Error: '.$e->getMessage(), 'tipo' => 'error']);
        }
    }

    /**
     * Renderiza la vista del componente.
     */
    public function render()
    {
        return view('livewire.homologaciones.gestionar-homologaciones');
    }
}
