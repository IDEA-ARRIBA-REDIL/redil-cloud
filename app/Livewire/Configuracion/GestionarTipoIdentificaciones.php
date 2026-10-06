<?php

namespace App\Livewire\Configuracion;

use App\Models\TipoIdentificacion;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class GestionarTipoIdentificaciones extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public string $search = '';

    public string $sortField = 'nombre';

    public string $sortDirection = 'asc';

    public ?int $tipoIdentificacionId = null;

    public string $nombre = '';

    public string $abreviatura = '';

    public bool $formulario_donacion = false;

    public bool $isEditing = false;

    protected function rules(): array
    {
        return [
            'nombre' => 'required|string|min:2|max:100',
            'abreviatura' => 'required|string|min:1|max:10',
            'formulario_donacion' => 'boolean',
        ];
    }

    protected array $messages = [
        'nombre.required' => 'El nombre del tipo de identificación es obligatorio.',
        'nombre.min' => 'El nombre debe tener al menos 2 caracteres.',
        'nombre.max' => 'El nombre no puede superar los 100 caracteres.',
        'abreviatura.required' => 'La abreviatura es obligatoria.',
        'abreviatura.min' => 'La abreviatura debe tener al menos 1 caracter.',
        'abreviatura.max' => 'La abreviatura no puede superar los 10 caracteres.',
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
        $allowedFields = ['nombre', 'abreviatura', 'formulario_donacion', 'usuarios_count', 'created_at'];
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
        $this->reset(['tipoIdentificacionId', 'nombre', 'abreviatura', 'formulario_donacion', 'isEditing']);
        $this->dispatch('abrir-offcanvas-tipo-identificacion');
    }

    public function editar(int $id): void
    {
        $this->resetValidation();
        $tipo = TipoIdentificacion::findOrFail($id);
        $this->tipoIdentificacionId = $tipo->id;
        $this->nombre = $tipo->nombre;
        $this->abreviatura = $tipo->abreviatura;
        $this->formulario_donacion = (bool) $tipo->formulario_donacion;
        $this->isEditing = true;
        $this->dispatch('abrir-offcanvas-tipo-identificacion');
    }

    public function guardar(): void
    {
        $this->validate();

        $datos = [
            'nombre' => trim($this->nombre),
            'abreviatura' => strtoupper(trim($this->abreviatura)),
            'formulario_donacion' => (bool) $this->formulario_donacion,
        ];

        if ($this->isEditing && $this->tipoIdentificacionId) {
            $tipo = TipoIdentificacion::findOrFail($this->tipoIdentificacionId);
            $tipo->update($datos);
            $mensaje = 'Tipo de identificación actualizado con éxito.';
        } else {
            TipoIdentificacion::create($datos);
            $mensaje = 'Tipo de identificación creado con éxito.';
        }

        $this->reset(['tipoIdentificacionId', 'nombre', 'abreviatura', 'formulario_donacion', 'isEditing']);
        $this->dispatch('cerrar-offcanvas-tipo-identificacion');
        $this->dispatch('msn', [
            'icono' => 'success',
            'titulo' => '¡Operación Exitosa!',
            'texto' => $mensaje,
        ]);
    }

    public function toggleDonacion(int $id): void
    {
        $tipo = TipoIdentificacion::findOrFail($id);
        $tipo->formulario_donacion = ! $tipo->formulario_donacion;
        $tipo->save();

        $estado = $tipo->formulario_donacion ? 'habilitado' : 'deshabilitado';
        $this->dispatch('msn', [
            'icono' => 'success',
            'titulo' => 'Estado Actualizado',
            'texto' => "El tipo {$tipo->nombre} ahora está {$estado} para donaciones.",
        ]);
    }

    public function eliminar(int $id): void
    {
        $tipo = TipoIdentificacion::withCount('usuarios')->findOrFail($id);

        if ($tipo->usuarios_count > 0) {
            $this->dispatch('msn', [
                'icono' => 'warning',
                'titulo' => 'No se puede eliminar',
                'texto' => "Este tipo de identificación tiene {$tipo->usuarios_count} usuario(s) asociado(s). No es posible eliminarlo.",
            ]);

            return;
        }

        $tipo->delete();

        $this->dispatch('msn', [
            'icono' => 'success',
            'titulo' => '¡Eliminado!',
            'texto' => 'El tipo de identificación ha sido eliminado con éxito.',
        ]);
    }

    public function render(): View
    {
        $termino = trim($this->search);

        $query = TipoIdentificacion::query()
            ->withCount('usuarios')
            ->when($termino !== '', function ($q) use ($termino) {
                $q->where(function ($sub) use ($termino) {
                    $sub->whereRaw(
                        "translate(nombre,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜ','aeiouAEIOUaeiouAEIOU') ILIKE translate(?,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜ','aeiouAEIOUaeiouAEIOU')",
                        ["%{$termino}%"]
                    )->orWhereRaw(
                        "translate(abreviatura,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜ','aeiouAEIOUaeiouAEIOU') ILIKE translate(?,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜ','aeiouAEIOUaeiouAEIOU')",
                        ["%{$termino}%"]
                    );
                });
            });

        if ($this->sortField === 'usuarios_count') {
            $query->orderBy('usuarios_count', $this->sortDirection);
        } elseif ($this->sortField === 'created_at') {
            $query->orderBy('created_at', $this->sortDirection);
        } elseif ($this->sortField === 'abreviatura') {
            $query->orderBy('abreviatura', $this->sortDirection);
        } elseif ($this->sortField === 'formulario_donacion') {
            $query->orderBy('formulario_donacion', $this->sortDirection);
        } else {
            $query->orderBy('nombre', $this->sortDirection);
        }

        $tipoIdentificaciones = $query->paginate(12);

        return view('livewire.configuracion.gestionar-tipo-identificaciones', [
            'tipoIdentificaciones' => $tipoIdentificaciones,
        ]);
    }
}
