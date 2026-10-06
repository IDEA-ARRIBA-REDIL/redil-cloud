<?php

namespace App\Livewire\Configuracion;

use App\Models\EstadoCivil;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class GestionarEstadosCiviles extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public string $search = '';

    public string $sortField = 'nombre';

    public string $sortDirection = 'asc';

    public ?int $estadoCivilId = null;

    public string $nombre = '';

    public bool $es_union_libre = false;

    public bool $isEditing = false;

    protected function rules(): array
    {
        return [
            'nombre' => 'required|string|min:2|max:100',
            'es_union_libre' => 'boolean',
        ];
    }

    protected array $messages = [
        'nombre.required' => 'El nombre del estado civil es obligatorio.',
        'nombre.min' => 'El nombre debe tener al menos 2 caracteres.',
        'nombre.max' => 'El nombre no puede superar los 100 caracteres.',
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
        $allowedFields = ['nombre', 'es_union_libre', 'usuarios_count', 'created_at'];
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

    public function toggleUnionLibre(int $id): void
    {
        $estado = EstadoCivil::findOrFail($id);
        $estado->es_union_libre = ! $estado->es_union_libre;
        $estado->save();

        $estadoTexto = $estado->es_union_libre ? 'marcado como unión libre' : 'desmarcado de unión libre';
        $this->dispatch('msn', [
            'icono' => 'success',
            'titulo' => '¡Actualizado!',
            'texto' => "El estado civil \"{$estado->nombre}\" ha sido {$estadoTexto}.",
        ]);
    }

    public function crear(): void
    {
        $this->resetValidation();
        $this->reset(['estadoCivilId', 'nombre', 'es_union_libre', 'isEditing']);
        $this->dispatch('abrir-offcanvas-estado-civil');
    }

    public function editar(int $id): void
    {
        $this->resetValidation();
        $estado = EstadoCivil::findOrFail($id);
        $this->estadoCivilId = $estado->id;
        $this->nombre = $estado->nombre;
        $this->es_union_libre = (bool) $estado->es_union_libre;
        $this->isEditing = true;
        $this->dispatch('abrir-offcanvas-estado-civil');
    }

    public function guardar(): void
    {
        $this->validate();

        if ($this->isEditing && $this->estadoCivilId) {
            $estado = EstadoCivil::findOrFail($this->estadoCivilId);
            $estado->update([
                'nombre' => trim($this->nombre),
                'es_union_libre' => $this->es_union_libre,
            ]);
            $mensaje = 'El estado civil ha sido actualizado correctamente.';
        } else {
            EstadoCivil::create([
                'nombre' => trim($this->nombre),
                'es_union_libre' => $this->es_union_libre,
            ]);
            $mensaje = 'El estado civil ha sido creado con éxito.';
        }

        $this->reset(['estadoCivilId', 'nombre', 'es_union_libre', 'isEditing']);
        $this->dispatch('cerrar-offcanvas-estado-civil');
        $this->dispatch('msn', [
            'icono' => 'success',
            'titulo' => '¡Operación Exitosa!',
            'texto' => $mensaje,
        ]);
    }

    public function eliminar(int $id): void
    {
        $estado = EstadoCivil::withCount('usuarios')->findOrFail($id);

        if ($estado->usuarios_count > 0) {
            $this->dispatch('msn', [
                'icono' => 'warning',
                'titulo' => 'No se puede eliminar',
                'texto' => "Este estado civil tiene {$estado->usuarios_count} usuario(s) asociado(s). No es posible eliminarlo.",
            ]);

            return;
        }

        $estado->delete();

        $this->dispatch('msn', [
            'icono' => 'success',
            'titulo' => '¡Eliminado!',
            'texto' => 'El estado civil ha sido eliminado con éxito.',
        ]);
    }

    public function render(): View
    {
        $termino = trim($this->search);

        $query = EstadoCivil::query()
            ->withCount('usuarios')
            ->when($termino !== '', function ($q) use ($termino) {
                $q->whereRaw(
                    "translate(nombre,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜ','aeiouAEIOUaeiouAEIOU') ILIKE translate(?,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜ','aeiouAEIOUaeiouAEIOU')",
                    ["%{$termino}%"]
                );
            });

        if ($this->sortField === 'usuarios_count') {
            $query->orderBy('usuarios_count', $this->sortDirection);
        } elseif ($this->sortField === 'es_union_libre') {
            $query->orderBy('es_union_libre', $this->sortDirection);
        } elseif ($this->sortField === 'created_at') {
            $query->orderBy('created_at', $this->sortDirection);
        } else {
            $query->orderBy('nombre', $this->sortDirection);
        }

        $estadosCiviles = $query->paginate(12);

        return view('livewire.configuracion.gestionar-estados-civiles', [
            'estadosCiviles' => $estadosCiviles,
        ]);
    }
}
