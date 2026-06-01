<?php

namespace App\Livewire\Alumno;

use App\Models\AlumnoRespuestaItem;
// Importante para la subida de archivos
use App\Models\Calificaciones;
use App\Models\CortePeriodo;
use App\Models\HorarioMateriaPeriodo;
use App\Models\ItemCorteMateriaPeriodo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Component; // Importar Log para errores

class CalificacionesAlumno extends Component
{
    public HorarioMateriaPeriodo $horario;

    public $alumno;

    public bool $showModal = false;

    public ?ItemCorteMateriaPeriodo $selectedItem = null;

    public ?AlumnoRespuestaItem $existingResponse = null;

    public string $respuestaTexto = '';

    public ?string $nombreArchivoSubido = null;

    // Reglas de validación dinámicas para el formulario
    public function rules(): array
    {
        $tieneArchivo = ! empty($this->nombreArchivoSubido);

        return [
            'respuestaTexto' => $tieneArchivo ? 'nullable|string' : 'required|string|min:10',
        ];
    }

    /**
     * Se ejecuta cuando el componente se inicializa.
     */
    public function mount(HorarioMateriaPeriodo $horario)
    {
        $this->horario = $horario;
        $this->alumno = Auth::user();
    }

    public function abrirModal(int $itemId)
    {
        $this->selectedItem = ItemCorteMateriaPeriodo::find($itemId);
        $this->existingResponse = AlumnoRespuestaItem::where('user_id', $this->alumno->id)
            ->where('item_corte_materia_periodo_id', $itemId)
            ->first();

        $this->reset(['respuestaTexto', 'nombreArchivoSubido']);
        $this->resetErrorBag();

        if ($this->existingResponse) {
            $this->respuestaTexto = $this->existingResponse->respuesta_alumno;
            $this->nombreArchivoSubido = $this->existingResponse->enlace_documento_alumno;
        }

        $this->showModal = true;
    }

    public function guardarRespuesta()
    {
        $this->validate();

        // Buscamos la respuesta en la base de datos de manera directa y fresca
        $response = AlumnoRespuestaItem::where('user_id', $this->alumno->id)
            ->where('item_corte_materia_periodo_id', $this->selectedItem->id)
            ->first();

        if ($response) {
            $response->respuesta_alumno = $this->respuestaTexto;
            $response->enlace_documento_alumno = $this->nombreArchivoSubido;
            $response->save();

            // Sincronizamos la propiedad del componente
            $this->existingResponse = $response;

            $this->dispatch('notificacion', ['mensaje' => '¡Respuesta actualizada con éxito!']);
        } else {
            $response = new AlumnoRespuestaItem;
            $response->user_id = $this->alumno->id;
            $response->item_corte_materia_periodo_id = $this->selectedItem->id;
            $response->respuesta_alumno = $this->respuestaTexto;
            $response->enlace_documento_alumno = $this->nombreArchivoSubido;
            $response->save();

            // Sincronizamos la propiedad del componente
            $this->existingResponse = $response;

            $this->dispatch('notificacion', ['mensaje' => '¡Respuesta guardada con éxito!']);
        }

        $this->showModal = false;
    }

    public function eliminarArchivo()
    {
        $periodoId = $this->horario->materiaPeriodo->periodo_id;
        $directorio = "/archivos/escuelas/periodo-{$periodoId}/respuestas";

        // Caso 1: Hay un archivo subido temporalmente pero no se ha guardado la respuesta en la BD todavía
        if (! $this->existingResponse && $this->nombreArchivoSubido) {
            $rutaCompleta = $directorio.'/'.$this->nombreArchivoSubido;
            Storage::disk('public')->delete($rutaCompleta);
            $this->nombreArchivoSubido = null;
            $this->dispatch('notificacion', ['mensaje' => 'Archivo eliminado con éxito.']);

            return;
        }

        // Caso 2: Ya existe una respuesta en la BD y tiene un archivo guardado
        if ($this->existingResponse && $this->existingResponse->enlace_documento_alumno) {
            $rutaCompleta = $directorio.'/'.$this->existingResponse->enlace_documento_alumno;
            Storage::disk('public')->delete($rutaCompleta);

            // Guardamos el cambio en la base de datos de manera explícita con save()
            $this->existingResponse->enlace_documento_alumno = null;
            $this->existingResponse->save();
            $this->existingResponse->refresh();

            $this->nombreArchivoSubido = null;
            $this->dispatch('notificacion', ['mensaje' => 'Archivo eliminado con éxito.']);

            return;
        }

        // Caso de seguridad por si las dudas (por ejemplo, si el nombreArchivoSubido local difiere de la BD)
        if ($this->nombreArchivoSubido) {
            $rutaCompleta = $directorio.'/'.$this->nombreArchivoSubido;
            Storage::disk('public')->delete($rutaCompleta);
            $this->nombreArchivoSubido = null;
            $this->dispatch('notificacion', ['mensaje' => 'Archivo eliminado con éxito.']);
        }
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
            ->with(['itemInstancias' => fn ($query) => $query->where('horario_materia_periodo_id', $this->horario->id)->orderBy('orden')])
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
                if ($item->entregado) {
                    $item->estado = 'Entregado';
                }
                if ($item->nota !== null) {
                    $item->estado = 'Calificado';
                }
            });
        });

        return view('livewire.alumno.calificaciones', [
            'cortes' => $cortes,
            'notaMinimaAprobacion' => $notaMinimaAprobacion,

        ]);
    }
}
