<?php

namespace App\Livewire\Alumno;

use Livewire\Component;
use Livewire\WithFileUploads; // Importante para la subida de archivos
use App\Models\HorarioMateriaPeriodo;
use App\Models\CortePeriodo;
use App\Models\Configuracion;
use App\Models\Calificaciones;
use App\Models\AlumnoRespuestaItem;
use App\Models\ItemCorteMateriaPeriodo;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log; // Importar Log para errores

class CalificacionesAlumno extends Component
{
    use WithFileUploads;

    public HorarioMateriaPeriodo $horario;
    public $alumno;
    public bool $showCreateModal = false;
    public bool $showEditModal = false;
    public ?ItemCorteMateriaPeriodo $selectedItem = null;
    public ?AlumnoRespuestaItem $existingResponse = null;
    public string $respuestaTexto = '';
    public $archivo;

    // Reglas de validación para el formulario
    protected $rules = [
        'respuestaTexto' => 'required|string|min:10',
        'archivo' => 'nullable|file|mimes:pdf,docx,xlsx,pptx|max:10240'
    ];

    /**
     * Se ejecuta cuando el componente se inicializa.
     */
    public function mount(HorarioMateriaPeriodo $horario)
    {
        $this->horario = $horario;
        $this->alumno = Auth::user();
    }

    // --- NUEVOS MÉTODOS QUE ESCUCHAN EVENTOS ---

    #[On('openCreateModal')]
    public function openCreateModal(int $itemId)
    {
        $this->selectedItem = ItemCorteMateriaPeriodo::find($itemId);
        $this->reset(['respuestaTexto', 'archivo', 'existingResponse']);
        $this->resetErrorBag();
        $this->showEditModal = false;
        $this->showCreateModal = true;
    }

    #[On('openEditModal')]
    public function openEditModal(int $itemId)
    {
        $this->selectedItem = ItemCorteMateriaPeriodo::find($itemId);
        $this->existingResponse = AlumnoRespuestaItem::where('user_id', $this->alumno->id)
            ->where('item_corte_materia_periodo_id', $itemId)
            ->first();

        if ($this->existingResponse) {
            $this->respuestaTexto = $this->existingResponse->respuesta_alumno;
        }

        $this->reset(['archivo']);
        $this->resetErrorBag();
        $this->showCreateModal = false;
        $this->showEditModal = true;
    }

    // --- MÉTODOS DE GUARDADO SEPARADOS ---

    /**
     * Guarda la nueva respuesta del alumno.
     */
    public function crearRespuesta()
    {
        $this->validate();

        // 1. Creamos el registro inicial sin el archivo.
        $respuesta = AlumnoRespuestaItem::create([
            'user_id' => $this->alumno->id,
            'item_corte_materia_periodo_id' => $this->selectedItem->id,
            'respuesta_alumno' => $this->respuestaTexto,
            'enlace_documento_alumno' => null, // Se deja nulo por ahora
        ]);

        // 2. Si hay un archivo para subir, lo procesamos.
        if ($this->archivo) {
            $nombreArchivo = $this->_subirArchivo($respuesta);

            // 3. Actualizamos el registro con el nombre del archivo.
            $respuesta->update(['enlace_documento_alumno' => $nombreArchivo]);
        }

        $this->showCreateModal = false;
        $this->dispatch('notificacion', ['mensaje' => '¡Respuesta guardada con éxito!']);
    }

    /**
     * Actualiza una respuesta existente.
     */
    public function editarRespuesta()
    {
        $this->validate();
        $nombreArchivo = $this->existingResponse->enlace_documento_alumno;

        if ($this->archivo) {
            $nombreArchivo = $this->_subirArchivo($this->existingResponse);
        }

        $this->existingResponse->update([
            'respuesta_alumno' => $this->respuestaTexto,
            'enlace_documento_alumno' => $nombreArchivo,
        ]);

        $this->showEditModal = false;
        $this->dispatch('notificacion', ['texto' => '¡Respuesta actualizada con éxito!']);
    }

