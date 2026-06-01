<?php

namespace App\Livewire\Maestros;

use App\Models\HorarioMateriaPeriodo;
use App\Models\Maestro;
use App\Models\RecursoAlumnoHorario;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use Livewire\Component;

class GestionRecursos extends Component
{
    // Propiedades del componente
    public $horario;

    public $maestro; // Si necesitas datos del maestro, puedes pasarlos también

    // NUEVA PROPIEDAD: para mantener el modelo del recurso que se está editando
    public ?RecursoAlumnoHorario $editingResource = null;

    // Propiedades para el formulario del modal
    public bool $showModal = false;

    public ?int $recursoId = null;

    public string $nombre = '';

    public string $descripcion = '';

    public string $tipo = 'Video';

    public string $link_externo = '';

    public string $link_youtube = '';

    // Propiedades para la subida asíncrona vía Alpine/Fetch
    public ?string $nombreArchivoSubido = null;

    public ?string $rutaArchivoSubida = null;

    // Reglas de validación
    protected function rules()
    {
        return [
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'tipo' => 'required|string',
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
        $this->reset(['recursoId', 'nombre', 'descripcion', 'tipo', 'link_externo', 'link_youtube', 'nombreArchivoSubido', 'rutaArchivoSubida']);
        $this->editingResource = null;
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
        $this->nombreArchivoSubido = null;
        $this->rutaArchivoSubida = null;

        $this->showModal = true;
    }

    // Cierra el modal
    public function cerrarModal()
    {
        // Si hay una subida temporal que no se guardó en BD, la eliminamos físicamente
        $this->eliminarArchivoLocal();

        $this->showModal = false;
        $this->editingResource = null;
    }

    // Guarda el recurso (ya sea nuevo o editado)
    public function guardarRecurso()
    {
        $this->validate();

        $data = [
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'tipo' => $this->tipo,
            'link_externo' => $this->link_externo,
            'link_youtube' => $this->link_youtube,
            'horario_materia_periodo_id' => $this->horario->id,
        ];

        // Lógica para guardar el archivo recién subido vía Alpine/Controller
        if ($this->rutaArchivoSubida) {
            // Eliminar archivo anterior si estamos editando y subimos uno nuevo
            if ($this->recursoId) {
                $recursoExistente = RecursoAlumnoHorario::find($this->recursoId);
                if ($recursoExistente && $recursoExistente->ruta_archivo) {
                    $rutaCompleta = "archivos/escuelas/recursos-horario/horario-{$this->horario->id}/{$recursoExistente->ruta_archivo}";
                    Storage::disk('public')->delete($rutaCompleta);
                }
            }

            // Guardamos solo el nombre del archivo (sin el directorio)
            $data['ruta_archivo'] = basename($this->rutaArchivoSubida);
            $data['nombre_archivo'] = $this->nombreArchivoSubido;
        }

        RecursoAlumnoHorario::updateOrCreate(['id' => $this->recursoId], $data);

        // Ya que guardamos la asociación, reseteamos las referencias temporales sin borrarlas físicamente
        $this->nombreArchivoSubido = null;
        $this->rutaArchivoSubida = null;

        $this->dispatch('notificacion', ['mensaje' => '¡Recurso guardado con éxito!']);
        $this->cerrarModal();
    }

    // Elimina el archivo que se subió de forma temporal en la sesión actual
    public function eliminarArchivoLocal()
    {
        if ($this->rutaArchivoSubida) {
            Storage::disk('public')->delete($this->rutaArchivoSubida);
            $this->nombreArchivoSubido = null;
            $this->rutaArchivoSubida = null;
            $this->dispatch('notificacion', ['texto' => 'Archivo temporal eliminado.']);
        }
    }

    // === NUEVO MÉTODO PARA ELIMINAR SÓLO EL ARCHIVO ===
    public function eliminarArchivoAdjunto()
    {
        // 1. Verificamos que estemos editando un recurso y que tenga un archivo
        if (! $this->editingResource || ! $this->editingResource->ruta_archivo) {
            return;
        }

        // 2. Eliminamos el archivo físico del almacenamiento
        $rutaCompleta = "archivos/escuelas/recursos-horario/horario-{$this->horario->id}/{$this->editingResource->ruta_archivo}";
        Storage::disk('public')->delete($rutaCompleta);

        // 3. Limpiamos las columnas relacionadas al archivo en la base de datos
        $this->editingResource->update([
            'ruta_archivo' => null,
            'nombre_archivo' => null,
        ]);

        // 4. Refrescamos el modelo desde la BD. Esto es CLAVE para que Livewire
        // re-renderice la vista y muestre el input para subir un nuevo archivo.
        $this->editingResource->refresh();

        $this->nombreArchivoSubido = null;
        $this->rutaArchivoSubida = null;

        $this->dispatch('notificacion', ['texto' => 'Archivo eliminado con éxito.']);
    }

    // Elimina un recurso
    #[On('eliminar-recurso')]
    public function eliminarRecurso($id)
    {
        $recurso = RecursoAlumnoHorario::findOrFail($id);

        // Eliminar el archivo físico si existe
        if ($recurso->ruta_archivo) {
            $rutaCompleta = "archivos/escuelas/recursos-horario/horario-{$recurso->horario_materia_periodo_id}/{$recurso->ruta_archivo}";
            Storage::disk('public')->delete($rutaCompleta);
        }

        $recurso->delete();
        $this->dispatch('notificacion', ['texto' => '¡Recurso eliminado!']);
    }

    // Cambia el estado de visibilidad
    public function toggleVisibilidad($id)
    {
        $recurso = RecursoAlumnoHorario::findOrFail($id);
        $recurso->visible = ! $recurso->visible;
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
