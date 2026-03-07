<?php

namespace App\Livewire\Maestros;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\HorarioMateriaPeriodo;
use App\Models\RecursoAlumnoHorario;
use App\Models\Maestro;
use App\Models\Configuracion;
use App\Models\User;
use Livewire\Attributes\On; // <-- 1. IMPORTAR EL ATRIBUTO
use Illuminate\Support\Facades\Storage;

class GestionRecursos extends Component
{
    use WithFileUploads;

    // Propiedades del componente
    public $horario;
    public $maestro; // Si necesitas datos del maestro, puedes pasarlos también
    // NUEVA PROPIEDAD: para mantener el modelo del recurso que se está editando
    public ?RecursoAlumnoHorario $editingResource = null;

    // Propiedades para el formulario del modal
    public bool $showModal = false;
    public ?int $recursoId = null;
    public string $nombre = '', $descripcion = '', $tipo = 'Video', $link_externo = '', $link_youtube = '';
    public $archivo;

    // Reglas de validación
    protected function rules()
    {
        return [
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'tipo' => 'required|string',

            'archivo' => 'nullable|file|mimes:pdf,docx,pptx,xlsx,jpg,png|max:10240', // Max 10MB
        ];
    }

    // El método mount se ejecuta al iniciar el componente.
    // Aquí recibimos el horario desde la ruta.
    public function mount(HorarioMateriaPeriodo $horarioAsignado, Maestro $maestro)
    {
        $this->horario = $horarioAsignado;
        $this->maestro = $maestro;
    }

    // Abre el modal en modo "Crear"
    public function abrirModalCrear()
    {
        $this->resetValidation();
        $this->reset(['recursoId', 'nombre', 'descripcion', 'tipo', 'link_externo', 'link_youtube', 'archivo']);
        $this->editingResource = null; // <- AÑADIR ESTA LÍNEA
        $this->tipo = 'Video';
        $this->showModal = true;
    }

    // Abre el modal en modo "Editar", cargando los datos del recurso.
    public function abrirModalEditar($id)
    {
        $this->resetValidation();
        $recurso = RecursoAlumnoHorario::findOrFail($id);

        // Asignamos el modelo completo a nuestra nueva propiedad
        $this->editingResource = $recurso;

        // Llenamos las propiedades individuales como antes
        $this->recursoId = $recurso->id;
        $this->nombre = $recurso->nombre;
        $this->descripcion = $recurso->descripcion;
        $this->tipo = $recurso->tipo;
        $this->link_externo = $recurso->link_externo;
        $this->link_youtube = $recurso->link_youtube;
        $this->archivo = null;

        $this->showModal = true;
    }

    // Cierra el modal
    public function cerrarModal()
    {
        $this->showModal = false;
        $this->editingResource = null; // <- AÑADIR ESTA LÍNEA
    }

    // Guarda el recurso (ya sea nuevo o editado)
    public function guardarRecurso()
    {
        $configuracion = Configuracion::find(1);
        $this->validate();

        $data = [
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'tipo' => $this->tipo,
            'link_externo' => $this->link_externo,
            'link_youtube' => $this->link_youtube,
            'horario_materia_periodo_id' => $this->horario->id,
        ];

        // Lógica para subir el archivo
        if ($this->archivo) {
            // Eliminar archivo anterior si estamos editando y subiendo uno nuevo
            if ($this->recursoId) {
                $recursoExistente = RecursoAlumnoHorario::find($this->recursoId);
                if ($recursoExistente && $recursoExistente->ruta_archivo) {
                    Storage::disk('public')->delete($recursoExistente->ruta_archivo);
                }
            }
            $periodoId = $this->horario->materiaPeriodo->periodo_id;

            $directorio = $configuracion->ruta_almacenamiento . "/archivos/escuelas/periodo-{$periodoId}/recursos/";
            $extension = $this->archivo->getClientOriginalExtension();
            $nombreArchivo = 'recursos-alumno-' . $this->horario->id . '.' . $extension;
            $data['ruta_archivo'] = $this->archivo->storeAs($directorio, $nombreArchivo, 'public');
            $data['nombre_archivo'] = $nombreArchivo;
        }

        RecursoAlumnoHorario::updateOrCreate(['id' => $this->recursoId], $data);

        $this->dispatch('notificacion', ['mensaje' => '¡Recurso guardado con éxito!']);
        $this->cerrarModal();
    }
    // === NUEVO MÉTODO PARA ELIMINAR SÓLO EL ARCHIVO ===
    public function eliminarArchivoAdjunto()
    {
        // 1. Verificamos que estemos editando un recurso y que tenga un archivo
        if (!$this->editingResource || !$this->editingResource->ruta_archivo) {
            return;
        }

        // 2. Eliminamos el archivo físico del almacenamiento
        Storage::disk('public')->delete($this->editingResource->ruta_archivo);

        // 3. Limpiamos las columnas relacionadas al archivo en la base de datos
        $this->editingResource->update([
            'ruta_archivo' => null,
            'nombre_archivo' => null,
        ]);

        // 4. Refrescamos el modelo desde la BD. Esto es CLAVE para que Livewire
        // re-renderice la vista y muestre el input para subir un nuevo archivo.
        $this->editingResource->refresh();

        $this->dispatch('notificacion', ['texto' => 'Archivo eliminado con éxito.']);
    }

    // Elimina un recurso
    #[On('eliminar-recurso')]
    public function eliminarRecurso($id)
    {
        $recurso = RecursoAlumnoHorario::findOrFail($id);
        $configuracion = Configuracion::find(1);
        $periodoId = $this->horario->materiaPeriodo->periodo_id;
        $directorio = $configuracion->ruta_almacenamiento . "/archivos/periodo-{$periodoId}/recursos/";
        $rutaCompleta = $directorio . '/' . $recurso->ruta_archivo;

        // Eliminar el archivo físico si existe
        if ($recurso->ruta_archivo) {
            Storage::disk('public')->delete($rutaCompleta);
        }

        $recurso->delete();
        $this->dispatch('notificacion', ['texto' => '¡Recurso eliminado!']);
    }

    // Cambia el estado de visibilidad
    public function toggleVisibilidad($id)
    {
        $recurso = RecursoAlumnoHorario::findOrFail($id);
        $recurso->visible = !$recurso->visible;
        $recurso->save();
    }

    // El método render muestra la vista y le pasa los datos necesarios.
    public function render()
    {
        // Obtenemos los datos que antes eran estáticos, pero ahora desde la BD.
        $recursos = RecursoAlumnoHorario::where('horario_materia_periodo_id', $this->horario->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $nombreMateria = $this->horario->materiaPeriodo->materia->nombre;
        // Suponiendo que tienes un accesor para esto

        return view('livewire.maestros.gestion-recursos', [
            'recursos' => $recursos,
            'nombreMateria' => $nombreMateria,

        ]);
    }
}
