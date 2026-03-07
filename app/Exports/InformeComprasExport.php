<?php

namespace App\Exports;

use App\Models\Compra;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class InformeComprasExport implements FromView
{
    protected $filters;

    public function __construct($filters)
    {
        $this->filters = $filters;
    }

    public function view(): View
    {
        $query = Compra::with([
            'user',
            'actividad',
            'metodoPago',
            'estadoPago',
            'moneda',
            'destinatario',
            'inscripciones.categoriaActividad',
            'inscripciones.user',
            'abonos',
            'pagos'
        ])
        ->orderBy('fecha', 'asc')
        ->orderBy('id', 'asc');

        // Apply filters
        if (!empty($this->filters['user_id'])) {
            $query->where('user_id', $this->filters['user_id']);
        }

        if (!empty($this->filters['actividad'])) {
            $query->where('actividad_id', $this->filters['actividad']);
        }

        if (!empty($this->filters['sucursales'])) {
            $query->whereIn('destinatario_id', $this->filters['sucursales']);
        }

         // Dates
        if (!empty($this->filters['fecha_inicio']) && !empty($this->filters['fecha_fin'])) {
            $query->whereBetween('fecha', [
                $this->filters['fecha_inicio'] . ' 00:00:00',
                $this->filters['fecha_fin'] . ' 23:59:59'
            ]);
        }

        if (!empty($this->filters['moneda_id'])) {
             $query->where('moneda_id', $this->filters['moneda_id']);
        }

        if (!empty($this->filters['grupo_id'])) {
            $grupoId = $this->filters['grupo_id'];
             $query->whereHas('user.gruposDondeAsiste', function ($q) use ($grupoId) {
                $q->where('grupo_id', $grupoId);
            });
        }

        if (!empty($this->filters['estado'])) {
             $estado = $this->filters['estado'];

             if ($estado == '4') {
                $query->whereHas('abonos');
            } else {
                $query->whereHas('estadoPago', function ($q) use ($estado) {
                    if ($estado == '1') {
                        $q->where('estado_pendiente', true);
                    } elseif ($estado == '2') {
                        $q->where('estado_final_inscripcion', true);
                    } elseif ($estado == '3') {
                        $q->where('estado_anulado_inscripcion', true);
                    }
                });
            }
        }

        $compras = $query->get();

        return view('contenido.paginas.informes.exportar.excel-compras', [
            'compras' => $compras
        ]);
    }
}
