<?php

namespace App\Livewire\Configuracion;

use App\Models\SectorEconomico;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class GestionarSectoresEconomicos extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public string $search = '';

    public string $sortField = 'nombre';

    public string $sortDirection = 'asc';

    public ?int $sectorEconomicoId = null;

    public string $nombre = '';

    public bool $isEditing = false;

    protected function rules(): array
    {
        return [
            'nombre' => 'required|string|min:2|max:100',
        ];
    }

    protected array $messages = [
        'nombre.required' => 'El nombre del sector económico es obligatorio.',
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
        $this->reset(['sectorEconomicoId', 'nombre', 'isEditing']);
        $this->dispatch('abrir-offcanvas-sector-economico');
    }

    public function editar(int $id): void
    {
        $this->resetValidation();
        $sector = SectorEconomico::findOrFail($id);
        $this->sectorEconomicoId = $sector->id;
        $this->nombre = $sector->nombre;
        $this->isEditing = true;
        $this->dispatch('abrir-offcanvas-sector-economico');
    }

    public function guardar(): void
    {
        $this->validate();

        if ($this->isEditing && $this->sectorEconomicoId) {
            $sector = SectorEconomico::findOrFail($this->sectorEconomicoId);
            $sector->update([
                'nombre' => trim($this->nombre),
            ]);
            $mensaje = 'El sector económico ha sido actualizado correctamente.';
        } else {
            SectorEconomico::create([
                'nombre' => trim($this->nombre),
            ]);
            $mensaje = 'El sector económico ha sido creado con éxito.';
        }

        $this->reset(['sectorEconomicoId', 'nombre', 'isEditing']);
        $this->dispatch('cerrar-offcanvas-sector-economico');
        $this->dispatch('msn', [
            'icono' => 'success',
            'titulo' => '¡Operación Exitosa!',
            'texto' => $mensaje,
        ]);
    }

    public function eliminar(int $id): void
    {
        $sector = SectorEconomico::withCount('usuarios')->findOrFail($id);

        if ($sector->usuarios_count > 0) {
            $this->dispatch('msn', [
                'icono' => 'warning',
                'titulo' => 'No se puede eliminar',
                'texto' => "Este sector económico tiene {$sector->usuarios_count} usuario(s) asociado(s). No es posible eliminarlo.",
            ]);

            return;
        }

        $sector->delete();

        $this->dispatch('msn', [
            'icono' => 'success',
            'titulo' => '¡Eliminado!',
            'texto' => 'El sector económico ha sido eliminado con éxito.',
        ]);
    }

    public function render(): View
    {
        $termino = trim($this->search);

        $query = SectorEconomico::query()
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

        $sectoresEconomicos = $query->paginate(12);

        return view('livewire.configuracion.gestionar-sectores-economicos', [
            'sectoresEconomicos' => $sectoresEconomicos,
        ]);
    }
}
