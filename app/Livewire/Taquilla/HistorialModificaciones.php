<?php

namespace App\Livewire\Taquilla;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\HistorialModificacionPago;
use App\Models\PuntoDePago;
use App\Models\Caja;

class HistorialModificaciones extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $busqueda = '';
    public $puntoPagoId = '';
    public $cajaId = '';
    public $fecha = '';

    public function mount()
    {
        $this->fecha = now()->toDateString();
    }

    public function updatingBusqueda()
    {
        $this->resetPage();
    }

    public function updatingPuntoPagoId()
    {
        $this->resetPage();
        $this->cajaId = ''; // Resetear caja al cambiar punto de pago
    }

    public function updatingCajaId()
    {
        $this->resetPage();
    }

    public function updatingFecha()
    {
        $this->resetPage();
    }

    public function exportarExcel()
    {
        $filtros = [
            'busqueda' => $this->busqueda,
            'puntoPagoId' => $this->puntoPagoId,
            'cajaId' => $this->cajaId,
            'fecha' => $this->fecha,
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
                        $subQ->where('nombres', 'like', '%' . $this->busqueda . '%')
                             ->orWhere('apellidos', 'like', '%' . $this->busqueda . '%')
                             ->orWhere('identificacion', 'like', '%' . $this->busqueda . '%');
                    })
                    ->orWhereHas('asesor', function ($subQ) {
                        $subQ->where('nombres', 'like', '%' . $this->busqueda . '%')
                             ->orWhere('apellidos', 'like', '%' . $this->busqueda . '%');
                    })
                    ->orWhere('motivo', 'like', '%' . $this->busqueda . '%');
                });
            })
            ->when($this->puntoPagoId, function ($query) {
                $query->where('punto_de_pago_id', $this->puntoPagoId);
            })
            ->when($this->cajaId, function ($query) {
                $query->where('caja_id', $this->cajaId);
            })
            ->when($this->fecha, function ($query) {
                $query->whereDate('created_at', $this->fecha);
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
            'cajas' => $cajas
        ]);
    }
}