    public function eliminarArchivo()
    {
        // Verificamos que tengamos una respuesta cargada para trabajar
        if (!$this->existingResponse || !$this->existingResponse->enlace_documento_alumno) {
            return;
        }

        // 1. Reconstruimos la ruta completa del archivo para poder borrarlo
        $configuracion = Configuracion::find(1);
        $periodoId = $this->horario->materiaPeriodo->periodo_id;
        $directorio = $configuracion->ruta_almacenamiento . "/archivos/periodo-{$periodoId}";
        $rutaCompleta = $directorio . '/' . $this->existingResponse->enlace_documento_alumno;

        // 2. Eliminamos el archivo físico del almacenamiento
        Storage::disk('public')->delete($rutaCompleta);

        // 3. Limpiamos la columna en la base de datos
        $this->existingResponse->update(['enlace_documento_alumno' => null]);

        // 4. Refrescamos los datos del modelo desde la BD
        // Esto es clave para que Livewire re-renderice la vista y muestre el input para subir archivo.
        $this->existingResponse->refresh();

        $this->dispatch('notificacion', ['mensaje' => 'Archivo eliminado con éxito.']);
    }

    /**
     * Renderiza la vista del componente.
     */
    public function render()
    {
        $periodo = $this->horario->materiaPeriodo->periodo;

        // --- CORRECCIÓN ---
        // Aseguramos que se carga la relación con el nombre 'itemInstancias'
        $cortes = CortePeriodo::where('periodo_id', $periodo->id)
            ->with(['itemInstancias' => fn($query) => $query->where('horario_materia_periodo_id', $this->horario->id)->orderBy('orden')])
            ->get();

        // --- CORRECCIÓN ---
        // Usamos 'itemInstancias' para obtener los IDs de los ítems para la consulta
        $respuestasAlumno = AlumnoRespuestaItem::where('user_id', $this->alumno->id)
            ->whereIn('item_corte_materia_periodo_id', $cortes->pluck('itemInstancias')->flatten()->pluck('id'))
            ->get()->keyBy('item_corte_materia_periodo_id');

        $notaMinimaAprobacion = Calificaciones::where('sistema_calificacion_id', $periodo->sistema_calificaciones_id)
            ->where('aprobado', true)
            ->min('nota_minima') ?? 3.0;

        $cortes->each(function ($corte) use ($respuestasAlumno) {
            $corte->nombre_completo = "{$corte->corteEscuela->nombre} ({$corte->porcentaje}%)";

            // --- CORRECCIÓN ---
            // Iteramos sobre la relación 'itemInstancias'
            $corte->itemInstancias->each(function ($item) use ($respuestasAlumno) {
                $respuesta = $respuestasAlumno->get($item->id);
                $item->nota = $respuesta?->nota_obtenida;
                $item->entregado = isset($respuesta);
                $item->respuesta_alumno = $respuesta?->respuesta_alumno;
                $item->feedback_maestro = $respuesta?->observaciones_maestro;

                // Lógica de estado correcta
                $item->estado = 'Pendiente';
                if ($item->entregado) $item->estado = 'Entregado';
                if ($item->nota !== null) $item->estado = 'Calificado';
            });
        });

        return view('livewire.alumno.calificaciones', [
            'cortes' => $cortes,
            'notaMinimaAprobacion' => $notaMinimaAprobacion

        ]);
    }

    /**
     * Procesa y guarda el archivo subido por el alumno.
     *
     * @param AlumnoRespuestaItem $respuesta La instancia de la respuesta para obtener los IDs.
     * @return string El nombre del archivo guardado.
     */
    private function _subirArchivo(AlumnoRespuestaItem $respuesta): string
    {
        $configuracion = Configuracion::find(1);
        $periodoId = $this->horario->materiaPeriodo->periodo_id;
        $alumnoId = $this->alumno->id;
        $itemId = $this->selectedItem->id;

        // 2. --- CORRECCIÓN ---
        // Construimos la ruta del directorio uniendo la ruta de la configuración
        // con la estructura de carpetas específica que necesitas.
        $directorio = $configuracion->ruta_almacenamiento . "/archivos/escuelas/periodo-{$periodoId}/respuestas";

        // 3. El nombre del archivo se mantiene como lo definiste.
        $extension = $this->archivo->getClientOriginalExtension();
        $nombreArchivo = "archivo-{$alumnoId}-{$itemId}.{$extension}";

        // 4. Usamos storeAs() indicando la ruta, el nombre y el disco 'public'.
        // Laravel se encargará de crear la estructura de carpetas si no existe.
        $this->archivo->storeAs($directorio, $nombreArchivo, 'public');

        // Devolvemos solo el nombre del archivo para guardarlo en la BD.
        return $nombreArchivo;
    }
}
