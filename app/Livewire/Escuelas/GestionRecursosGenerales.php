<?php

namespace App\Livewire\Escuelas;

use App\Models\RecursoGeneralEscuela;
use App\Models\Role;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class GestionRecursosGenerales extends Component
{
    // --- Propiedades para el modal de Crear/Editar Recurso ---
    public $recursoId;

    public $nombre;

    public $descripcion;

    public $tipo;

    public $link_externo;

    public $link_youtube;

    public $archivoExistente = null;

    public $modoEdicion = false;

    public $recursos;

    public $listaDeRoles;

    public $archivoUrl = null;

    // Propiedades para Alpine Upload
    public $nombreArchivoSubido = null;

    public $rutaArchivoSubida = null;

    // --- Propiedades para el Offcanvas de Roles ---
    public ?RecursoGeneralEscuela $recursoSeleccionado = null;

    public $rolesAsignados = [];

    // --- Reglas de Validación ---
    protected function rules()
    {
        return [
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'link_externo' => 'nullable',
            'link_youtube' => 'nullable',
        ];
    }

    public function mount()
    {
        $this->recursos = RecursoGeneralEscuela::with('roles')->latest()->get();
        $this->listaDeRoles = Role::all();
    }

    /**
     * Renderiza la vista del componente.
     */
    public function render()
    {

        return view('livewire.escuelas.gestion-recursos-generales', [
            'recursos' => $this->recursos,
            'listaDeRoles' => $this->listaDeRoles,
        ]);
    }

    // --- MÉTODOS PARA EL MODAL DE CREAR/EDITAR RECURSO ---

    /**
     * Prepara el modal para crear un nuevo recurso.
     */
    public function abrirModalCrear()
    {
        $this->resetInputFields();
        $this->modoEdicion = false;
        $this->dispatch('abrir-modal-recurso');
    }

    /**
     * Carga los datos de un recurso existente en el modal para edición.
     */
    public function abrirModalEditar($id)
    {
        $recurso = RecursoGeneralEscuela::findOrFail($id);
        $this->recursoId = $id;
        $this->nombre = $recurso->nombre;
        $this->descripcion = $recurso->descripcion;
        $this->tipo = $recurso->tipo;
        $this->link_externo = $recurso->link_externo;
        $this->link_youtube = $recurso->link_youtube;
        $this->archivoExistente = $recurso->nombre_archivo;
        $this->archivoUrl = $recurso->archivo_url; // <-- AÑADE ESTA LÍNEA
        $this->modoEdicion = true;
        $this->dispatch('abrir-modal-recurso');
    }

    /**
     * Guarda un recurso nuevo o actualiza uno existente.
     */
    /**
     * Guarda un recurso nuevo o actualiza uno existente.
     */
    public function guardarRecurso(): void
    {
        $this->validate();

        $datosRecurso = [
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'tipo' => $this->tipo,
            'link_externo' => $this->link_externo,
            'link_youtube' => $this->link_youtube,
        ];

        // Lógica para gestionar el archivo subido mediante Alpine.js
        if ($this->rutaArchivoSubida) {
            // 1. Eliminar archivo anterior si estamos editando y subiendo uno nuevo
            if ($this->recursoId) {
                $recursoViejo = RecursoGeneralEscuela::find($this->recursoId);
                if ($recursoViejo && $recursoViejo->ruta_archivo) {
                    Storage::disk('public')->delete($recursoViejo->ruta_archivo);
                }
            }

            // 2. Guardamos la nueva ruta y nombre del archivo
            $datosRecurso['ruta_archivo'] = $this->rutaArchivoSubida;
            $datosRecurso['nombre_archivo'] = $this->nombreArchivoSubido;
        }

        RecursoGeneralEscuela::updateOrCreate(['id' => $this->recursoId], $datosRecurso);

        $this->dispatch('notificacion', [
            'titulo' => $this->modoEdicion ? '¡Recurso Actualizado!' : '¡Recurso Creado!',
            'texto' => 'La operación se realizó correctamente.',
        ]);

        $this->dispatch('cerrar-modal-recurso');
        $this->mount(); // Esto refresca la lista de recursos
        $this->resetInputFields();
    }
    // --- MÉTODOS PARA EL OFFCANVAS DE GESTIÓN DE ROLES ---

    public function eliminarArchivoAdjunto()
    {
        // Nos aseguramos de que estemos editando un recurso
        if ($this->recursoId) {
            $recurso = RecursoGeneralEscuela::find($this->recursoId);

            // Si el recurso y el archivo existen
            if ($recurso && $recurso->ruta_archivo) {
                // 1. Elimina el archivo físico del storage
                Storage::disk('public')->delete($recurso->ruta_archivo);

                // 2. Limpia los campos en la base de datos
                $recurso->update([
                    'ruta_archivo' => null,
                    'nombre_archivo' => null,
                ]);

                // 3. Actualiza el estado del componente para reflejar el cambio en la vista al instante
                $this->archivoExistente = null;
                $this->archivoUrl = null;

                // 4. (Opcional) Notifica al usuario
                $this->dispatch('notificacion', ['titulo' => '¡Archivo Eliminado!', 'texto' => 'El archivo adjunto ha sido eliminado.']);
            }
        }
    }

    /**
     * Abre el offcanvas para gestionar los roles de un recurso específico.
     */
    /**
     * Abre el modal para gestionar los roles de un recurso específico.
     */
    public function abrirModalRoles($id)
    {
        // 1. Busca el recurso específico que el usuario quiere editar.
        //    Usamos with('roles') para cargar también los roles asociados en una sola consulta.
        $this->recursoSeleccionado = RecursoGeneralEscuela::with('roles')->findOrFail($id);

        // 2. De los roles que encontramos, extraemos SOLAMENTE sus IDs y los convertimos en un array simple.
        //    El resultado es algo como: [1, 5, 12]
        $this->rolesAsignados = $this->recursoSeleccionado->roles->pluck('id')->toArray();

        // 3. Ahora que la variable $rolesAsignados tiene los IDs correctos,
        //    le avisamos a la vista que abra el modal. Alpine recibirá automáticamente
        //    el array [1, 5, 12] a través de @entangle.
        $this->dispatch('abrir-modal-roles');
    }

    /**
     * Sincroniza los roles seleccionados con el recurso en la base de datos.
     */
    public function actualizarRoles()
    {
        if ($this->recursoSeleccionado) {
            $this->recursoSeleccionado->roles()->sync($this->rolesAsignados);

            $this->dispatch('notificacion', [
                'titulo' => '¡Roles Actualizados!',
                'texto' => 'Los permisos del recurso han sido modificados.',
            ]);

            // Despachamos un nuevo evento para cerrar el modal
            $this->dispatch('cerrar-modal-roles');
            $this->recursoSeleccionado = null;
            $this->rolesAsignados = [];
        }
    }
    // --- MÉTODO PARA ELIMINAR ---

    /**
     * Elimina un recurso de la base de datos y su archivo asociado.
     */
    public function eliminarRecurso($id)
    {
        $recurso = RecursoGeneralEscuela::findOrFail($id);

        if ($recurso->ruta_archivo) {
            Storage::disk('public')->delete($recurso->ruta_archivo);
        }

        $recurso->delete();
        $this->mount();

        $this->dispatch('notificacion', [
            'titulo' => '¡Recurso Eliminado!',
            'texto' => 'El recurso ha sido eliminado permanentemente.',
        ]);
    }

    /**
     * Resetea los campos del formulario del modal.
     */
    private function resetInputFields(): void
    {
        // Si hay una subida temporal activa que no se ha guardado, la eliminamos físicamente
        if ($this->rutaArchivoSubida) {
            Storage::disk('public')->delete($this->rutaArchivoSubida);
        }

        $this->recursoId = null;
        $this->nombre = '';
        $this->descripcion = '';
        $this->archivoUrl = null;
        $this->tipo = '';
        $this->link_externo = '';
        $this->link_youtube = '';
        $this->archivoExistente = null;
        $this->nombreArchivoSubido = null;
        $this->rutaArchivoSubida = null;
        $this->resetErrorBag();
    }

    /**
     * Elimina el archivo subido localmente antes de guardar el recurso.
     */
    public function eliminarArchivoLocal(): void
    {
        if ($this->rutaArchivoSubida) {
            Storage::disk('public')->delete($this->rutaArchivoSubida);
            $this->rutaArchivoSubida = null;
            $this->nombreArchivoSubido = null;
        }
    }
}
