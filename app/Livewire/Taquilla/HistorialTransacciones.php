<?php

namespace App\Livewire\Taquilla;

use App\Exports\HistorialTransaccionesCajaExport;
use App\Models\Actividad;
use App\Models\Caja;
use App\Models\Compra;
use App\Models\Pago;
use App\Models\TipoPago;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class HistorialTransacciones extends Component
{
    use WithPagination;

    public $cajaActiva;

    public $fecha;

    public $busqueda = '';
    
    public $actividad_id = '';
    
    public $tipo_pago_id = '';

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
    
    public function updatingActividadId()
    {
        $this->resetPage();
    }

    public function updatingTipoPagoId()
    {
        $this->resetPage();
    }

    public function anularCompra($compraId)
    {
        $compra = Compra::find($compraId);

        if ($compra) {
            $compra->estado = 4;
            $compra->save();

            $this->dispatch('mostrarToast', [
                'icon' => 'success',
                'title' => 'Compra anulada correctamente',
            ]);
        }
    }
    
    public function exportarExcel()
    {
        $filtros = [
            'fecha' => $this->fecha,
            'busqueda' => $this->busqueda,
            'actividad_id' => $this->actividad_id,
            'tipo_pago_id' => $this->tipo_pago_id,
            'caja_id' => $this->cajaActiva->id
        ];
        
        return Excel::download(new HistorialTransaccionesCajaExport($filtros), 'historial_caja_' . $this->cajaActiva->id . '_' . now()->format('Ymd_Hi') . '.xlsx');
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
        
        if (!empty($this->actividad_id)) {
            $query->where('actividad_id', $this->actividad_id);
        }

        $transacciones = $query->whereHas('pagos', function ($q) {
            $q->where('registro_caja_id', $this->cajaActiva->id);
            if (!empty($this->tipo_pago_id)) {
                $q->where('tipo_pago_id', $this->tipo_pago_id);
            }
        })
            ->when($this->busqueda, function ($q) {
                $q->where(function ($subQ) {
                    $subQ->where('nombre_completo_comprador', 'like', '%'.$this->busqueda.'%')
                        ->orWhere('identificacion_comprador', 'like', '%'.$this->busqueda.'%')
                        ->orWhere('email_comprador', 'like', '%'.$this->busqueda.'%');
                });
            })
            ->with(['user', 'actividad.tipo', 'pagos', 'inscripciones.categoriaActividad'])
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        // --- CÁLCULO DEL RESUMEN FINANCIERO DEL DÍA / RANGO PARA LA CAJA ACTIVA ---
        $queryPagos = Pago::where('registro_caja_id', $this->cajaActiva->id)
            ->where('anulado_pdp', false);

        if (str_contains($this->fecha, ' to ')) {
            $fechas = explode(' to ', $this->fecha);
            $queryPagos->whereBetween('fecha', [$fechas[0].' 00:00:00', $fechas[1].' 23:59:59']);
        } else {
            $queryPagos->whereDate('fecha', $this->fecha);
        }
        
        if (!empty($this->tipo_pago_id)) {
            $queryPagos->where('tipo_pago_id', $this->tipo_pago_id);
        }
        
        if (!empty($this->actividad_id)) {
            $queryPagos->whereHas('compra', function($q) {
                $q->where('actividad_id', $this->actividad_id);
            });
        }

        $pagosCaja = $queryPagos->with(['compra.actividad', 'tipoPago'])->get();

        $totalRecaudado = $pagosCaja->sum('valor');

        $totalEfectivo = $pagosCaja->filter(fn ($p) => str_contains(strtolower($p->tipoPago->nombre ?? ''), 'efectivo'))->sum('valor');
        $totalDatafono = $pagosCaja->filter(fn ($p) => ! str_contains(strtolower($p->tipoPago->nombre ?? ''), 'efectivo'))->sum('valor');

        $desgloseActividades = $pagosCaja->groupBy(fn ($p) => $p->compra->actividad->nombre ?? 'Sin Actividad')
            ->map(function ($group, $nombreActividad) use ($totalRecaudado) {
                $totalVal = $group->sum('valor');
                $porcentaje = $totalRecaudado > 0 ? round(($totalVal / $totalRecaudado) * 100, 1) : 0;

                return [
                    'nombre' => $nombreActividad,
                    'total' => $totalVal,
                    'cantidad' => $group->count(),
                    'porcentaje' => $porcentaje,
                ];
            })
            ->sortByDesc('total')
            ->values();

        $desgloseMetodosPago = $pagosCaja->groupBy(fn ($p) => $p->tipoPago->nombre ?? 'Otro')
            ->map(function ($group, $nombreMetodo) use ($totalRecaudado) {
                $totalVal = $group->sum('valor');
                $porcentaje = $totalRecaudado > 0 ? round(($totalVal / $totalRecaudado) * 100, 1) : 0;

                return [
                    'nombre' => $nombreMetodo,
                    'total' => $totalVal,
                    'cantidad' => $group->count(),
                    'porcentaje' => $porcentaje,
                ];
            })
            ->sortByDesc('total')
            ->values();

        $resumenFinanciero = [
            'totalRecaudado' => $totalRecaudado,
            'totalTransacciones' => $transacciones->total(),
            'totalEfectivo' => $totalEfectivo,
            'totalDatafono' => $totalDatafono,
            'desgloseActividades' => $desgloseActividades,
            'desgloseMetodosPago' => $desgloseMetodosPago,
        ];

        return view('livewire.taquilla.historial-transacciones', [
            'transacciones' => $transacciones,
            'resumenFinanciero' => $resumenFinanciero,
            'actividades' => Actividad::orderBy('created_at', 'desc')->get(),
            'tiposPago' => TipoPago::where('activo', true)->get()
        ]);
    }
}
