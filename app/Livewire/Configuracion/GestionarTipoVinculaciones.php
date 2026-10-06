<?php

namespace App\Livewire\Configuracion;

use App\Models\TipoVinculacion;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class GestionarTipoVinculaciones extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public string $search = '';

    public string $sortField = 'nombre';

    public string $sortDirection = 'asc';

    public ?int $tipoVinculacionId = null;

    public string $nombre = '';

    public bool $por_grupo = false;

    public bool $isEditing = false;

    protected function rules(): array
    {
        return [
            'nombre' => 'required|string|min:2|max:30',
            'por_grupo' => 'boolean',
        ];
    }

    protected array $messages = [
        'nombre.required' => 'El nombre del tipo de vinculación es obligatorio.',
        'nombre.min' => 'El nombre debe tener al menos 2 caracteres.',
        'nombre.max' => 'El nombre no puede superar los 30 caracteres.',
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
        $allowedFields = ['nombre', 'por_grupo', 'usuarios_count', 'created_at'];
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

    public function togglePorGrupo(int $id): void
    {
        $tipo = TipoVinculacion::findOrFail($id);
        $tipo->por_grupo = ! $tipo->por_grupo;
        $tipo->save();

        $estadoTexto = $tipo->por_grupo ? 'habilitado para reportes de grupo' : 'deshabilitado para reportes de grupo';
        $this->dispatch('msn', [
            'icono' => 'success',
            'titulo' => '¡Actualizado!',
            'texto' => "El tipo de vinculación \"{$tipo->nombre}\" ha sido {$estadoTexto}.",
        ]);
    }

    public function crear(): void
    {
        $this->resetValidation();
        $this->reset(['tipoVinculacionId', 'nombre', 'por_grupo', 'isEditing']);
        $this->dispatch('abrir-offcanvas-tipo-vinculacion');
    }

    public function editar(int $id): void
    {
        $this->resetValidation();
        $tipo = TipoVinculacion::findOrFail($id);
        $this->tipoVinculacionId = $tipo->id;
        $this->nombre = $tipo->nombre;
        $this->por_grupo = (bool) $tipo->por_grupo;
        $this->isEditing = true;
        $this->dispatch('abrir-offcanvas-tipo-vinculacion');
    }

    public function guardar(): void
    {
        $this->validate();

        if ($this->isEditing && $this->tipoVinculacionId) {
            $tipo = TipoVinculacion::findOrFail($this->tipoVinculacionId);
            $tipo->update([
                'nombre' => trim($this->nombre),
                'por_grupo' => $this->por_grupo,
            ]);
            $mensaje = 'El tipo de vinculación ha sido actualizado correctamente.';
        } else {
            TipoVinculacion::create([
                'nombre' => trim($this->nombre),
                'por_grupo' => $this->por_grupo,
            ]);
            $mensaje = 'El tipo de vinculación ha sido creado con éxito.';
        }

        $this->reset(['tipoVinculacionId', 'nombre', 'por_grupo', 'isEditing']);
        $this->dispatch('cerrar-offcanvas-tipo-vinculacion');
        $this->dispatch('msn', [
            'icono' => 'success',
            'titulo' => '¡Operación Exitosa!',
            'texto' => $mensaje,
        ]);
    }

    public function eliminar(int $id): void
    {
        $tipo = TipoVinculacion::withCount('usuarios')->findOrFail($id);

        if ($tipo->usuarios_count > 0) {
            $this->dispatch('msn', [
                'icono' => 'warning',
                'titulo' => 'No se puede eliminar',
                'texto' => "Este tipo de vinculación tiene {$tipo->usuarios_count} usuario(s) asociado(s). No es posible eliminarlo.",
            ]);

            return;
        }

        $tipo->delete();

        $this->dispatch('msn', [
            'icono' => 'success',
            'titulo' => '¡Eliminado!',
            'texto' => 'El tipo de vinculación ha sido eliminado con éxito.',
        ]);
    }

    public function render(): View
    {
        $termino = trim($this->search);

        $query = TipoVinculacion::query()
            ->withCount('usuarios')
            ->when($termino !== '', function ($q) use ($termino) {
                $q->whereRaw(
                    "translate(nombre,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜ','aeiouAEIOUaeiouAEIOU') ILIKE translate(?,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜ','aeiouAEIOUaeiouAEIOU')",
                    ["%{$termino}%"]
                );
            });

        if ($this->sortField === 'usuarios_count') {
            $query->orderBy('usuarios_count', $this->sortDirection);
        } elseif ($this->sortField === 'por_grupo') {
            $query->orderBy('por_grupo', $this->sortDirection);
        } elseif ($this->sortField === 'created_at') {
            $query->orderBy('created_at', $this->sortDirection);
        } else {
            $query->orderBy('nombre', $this->sortDirection);
        }

        $tipoVinculaciones = $query->paginate(12);

        return view('livewire.configuracion.gestionar-tipo-vinculaciones', [
            'tipoVinculaciones' => $tipoVinculaciones,
        ]);
    }
}
