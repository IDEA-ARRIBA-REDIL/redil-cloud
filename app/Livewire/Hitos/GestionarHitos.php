<?php

namespace App\Livewire\Hitos;

use App\Models\Hito;
use App\Models\TipoHito;
use App\Services\HitoTriggerService;
use Livewire\Component;
use Livewire\WithPagination;

class GestionarHitos extends Component
{
    use WithPagination;

    public $search = '';

    public $tipoFiltro = '';

    public $estadoFiltro = '';

    public $fecha_inicio = '';

    public $fecha_fin = '';

    protected $paginationTheme = 'bootstrap';

    public function mount()
    {
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
        if ($rolActivo) {
            $rolActivo->verificacionDelPermiso('hitos.gestionar');
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingTipoFiltro()
    {
        $this->resetPage();
    }

    public function updatingEstadoFiltro()
    {
        $this->resetPage();
    }

    public function updatingFechaInicio()
    {
        $this->resetPage();
    }

    public function updatingFechaFin()
    {
        $this->resetPage();
    }

    public function limpiarFiltros()
    {
        $this->reset(['search', 'tipoFiltro', 'estadoFiltro', 'fecha_inicio', 'fecha_fin']);
        $this->resetPage();
        $this->dispatch('limpiarFlatpickr');
    }

    public function toggleActivo($hitoId)
    {
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
        if ($rolActivo) {
            $rolActivo->verificacionDelPermiso(['hitos.editar', 'hitos.gestionar']);
        }

        try {
            $hito = Hito::findOrFail($hitoId);
            $hito->activo = ! $hito->activo;
            $hito->save();

            $this->dispatch('msn', [
                'msnIcono' => 'success',
                'msnTitulo' => '¡Estado Actualizado!',
                'msnTexto' => $hito->activo ? 'El hito ha sido activado exitosamente.' : 'El hito ha sido desactivado.',
            ]);
        } catch (\Throwable $e) {
            $this->dispatch('msn', [
                'msnIcono' => 'error',
                'msnTitulo' => 'Error',
                'msnTexto' => 'No se pudo actualizar el estado: '.$e->getMessage(),
            ]);
        }
    }

    public function migrarRetroactivo($hitoId)
    {
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
        if ($rolActivo) {
            $rolActivo->verificacionDelPermiso(['hitos.migrar_retroactivo', 'hitos.gestionar', 'hitos.editar']);
        }

        try {
            $hito = Hito::findOrFail($hitoId);
            $count = app(HitoTriggerService::class)->migrarRetroactivo($hito);

            $this->dispatch('msn', [
                'msnIcono' => 'success',
                'msnTitulo' => '¡Migración Retroactiva Completada!',
                'msnTexto' => "Se ha asignado este hito a <b>{$count}</b> usuario(s) que cumplían los requisitos históricos.",
            ]);
        } catch (\Throwable $e) {
            $this->dispatch('msn', [
                'msnIcono' => 'error',
                'msnTitulo' => 'Error en Migración',
                'msnTexto' => 'Ocurrió un error al migrar: '.$e->getMessage(),
            ]);
        }
    }

    public function eliminarHito($hitoId)
    {
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
        if ($rolActivo) {
            $rolActivo->verificacionDelPermiso(['hitos.eliminar', 'hitos.gestionar']);
        }

        try {
            $hito = Hito::findOrFail($hitoId);
            $hito->delete();

            $this->dispatch('msn', [
                'msnIcono' => 'success',
                'msnTitulo' => '¡Eliminado!',
                'msnTexto' => 'El hito fue eliminado correctamente.',
            ]);
        } catch (\Throwable $e) {
            $this->dispatch('msn', [
                'msnIcono' => 'error',
                'msnTitulo' => 'Error',
                'msnTexto' => 'No se pudo eliminar el hito: '.$e->getMessage(),
            ]);
        }
    }

    public function render()
    {
        $tiposHito = TipoHito::activos()->orderBy('nombre')->get();
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();

        $searchTerm = trim($this->search);

        $hitos = Hito::query()
            ->with(['tipoHito', 'autor', 'actividad'])
            ->withCount(['fotos', 'likes'])
            ->when($searchTerm !== '', function ($q) use ($searchTerm) {
                $term = '%'.mb_strtolower($searchTerm, 'UTF-8').'%';
                $q->where(function ($sub) use ($term) {
                    $sub->whereRaw('LOWER(titulo) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(descripcion) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(mensaje_usuario) LIKE ?', [$term]);
                });
            })
            ->when($this->tipoFiltro, fn ($q) => $q->where('tipo_hito_id', $this->tipoFiltro))
            ->when($this->estadoFiltro !== '', fn ($q) => $q->where('activo', (bool) $this->estadoFiltro))
            ->when($this->fecha_inicio, function ($q) {
                $q->where(function ($sub) {
                    $sub->whereDate('fecha_evento', '>=', $this->fecha_inicio)
                        ->orWhere(function ($sub2) {
                            $sub2->whereNull('fecha_evento')
                                ->whereDate('created_at', '>=', $this->fecha_inicio);
                        });
                });
            })
            ->when($this->fecha_fin, function ($q) {
                $q->where(function ($sub) {
                    $sub->whereDate('fecha_evento', '<=', $this->fecha_fin)
                        ->orWhere(function ($sub2) {
                            $sub2->whereNull('fecha_evento')
                                ->whereDate('created_at', '<=', $this->fecha_fin);
                        });
                });
            })
            // Orden: De la más reciente a la más antigua (por fecha_evento, luego created_at)
            ->orderByRaw('COALESCE(fecha_evento, created_at) DESC')
            ->orderBy('id', 'desc')
            ->paginate(12);

        return view('livewire.hitos.gestionar-hitos', [
            'hitos' => $hitos,
            'tiposHito' => $tiposHito,
            'rolActivo' => $rolActivo,
        ]);
    }
}
