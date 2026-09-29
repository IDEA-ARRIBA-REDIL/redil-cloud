<?php

namespace App\Livewire\Taquilla;

use App\Models\Caja;
use App\Models\HistorialModificacionPago;
use App\Models\PuntoDePago;
use Livewire\Component;
use Livewire\WithPagination;

class HistorialModificaciones extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $busqueda = '';

    public $puntoPagoId = '';

    public $cajaId = '';

    public $fechaInicio = '';

    public $fechaFin = '';

    public function mount(): void
    {
        // Por defecto se establece el mes en curso (del primer al último día)
        $this->fechaInicio = now()->startOfMonth()->toDateString();
        $this->fechaFin = now()->endOfMonth()->toDateString();
    }

    public function updatingBusqueda(): void
    {
        $this->resetPage();
    }

    public function updatingPuntoPagoId(): void
    {
        $this->resetPage();
        $this->cajaId = ''; // Resetear caja al cambiar punto de pago
    }

    public function updatingCajaId(): void
    {
        $this->resetPage();
    }

    public function updatingFechaInicio(): void
    {
        $this->resetPage();
    }

    public function updatingFechaFin(): void
    {
        $this->resetPage();
    }

    public function restablecerMesActual(): void
    {
        $this->fechaInicio = now()->startOfMonth()->toDateString();
        $this->fechaFin = now()->endOfMonth()->toDateString();
        $this->resetPage();
        $this->dispatch('restablecerFechas', inicio: $this->fechaInicio, fin: $this->fechaFin);
    }

    public function limpiarFiltros(): void
    {
        $this->reset(['busqueda', 'puntoPagoId', 'cajaId']);
        $this->restablecerMesActual();
    }

    public function exportarExcel()
    {
        $filtros = [
            'busqueda' => $this->busqueda,
            'puntoPagoId' => $this->puntoPagoId,
            'cajaId' => $this->cajaId,
            'fechaInicio' => $this->fechaInicio,
            'fechaFin' => $this->fechaFin,
        ];

        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\HistorialModificacionesExport($filtros), 'historial_modificaciones.xlsx');
    }

    public function render()
    {
        $modificaciones = HistorialModificacionPago::query()
            ->with(['asesor', 'caja', 'puntoDePago', 'compra', 'pago', 'usuarioAfectado', 'usuarioAfectado.tipoIdentificacion', 'actividad', 'categoriaActividad', 'tipoPago'])
            ->when($this->busqueda, function ($query) {
                $query->where(function ($q) {
                    $q->whereHas('usuarioAfectado', function ($subQ) {
                        $subQ->where('nombres', 'like', '%'.$this->busqueda.'%')
                            ->orWhere('apellidos', 'like', '%'.$this->busqueda.'%')
                            ->orWhere('identificacion', 'like', '%'.$this->busqueda.'%');
                    })
                        ->orWhereHas('asesor', function ($subQ) {
                            $subQ->where('nombres', 'like', '%'.$this->busqueda.'%')
                                ->orWhere('apellidos', 'like', '%'.$this->busqueda.'%');
                        })
                        ->orWhere('motivo', 'like', '%'.$this->busqueda.'%');
                });
            })
            ->when($this->puntoPagoId, function ($query) {
                $query->where('punto_de_pago_id', $this->puntoPagoId);
            })
            ->when($this->cajaId, function ($query) {
                $query->where('caja_id', $this->cajaId);
            })
            ->when($this->fechaInicio, function ($query) {
                $query->whereDate('created_at', '>=', $this->fechaInicio);
            })
            ->when($this->fechaFin, function ($query) {
                $query->whereDate('created_at', '<=', $this->fechaFin);
            })
            ->latest()
            ->paginate(10);

        $puntosDePago = PuntoDePago::where('estado', 1)->get();

        $cajas = [];
        if ($this->puntoPagoId) {
            $cajas = Caja::where('punto_de_pago_id', $this->puntoPagoId)->get();
        } else {
            $cajas = Caja::all();
        }

        return view('livewire.taquilla.historial-modificaciones', [
            'modificaciones' => $modificaciones,
            'puntosDePago' => $puntosDePago,
            'cajas' => $cajas,
        ]);
    }
}
