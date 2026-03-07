<?php

namespace App\Livewire\Homologaciones;

use Livewire\Component;
use App\Models\Escuela;
use App\Models\User;
use App\Models\Sede;
use App\Models\Materia;
use App\Models\MateriaAprobadaUsuario;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\CrecimientoUsuario;

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
    public ?Materia $materiaParaHomologar = null;
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
     * @param int $alumnoId El ID del usuario seleccionado en el buscador.
     */
    public function buscarMaterias($alumnoId)
    {
        // --- PASO 1: ACTUALIZAR EL ESTADO DEL COMPONENTE ---
        // Asignamos el ID del alumno que recibimos desde la vista a la propiedad del componente.
        // Es importante hacer esto primero para que la validación funcione.
        $this->alumnoSeleccionadoId = $alumnoId;

        // --- PASO 2: VALIDACIÓN ---
        // Validamos que tanto el alumno como la escuela hayan sido seleccionados
        // antes de proceder a realizar consultas costosas a la base de datos.
        $this->validate([
            'alumnoSeleccionadoId' => 'required',
            'escuelaSeleccionadaId' => 'required',
        ], [
            'alumnoSeleccionadoId.required' => 'Debes seleccionar un alumno para poder buscar.',
            'escuelaSeleccionadaId.required' => 'Debes seleccionar una escuela para poder buscar.',
        ]);

        // --- PASO 3: OBTENER LISTA MAESTRA DE MATERIAS ---
        // Consultamos todas las materias que pertenecen a la escuela seleccionada.
        // Esta es nuestra lista de "candidatas" para la homologación.
        $materiasDeEscuela = Materia::where('escuela_id', $this->escuelaSeleccionadaId)
            ->orderBy('nombre')->get();

        // --- PASO 4: OBTENER HISTORIAL DEL ALUMNO ---
        // Hacemos una consulta eficiente para obtener solo los IDs de las materias
        // que el alumno ya tiene en su historial (sea por aprobación o por homologación).
        $historialIds = MateriaAprobadaUsuario::where('user_id', $this->alumnoSeleccionadoId)
            ->pluck('materia_id')->toArray();

        // --- PASO 5: COMPARAR Y ASIGNAR ESTADO ---
        // Usamos el método map() de las colecciones de Laravel para recorrer la lista de materias
        // y añadirles una nueva propiedad 'estado' basada en la comparación con el historial.
        $this->materias = $materiasDeEscuela->map(function ($materia) use ($historialIds) {
            // La función in_array() comprueba si el ID de la materia actual está en el historial.
            $materia->estado = in_array($materia->id, $historialIds)
                ? '1'
                : '0';
            return $materia;
        });
    }

    /**
     * Prepara y abre el modal para realizar una homologación.
     *
     * @param int $materiaId El ID de la materia que se va a homologar.
     */
    public function abrirModalHomologacion(int $materiaId)
    {
        $this->materiaParaHomologar = Materia::find($materiaId);
        $this->reset(['sedeHomologacionId', 'observacionHomologacion']);
        $this->resetErrorBag(); // Limpia errores de validación anteriores.
        $this->showModal = true;
    }

    /**
     * Valida y guarda el nuevo registro de homologación en la base de datos.
     */


    public function guardarHomologacion()
    {
        $this->validate([
            'sedeHomologacionId' => 'required|exists:sedes,id',
            'observacionHomologacion' => 'required|string|min:10',
        ]);

        // --- 1. INICIAMOS UNA TRANSACCIÓN DE BASE DE DATOS ---
        // Esto asegura que todas las operaciones (crear homologación y crear paso de crecimiento)
        // se completen con éxito, o ninguna de ellas se aplique.
        DB::beginTransaction();

        try {
            // --- 2. CREAR EL REGISTRO DE HOMOLOGACIÓN (Lógica existente) ---
            MateriaAprobadaUsuario::create([
                'user_id' => $this->alumnoSeleccionadoId,
                'materia_id' => $this->materiaParaHomologar->id,
                'aprobado' => true,
                'materia_periodo_id' => null,
                'periodo_id' => null,
                'es_homologacion' => true,
                'observacion_homologacion' => $this->observacionHomologacion,
                'sede_id' => $this->sedeHomologacionId,
                'fecha_homologacion' => now(),
                'homologado_por_user_id' => Auth::id(),
            ]);

            // --- 3. BUSCAR EL "PASO DE CRECIMIENTO A CULMINAR" ASOCIADO A LA MATERIA ---
            // Usamos la relación 'pasosCrecimiento' que definiste en tu modelo Materia.
            // Asumimos que 'al_iniciar' = false significa que es un paso de culminación.
            $pasoACulminar = $this->materiaParaHomologar->pasosCrecimiento()
                ->wherePivot('al_iniciar', false)
                ->first();

            // --- 4. SI SE ENCUENTRA UN PASO, CREAR/ACTUALIZAR EL REGISTRO EN 'crecimiento_usuario' ---
            if ($pasoACulminar) {
                // Asumimos que el estado "Finalizado" tiene el ID 3.
                // Sería ideal tener esto en una configuración o constante.
                $estadoFinalizadoId = 3;
                $detalleHomologacion = 'Aprobado por homologación de la materia: ' . $this->materiaParaHomologar->nombre;

                // Usamos updateOrCreate para evitar duplicados.
                // Si el alumno ya tenía este paso iniciado, lo actualiza a 'Finalizado'.
                // Si no lo tenía, lo crea.
                CrecimientoUsuario::updateOrCreate(
                    [
                        'user_id' => $this->alumnoSeleccionadoId,
                        'paso_crecimiento_id' => $pasoACulminar->id,
                    ],
                    [
                        'estado_id' => $estadoFinalizadoId,
                        'fecha' => now(),
                        'detalle' => $detalleHomologacion,
                    ]
                );

                Log::info("Paso de crecimiento ID {$pasoACulminar->id} completado por homologación para el usuario ID {$this->alumnoSeleccionadoId}.");
            }

            // --- 5. SI TODO SALIÓ BIEN, CONFIRMAMOS LA TRANSACCIÓN ---
            DB::commit();

            // --- 6. CERRAMOS MODAL, RECARGAMOS Y NOTIFICAMOS (Lógica existente) ---
            $this->showModal = false;
            $this->buscarMaterias($this->alumnoSeleccionadoId);
            $this->dispatch('notificacion', ['mensaje' => '¡Homologación y paso de crecimiento creados con éxito!']);
        } catch (\Exception $e) {
            // Si algo falla, revertimos todos los cambios en la base de datos.
            DB::rollBack();

            Log::error("Error al crear homologación y/o paso de crecimiento: " . $e->getMessage());
            $mensajeError = app()->environment('local')
                ? 'Error: ' . $e->getMessage()
                : 'Ocurrió un error inesperado.';

            $this->dispatch('notificacion', ['mensaje' => $mensajeError, 'tipo' => 'error']);
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
