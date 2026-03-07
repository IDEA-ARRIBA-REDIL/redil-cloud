<?php

namespace App\Livewire\PuntosDePago;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\PuntoDePago;
use App\Models\Caja;
use App\Models\Pago;
use App\Models\Compra;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\InformeTransaccionesPuntoExport;

class InformeTransaccionesPunto extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public PuntoDePago $puntoDePago;

    // Filtros
    public $fechaInicio;
    public $fechaFin;
    public $cajasSeleccionadas = []; // Array de IDs de cajas
    public $estadoTransaccion = 'todos'; // todos, aprobada (1), pendiente (2), anulada (6)

    // Datos para la vista (Selects)
    public $todasLasCajas = [];

    // Métricas
    public $totalIngresos = 0;
    public $totalTransacciones = 0;
    public $totalPorCaja = [];

    public function exportarExcel()
    {
        $fecha = Carbon::now()->format('Y-m-d_H-i');
        return Excel::download(new InformeTransaccionesPuntoExport(
            $this->fechaInicio,
            $this->fechaFin,
            $this->cajasSeleccionadas,
            $this->estadoTransaccion
        ), 'reporte_transacciones_pdp_' . $this->puntoDePago->id . '_' . $fecha . '.xlsx');
    }

    public function mount(PuntoDePago $puntoDePago)
    {
        $this->puntoDePago = $puntoDePago;
        // Por defecto: Hoy
        $this->fechaInicio = Carbon::now()->format('Y-m-d');
        $this->fechaFin = Carbon::now()->format('Y-m-d');

        // Cargar todas las cajas del punto
        $this->todasLasCajas = Caja::where('punto_de_pago_id', $this->puntoDePago->id)->get();

        // Por defecto: Todas las cajas seleccionadas
        $this->cajasSeleccionadas = $this->todasLasCajas->pluck('id')->map(fn($id) => (string)$id)->toArray();
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['fechaInicio', 'fechaFin', 'cajasSeleccionadas', 'estadoTransaccion'])) {
            $this->resetPage();
        }
    }

    public function render()
    {
        // 1. Query Base de Pagos (Para métricas y lista detallada)
        // Buscamos pagos que pertenezcan a las cajas seleccionadas
        // Y filtramos por fecha y estado de la COMPRA asociada

        $query = Pago::query()
            ->with(['compra.user', 'caja.usuario', 'tipoPago'])
            ->whereIn('registro_caja_id', $this->cajasSeleccionadas)
            ->whereBetween(DB::raw('DATE(created_at)'), [$this->fechaInicio, $this->fechaFin]);

        // Filtro por Estado de Compra (si aplica)
        if ($this->estadoTransaccion !== 'todos') {
            $query->whereHas('compra', function ($q) {
                if ($this->estadoTransaccion == 'aprobada') {
                    $q->where('estado', 1);
                } elseif ($this->estadoTransaccion == 'pendiente') {
                    $q->where('estado', 2);
                } elseif ($this->estadoTransaccion == 'anulada') {
                    $q->where('estado', 6);
                }
            });
        }

        // 2. Calcular Métricas Globales (antes de paginar)
        // Clonamos el query para no afectar la paginación
        $metricsQuery = clone $query;
        $this->totalIngresos = $metricsQuery->sum('valor');
        $this->totalTransacciones = $metricsQuery->count();

        // 3. Calcular Totales por Caja
        // Usamos raw query o colección para agrupar
        $porCaja = clone $query;
        $this->totalPorCaja = $porCaja->select('registro_caja_id', DB::raw('SUM(valor) as total'), DB::raw('COUNT(*) as cantidad'))
            ->groupBy('registro_caja_id')
            ->get()
            ->mapWithKeys(function ($item) {
                $caja = $this->todasLasCajas->find($item->registro_caja_id);
                return [$item->registro_caja_id => [
                    'nombre' => $caja ? $caja->nombre : 'Caja #'.$item->registro_caja_id,
                    'total' => $item->total,
                    'cantidad' => $item->cantidad
                ]];
            });

        // 4. Obtener listado paginado
        $transacciones = $query->latest()->paginate(12);

        return view('livewire.puntos-de-pago.informe-transacciones-punto', [
            'transacciones' => $transacciones
        ]);
    }
}
