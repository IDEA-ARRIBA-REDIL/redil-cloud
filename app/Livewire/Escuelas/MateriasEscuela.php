<?php



namespace App\Livewire\Escuelas;

use Livewire\Component;
use App\Models\Materia;
use App\Models\Escuela;

class MateriasEscuela extends Component
{
    // Recibimos el id de la escuela y la escuela (si es necesario)
    public $escuelaId;
    public $materias = [];

    // Propiedades para el formulario de creación de materia
    public $nombre;
    public $nivel_id; // Puede ser select o input, según tus necesidades
    public $habilitar_calificaciones = true;
    public $habilitar_asistencias = false;
    public $asistencias_minimas;
    public $descripcion;
    public $habilitar_alerta_inasistencias = false;
    public $habilitar_traslado = false;
    public $caracter_obligatorio = true;

    // Para controlar la apertura del modal (si lo manejas desde Livewire)
    public $modalOpen = false;

    protected $rules = [
        'nombre' => 'required|string|max:100',
        // 'nivel_id' => 'nullable|integer', // Si es necesario
        // Escuela_id se asigna desde el componente
        'habilitar_calificaciones' => 'boolean',
        'habilitar_asistencias' => 'boolean',
        'asistencias_minimas' => 'nullable|integer',
        'descripcion' => 'nullable|string',
        'habilitar_alerta_inasistencias' => 'boolean',
        'habilitar_traslado' => 'boolean',
        'caracter_obligatorio' => 'boolean',
    ];

    public function mount($escuelaId)
    {
        $this->escuelaId = $escuelaId;
        // Cargamos las materias de la escuela actual
        $this->loadMaterias();
    }

    public function loadMaterias()
    {
        // Asumimos que el modelo Escuela tiene la relación "materias"
        $this->materias = Escuela::find($this->escuelaId)->materias()->get();
    }

    // Método para abrir el modal
    public function openModal()
    {
        $this->resetForm();
        $this->modalOpen = true;
        $this->dispatch('openModal');
    }

    // Método para cerrar el modal
    public function closeModal()
    {
        $this->modalOpen = false;
    }

    // Método para resetear las propiedades del formulario
    public function resetForm()
    {
        $this->reset([
            'nombre', 'nivel_id', 'habilitar_calificaciones', 'habilitar_asistencias',
            'asistencias_minimas', 'descripcion', 'habilitar_alerta_inasistencias',
            'habilitar_traslado', 'caracter_obligatorio'
        ]);
        // Reiniciar a los valores por defecto
        $this->habilitar_calificaciones = true;
        $this->habilitar_asistencias = false;
        $this->habilitar_alerta_inasistencias = false;
        $this->habilitar_traslado = false;
        $this->caracter_obligatorio = true;
    }

    // Método para crear una nueva materia
    public function createMateria()
    {
        $this->validate();

        // Crear la materia; se asigna la escuela automáticamente
        Materia::create([
            'nombre' => $this->nombre,
            'nivel_id' => $this->nivel_id,
            'escuela_id' => $this->escuelaId,
            'habilitar_calificaciones' => $this->habilitar_calificaciones,
            'habilitar_asistencias' => $this->habilitar_asistencias,
            'asistencias_minimas' => $this->asistencias_minimas,
            'descripcion' => $this->descripcion,
            'habilitar_alerta_inasistencias' => $this->habilitar_alerta_inasistencias,
            'habilitar_traslado' => $this->habilitar_traslado,
            'caracter_obligatorio' => $this->caracter_obligatorio,
        ]);

        // Actualizamos la lista de materias
        $this->loadMaterias();

        // Cerramos el modal
        $this->closeModal();

        // Emitir un evento para mostrar SweetAlert de éxito
        $this->dispatch('materia-created', [
            'titulo' => '¡Felicidades!',
            'mensaje' => 'La materia se ha creado exitosamente.',
            'tipo' => 'success'
        ]);
    }

    public function render()
    {
        return view('livewire.escuelas.materias-escuela');
    }
}
