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
    protected function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'tipo' => ['required', 'in:Video,Documento,Enlace,Libro,Clase,Predica'],
            'link_externo' => ['nullable', 'url:http,https', 'max:2048'],
            'link_youtube' => ['nullable', 'url:http,https', 'max:2048'],
        ];
    }

    /**
     * Mensajes de validación personalizados.
     *
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del recurso es obligatorio.',
            'nombre.max' => 'El nombre del recurso no puede superar los 255 caracteres.',
            'tipo.required' => 'Debes seleccionar un tipo de recurso.',
            'tipo.in' => 'El tipo de recurso seleccionado no es válido.',
            'link_externo.url' => 'El enlace externo debe ser una URL válida (http:// o https://).',
            'link_youtube.url' => 'El enlace de YouTube debe ser una URL válida (http:// o https://).',
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

        // Llenamos las propiedades individuales con respaldo seguro ante valores nulos
        $this->recursoId = $recurso->id;
        $this->nombre = (string) ($recurso->nombre ?? '');
        $this->descripcion = (string) ($recurso->descripcion ?? '');
        $this->tipo = (string) ($recurso->tipo ?: 'Video');
        $this->link_externo = (string) ($recurso->link_externo ?? '');
        $this->link_youtube = (string) ($recurso->link_youtube ?? '');
        $this->nombreArchivoSubido = null;
        $this->rutaArchivoSubida = null;

        $this->showModal = true;
    }

    // Cierra el modal
    public function cerrarModal()
    {
        // Si hay una subida temporal que no se guardó en BD, la eliminamos físicamente
        $this->eliminarArchivoLocal(false);

        $this->showModal = false;
        $this->editingResource = null;
    }

    // Guarda el recurso (ya sea nuevo o editado)
    public function guardarRecurso()
    {
        $this->nombre = trim($this->nombre);
        $this->descripcion = trim($this->descripcion);
        $this->link_externo = trim($this->link_externo);
        $this->link_youtube = trim($this->link_youtube);

        $this->validate();

        $data = [
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion !== '' ? $this->descripcion : null,
            'tipo' => $this->tipo,
            'link_externo' => $this->link_externo !== '' ? $this->link_externo : null,
            'link_youtube' => $this->link_youtube !== '' ? $this->link_youtube : null,
            'horario_materia_periodo_id' => $this->horario->id,
        ];

        // Lógica para guardar el archivo recién subido vía Alpine/Controller validando la ruta permitida
        if ($this->rutaArchivoSubida) {
            $prefijoPermitido = "archivos/escuelas/recursos-horario/horario-{$this->horario->id}/";
            $patronSeguro = '/^'.preg_quote($prefijoPermitido, '/').'[A-Za-z0-9._-]+\.(pdf|docx|txt)$/i';

            if (preg_match($patronSeguro, $this->rutaArchivoSubida) !== 1) {
                $this->addError('archivo', 'La ruta del archivo cargado no es válida.');

                return;
            }

            // Eliminar archivo anterior si estamos editando y subimos uno nuevo
            if ($this->recursoId) {
                $recursoExistente = RecursoAlumnoHorario::find($this->recursoId);
                if ($recursoExistente && $recursoExistente->ruta_archivo) {
                    $rutaCompleta = "archivos/escuelas/recursos-horario/horario-{$this->horario->id}/{$recursoExistente->ruta_archivo}";
                    Storage::disk('public')->delete($rutaCompleta);
                }
            }

            // Guardamos solo el nombre del archivo en servidor y el nombre visible seguro
            $data['ruta_archivo'] = basename($this->rutaArchivoSubida);
            $data['nombre_archivo'] = $this->nombreArchivoSubido ?: basename($this->rutaArchivoSubida);
        }

        RecursoAlumnoHorario::updateOrCreate(['id' => $this->recursoId], $data);

        // Ya que guardamos la asociación, reseteamos las referencias temporales sin borrarlas físicamente
        $this->nombreArchivoSubido = null;
        $this->rutaArchivoSubida = null;

        $this->dispatch('notificacion', ['texto' => '¡Recurso guardado con éxito!']);
        $this->cerrarModal();
    }

    // Elimina el archivo que se subió de forma temporal en la sesión actual
    public function eliminarArchivoLocal(bool $notificar = true)
    {
        if ($this->rutaArchivoSubida) {
            Storage::disk('public')->delete($this->rutaArchivoSubida);
            $this->nombreArchivoSubido = null;
            $this->rutaArchivoSubida = null;

            if ($notificar) {
                $this->dispatch('notificacion', ['texto' => 'Archivo temporal eliminado.']);
            }
        }
    }

    // === MÉTODO PARA ELIMINAR SÓLO EL ARCHIVO ADJUNTO ===
    #[On('eliminar-archivo-adjunto')]
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

        $nombreMateria = $this->horario->materiaPeriodo?->materia?->nombre ?? 'Materia no disponible';
        $infoClase = $this->horario->resumenEncabezadoClase();

        return view('livewire.maestros.gestion-recursos', [
            'recursos' => $recursos,
            'nombreMateria' => $nombreMateria,
            'infoClase' => $infoClase,
        ]);
    }
}
