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

        if (str_contains($this->filtros['fecha'], ' to ')) {
            $fechas = explode(' to ', $this->filtros['fecha']);
            $query->whereBetween('fecha', [$fechas[0].' 00:00:00', $fechas[1].' 23:59:59']);
        } else {
            $query->whereDate('fecha', $this->filtros['fecha']);
        }

        if (!empty($this->filtros['tipo_pago_id'])) {
            $query->where('tipo_pago_id', $this->filtros['tipo_pago_id']);
        }

        if (!empty($this->filtros['actividad_id'])) {
            $query->whereHas('compra', function ($q) {
                $q->where('actividad_id', $this->filtros['actividad_id']);
            });
        }
        
        if (!empty($this->filtros['busqueda'])) {
            $query->whereHas('compra', function ($q) {
                $q->where('nombre_completo_comprador', 'like', '%'.$this->filtros['busqueda'].'%')
                  ->orWhere('identificacion_comprador', 'like', '%'.$this->filtros['busqueda'].'%')
                  ->orWhere('email_comprador', 'like', '%'.$this->filtros['busqueda'].'%');
            });
        }

        $pagos = $query->with(['compra.actividad', 'tipoPago', 'caja', 'estadoPago', 'actividadCategoria'])->orderBy('created_at', 'desc')->get();

        return view('contenido.paginas.taquillas.exportar.excel-historial', [
            'pagos' => $pagos
        ]);
    }
}
