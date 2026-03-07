<?php

namespace App\Livewire\Alumno;

use Livewire\Component;
use Livewire\WithFileUploads; // Importante para la subida de archivos
use App\Models\HorarioMateriaPeriodo;
use App\Models\CortePeriodo;
use App\Models\Calificaciones;
use App\Models\AlumnoRespuestaItem;
use App\Models\ItemCorteMateriaPeriodo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CalificacionesAlumno extends Component
{
    use WithFileUploads;

    // Propiedades que recibimos
    public HorarioMateriaPeriodo $horario;
    public $alumno;

    // Propiedades para el modal
    public bool $showModal = false;
    public ?ItemCorteMateriaPeriodo $selectedItem = null;
    public string $respuestaTexto = '';
    public $archivo; // Para la subida de archivos

    // Reglas de validación para el formulario
    protected $rules = [
        'respuestaTexto' => 'required|string|min:10',
        'archivo' => 'nullable|file|max:10240', // Opcional, max 10MB
    ];

    /**
     * Se ejecuta cuando el componente se inicializa.
     */
    public function mount(HorarioMateriaPeriodo $horario)
    {
        $this->horario = $horario;
        $this->alumno = Auth::user();
    }

    /**
     * Abre el modal para responder a un ítem específico.
     */
    public function abrirModal(int $itemId)
    {
        $this->selectedItem = ItemCorteMateriaPeriodo::find($itemId);
        $this->reset(['respuestaTexto', 'archivo']); // Limpiamos datos anteriores
        $this->resetErrorBag(); // Limpiamos errores de validación anteriores
        $this->showModal = true;
    }

    /**
     * Guarda la respuesta del alumno.
     */
    public function guardarRespuesta()
    {
        $this->validate();

        $rutaArchivo = null;
        if ($this->archivo) {
            // Guardamos el archivo en una ruta específica
            $rutaArchivo = $this->archivo->store('escuelas/respuestas_alumnos');
        }

        // Usamos updateOrCreate para crear o actualizar la respuesta
        AlumnoRespuestaItem::updateOrCreate(
            [
                'user_id' => $this->alumno->id,
                'item_corte_materia_periodo_id' => $this->selectedItem->id,
            ],
            [
                'respuesta_alumno' => $this->respuestaTexto,
                'enlace_documento_alumno' => $rutaArchivo, // O el nombre del archivo
                // 'ruta_documento_alumno' => $rutaArchivo,
            ]
        );

        $this->showModal = false; // Cerramos el modal

        // Enviamos una notificación "toast" a la vista
        $this->dispatch('notificacion', ['mensaje' => '¡Respuesta guardada con éxito!']);
    }

    /**
     * Renderiza la vista del componente.
     */
    public function render()
    {
        $periodo = $this->horario->materiaPeriodo->periodo;
        $cortes = CortePeriodo::where('periodo_id', $periodo->id)
            ->with(['itemInstancias' => fn($query) => $query->where('horario_materia_periodo_id', $this->horario->id)->orderBy('orden')])
            ->get();

        $respuestasAlumno = AlumnoRespuestaItem::where('user_id', $this->alumno->id)
            ->whereIn('item_corte_materia_periodo_id', $cortes->pluck('items')->flatten()->pluck('id'))
            ->get()->keyBy('item_corte_materia_periodo_id');

        // 1. Buscamos la nota mínima para aprobar, igual que en el controlador principal.
        $notaMinimaAprobacion = Calificaciones::where('sistema_calificacion_id', $periodo->sistema_calificaciones_id)
            ->where('aprobado', true)
            ->min('nota_minima');

        // 2. Si no se encuentra, usamos un valor por defecto seguro.
        if (is_null($notaMinimaAprobacion)) {
            $notaMinimaAprobacion = 3.0;
        }

        $cortes->each(function ($corte) use ($respuestasAlumno) {
            $corte->nombre_completo = "{$corte->corteEscuela->nombre} ({$corte->porcentaje}%)";
            $corte->itemInstancias->each(function ($item) use ($respuestasAlumno) {
                $respuesta = $respuestasAlumno->get($item->id);
                $item->nota = $respuesta?->nota_obtenida;
                $item->entregado = isset($respuesta);
                $item->respuesta_alumno = $respuesta?->respuesta_alumno;
                $item->feedback_maestro = $respuesta?->observaciones_maestro;
                $item->estado = 'Pendiente';
                if ($item->entregado) $item->estado = 'Entregado';
                if ($item->nota !== null) $item->estado = 'Calificado';
            });
        });

        return view('livewire.alumno.calificaciones', [
            'cortes' => $cortes,
        ]);
    }
}
