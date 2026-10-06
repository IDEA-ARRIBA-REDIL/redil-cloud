<?php

namespace App\Livewire\Configuracion;

use App\Models\TareaConsolidacion;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class GestionarTareasConsolidacion extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public string $search = '';

    public string $sortField = 'orden';

    public string $sortDirection = 'asc';

    public ?int $tareaId = null;

    public string $nombre = '';

    public string $descripcion = '';

    public ?int $orden = null;

    public bool $default = false;

    public bool $isEditing = false;

    protected function rules(): array
    {
        return [
            'nombre' => 'required|string|min:2|max:50',
            'descripcion' => 'nullable|string|max:200',
            'orden' => 'nullable|integer|min:0|max:32767',
            'default' => 'boolean',
        ];
    }

    protected array $messages = [
        'nombre.required' => 'El nombre de la tarea es obligatorio.',
        'nombre.min' => 'El nombre debe tener al menos 2 caracteres.',
        'nombre.max' => 'El nombre no puede superar los 50 caracteres.',
        'descripcion.max' => 'La descripción no puede superar los 200 caracteres.',
        'orden.integer' => 'El orden debe ser un número entero.',
        'orden.min' => 'El orden no puede ser negativo.',
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function limpiarBusqueda(): void
    {
        $this->search = '';
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        $allowedFields = ['orden', 'nombre', 'default', 'usuarios_count', 'created_at'];
        if (! in_array($field, $allowedFields)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = $field === 'created_at' ? 'desc' : 'asc';
        }

        $this->resetPage();
    }

    public function toggleDefault(int $id): void
    {
        $tarea = TareaConsolidacion::findOrFail($id);
        $tarea->default = ! $tarea->default;
        $tarea->save();

        $estadoTexto = $tarea->default ? 'marcada como tarea por defecto' : 'desmarcada de tarea por defecto';
        $this->dispatch('msn', [
            'icono' => 'success',
            'titulo' => '¡Actualizado!',
            'texto' => "La tarea \"{$tarea->nombre}\" ha sido {$estadoTexto}.",
        ]);
    }

    public function crear(): void
    {
        $this->resetValidation();
        $this->reset(['tareaId', 'nombre', 'descripcion', 'orden', 'default', 'isEditing']);

        // Asignar automáticamente el siguiente orden sugerido
        $ultimoOrden = TareaConsolidacion::max('orden');
        $this->orden = $ultimoOrden !== null ? $ultimoOrden + 1 : 1;

        $this->dispatch('abrir-offcanvas-tarea');
    }

    public function editar(int $id): void
    {
        $this->resetValidation();
        $tarea = TareaConsolidacion::findOrFail($id);
        $this->tareaId = $tarea->id;
        $this->nombre = $tarea->nombre;
        $this->descripcion = $tarea->descripcion ?? '';
        $this->orden = $tarea->orden;
        $this->default = (bool) $tarea->default;
        $this->isEditing = true;
        $this->dispatch('abrir-offcanvas-tarea');
    }

    public function guardar(): void
    {
        $this->validate();

        $data = [
            'nombre' => trim($this->nombre),
            'descripcion' => trim($this->descripcion) !== '' ? trim($this->descripcion) : null,
            'orden' => $this->orden !== null ? (int) $this->orden : null,
            'default' => $this->default,
        ];

        if ($this->isEditing && $this->tareaId) {
            $tarea = TareaConsolidacion::findOrFail($this->tareaId);
            $tarea->update($data);
            $mensaje = 'La tarea de consolidación ha sido actualizada correctamente.';
        } else {
            TareaConsolidacion::create($data);
            $mensaje = 'La tarea de consolidación ha sido creada con éxito.';
        }

        $this->reset(['tareaId', 'nombre', 'descripcion', 'orden', 'default', 'isEditing']);
        $this->dispatch('cerrar-offcanvas-tarea');
        $this->dispatch('msn', [
            'icono' => 'success',
            'titulo' => '¡Operación Exitosa!',
            'texto' => $mensaje,
        ]);
    }

    public function eliminar(int $id): void
    {
        $tarea = TareaConsolidacion::withCount('usuarios')->findOrFail($id);

        if ($tarea->usuarios_count > 0) {
            $this->dispatch('msn', [
                'icono' => 'warning',
                'titulo' => 'No se puede eliminar',
                'texto' => "Esta tarea de consolidación tiene {$tarea->usuarios_count} asignación(es) o usuario(s) vinculado(s). No es posible eliminarla.",
            ]);

            return;
        }

        $tarea->delete();

        $this->dispatch('msn', [
            'icono' => 'success',
            'titulo' => '¡Eliminado!',
            'texto' => 'La tarea de consolidación ha sido eliminada con éxito.',
        ]);
    }

    public function render(): View
    {
        $termino = trim($this->search);

        $query = TareaConsolidacion::query()
            ->withCount('usuarios')
            ->when($termino !== '', function ($q) use ($termino) {
                $q->where(function ($sub) use ($termino) {
                    $sub->whereRaw(
                        "translate(nombre,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜ','aeiouAEIOUaeiouAEIOU') ILIKE translate(?,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜ','aeiouAEIOUaeiouAEIOU')",
                        ["%{$termino}%"]
                    )->orWhereRaw(
                        "translate(coalesce(descripcion,''),'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜ','aeiouAEIOUaeiouAEIOU') ILIKE translate(?,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜ','aeiouAEIOUaeiouAEIOU')",
                        ["%{$termino}%"]
                    );
                });
            });

        if ($this->sortField === 'usuarios_count') {
            $query->orderBy('usuarios_count', $this->sortDirection);
        } elseif ($this->sortField === 'default') {
            $query->orderBy('default', $this->sortDirection);
        } elseif ($this->sortField === 'nombre') {
            $query->orderBy('nombre', $this->sortDirection);
        } elseif ($this->sortField === 'created_at') {
            $query->orderBy('created_at', $this->sortDirection);
        } else {
            $query->orderBy('orden', $this->sortDirection)->orderBy('id', 'asc');
        }

        $tareas = $query->paginate(12);

        return view('livewire.configuracion.gestionar-tareas-consolidacion', [
            'tareas' => $tareas,
        ]);
    }
}
