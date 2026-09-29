<?php

namespace App\Exports;

use App\Models\Pago;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class HistorialTransaccionesCajaExport implements FromView, ShouldAutoSize
{
    public $filtros;

    public function __construct($filtros)
    {
        $this->filtros = $filtros;
    }

    public function view(): View
    {
        $query = Pago::where('registro_caja_id', $this->filtros['caja_id'])
            ->where('anulado_pdp', false);

        if (! empty($this->filtros['fecha'])) {
            $fechaFiltro = $this->filtros['fecha'];
            if (str_contains($fechaFiltro, ' to ')) {
                $fechas = explode(' to ', $fechaFiltro);
                $start = trim($fechas[0]);
                $end = trim($fechas[1] ?? $fechas[0]);
                $query->whereBetween('fecha', [$start.' 00:00:00', $end.' 23:59:59']);
            } elseif (str_contains($fechaFiltro, ' a ')) {
                $fechas = explode(' a ', $fechaFiltro);
                $start = trim($fechas[0]);
                $end = trim($fechas[1] ?? $fechas[0]);
                $query->whereBetween('fecha', [$start.' 00:00:00', $end.' 23:59:59']);
            } else {
                $query->whereDate('fecha', trim($fechaFiltro));
            }
        }

        if (! empty($this->filtros['tipo_pago_id'])) {
            $query->where('tipo_pago_id', $this->filtros['tipo_pago_id']);
        }

        if (! empty($this->filtros['actividad_id'])) {
            $query->whereHas('compra', function ($q) {
                $q->where('actividad_id', $this->filtros['actividad_id']);
            });
        }

        if (! empty($this->filtros['busqueda'])) {
            $query->whereHas('compra', function ($q) {
                $q->where('nombre_completo_comprador', 'like', '%'.$this->filtros['busqueda'].'%')
                    ->orWhere('identificacion_comprador', 'like', '%'.$this->filtros['busqueda'].'%')
                    ->orWhere('email_comprador', 'like', '%'.$this->filtros['busqueda'].'%');
            });
        }

        $pagos = $query->with(['compra.actividad', 'tipoPago', 'caja', 'estadoPago', 'actividadCategoria'])->orderBy('created_at', 'desc')->get();

        return view('contenido.paginas.taquillas.exportar.excel-historial', [
            'pagos' => $pagos,
        ]);
    }
}
