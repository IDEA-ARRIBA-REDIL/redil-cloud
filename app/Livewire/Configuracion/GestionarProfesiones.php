<?php

namespace App\Livewire\Configuracion;

use App\Models\Profesion;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class GestionarProfesiones extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public string $search = '';

    public string $sortField = 'nombre';

    public string $sortDirection = 'asc';

    public ?int $profesionId = null;

    public string $nombre = '';

    public bool $isEditing = false;

    protected function rules(): array
    {
        return [
            'nombre' => 'required|string|min:2|max:100',
        ];
    }

    protected array $messages = [
        'nombre.required' => 'El nombre de la profesión es obligatorio.',
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
        $allowedFields = ['nombre', 'usuarios_count', 'created_at'];
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

    public function crear(): void
    {
        $this->resetValidation();
        $this->reset(['profesionId', 'nombre', 'isEditing']);
        $this->dispatch('abrir-offcanvas-profesion');
    }

    public function editar(int $id): void
    {
        $this->resetValidation();
        $profesion = Profesion::findOrFail($id);
        $this->profesionId = $profesion->id;
        $this->nombre = $profesion->nombre;
        $this->isEditing = true;
        $this->dispatch('abrir-offcanvas-profesion');
    }

    public function guardar(): void
    {
        $this->validate();

        if ($this->isEditing && $this->profesionId) {
            $profesion = Profesion::findOrFail($this->profesionId);
            $profesion->update([
                'nombre' => trim($this->nombre),
            ]);
            $mensaje = 'La profesión ha sido actualizada correctamente.';
        } else {
            Profesion::create([
                'nombre' => trim($this->nombre),
            ]);
            $mensaje = 'La profesión ha sido creada con éxito.';
        }

        $this->reset(['profesionId', 'nombre', 'isEditing']);
        $this->dispatch('cerrar-offcanvas-profesion');
        $this->dispatch('msn', [
            'icono' => 'success',
            'titulo' => '¡Operación Exitosa!',
            'texto' => $mensaje,
        ]);
    }

    public function eliminar(int $id): void
    {
        $profesion = Profesion::withCount('usuarios')->findOrFail($id);

        if ($profesion->usuarios_count > 0) {
            $this->dispatch('msn', [
                'icono' => 'warning',
                'titulo' => 'No se puede eliminar',
                'texto' => "Esta profesión tiene {$profesion->usuarios_count} usuario(s) asociado(s). No es posible eliminarla.",
            ]);

            return;
        }

        $profesion->delete();

        $this->dispatch('msn', [
            'icono' => 'success',
            'titulo' => '¡Eliminado!',
            'texto' => 'La profesión ha sido eliminada con éxito.',
        ]);
    }

    public function render(): View
    {
        $termino = trim($this->search);

        $query = Profesion::query()
            ->withCount('usuarios')
            ->when($termino !== '', function ($q) use ($termino) {
                $q->whereRaw(
                    "translate(nombre,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜ','aeiouAEIOUaeiouAEIOU') ILIKE translate(?,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜ','aeiouAEIOUaeiouAEIOU')",
                    ["%{$termino}%"]
                );
            });

        if ($this->sortField === 'usuarios_count') {
            $query->orderBy('usuarios_count', $this->sortDirection);
        } elseif ($this->sortField === 'created_at') {
            $query->orderBy('created_at', $this->sortDirection);
        } else {
            $query->orderBy('nombre', $this->sortDirection);
        }

        $profesiones = $query->paginate(12);

        return view('livewire.configuracion.gestionar-profesiones', [
            'profesiones' => $profesiones,
        ]);
    }
}
