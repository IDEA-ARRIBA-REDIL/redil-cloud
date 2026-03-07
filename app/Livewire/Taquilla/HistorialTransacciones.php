<?php

namespace App\Livewire\Taquilla;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Caja;
use App\Models\Compra;
use Carbon\Carbon;

class HistorialTransacciones extends Component
{
    use WithPagination;

    public $cajaActiva;
    public $fecha;
    public $busqueda = '';

    protected $paginationTheme = 'bootstrap';

    public function mount(Caja $cajaActiva)
    {
        $this->cajaActiva = $cajaActiva;
        $this->fecha = Carbon::now()->format('Y-m-d');
    }

    public function updatingFecha()
    {
        $this->resetPage();
    }

    public function updatingBusqueda()
    {
        $this->resetPage();
    }

    public function anularCompra($compraId)
    {
        $compra = Compra::find($compraId);

        if ($compra) {
            // Lógica básica de anulación: cambiar estado a 4 (Anulado/Error)
            // Aquí se podría expandir para revertir pagos, liberar cupos, etc.
            $compra->estado = 4;
            $compra->save();

            $this->dispatch('mostrarToast', [
                'icon' => 'success',
                'title' => 'Compra anulada correctamente'
            ]);
        }
    }

    public function render()
    {
        $query = Compra::query();

        if (str_contains($this->fecha, ' to ')) {
            $fechas = explode(' to ', $this->fecha);
            $query->whereBetween('fecha', [$fechas[0], $fechas[1]]);
        } else {
            $query->whereDate('fecha', $this->fecha);
        }

        $transacciones = $query->whereHas('pagos', function ($q) {
                $q->where('registro_caja_id', $this->cajaActiva->id);
            })
            ->when($this->busqueda, function ($q) {
                $q->where(function ($subQ) {
                    $subQ->where('nombre_completo_comprador', 'like', '%' . $this->busqueda . '%')
                      ->orWhere('identificacion_comprador', 'like', '%' . $this->busqueda . '%')
                      ->orWhere('email_comprador', 'like', '%' . $this->busqueda . '%');
                });
            })
            ->with(['user', 'actividad.tipo', 'pagos', 'inscripciones.categoriaActividad'])
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('livewire.taquilla.historial-transacciones', [
            'transacciones' => $transacciones
        ]);
    }
}
