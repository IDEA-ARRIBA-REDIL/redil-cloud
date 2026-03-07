<?php

namespace App\Exports;

use App\Models\HistorialModificacionPago;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\Exportable;

class HistorialModificacionesExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    protected $filtros;

    public function __construct($filtros = [])
    {
        $this->filtros = $filtros;
    }

    public function query()
    {
        $query = HistorialModificacionPago::query()
            ->with(['asesor', 'caja', 'puntoDePago', 'compra', 'pago', 'usuarioAfectado', 'actividad', 'categoriaActividad', 'tipoPago']);

        // Filtro por Búsqueda
        if (!empty($this->filtros['busqueda'])) {
            $busqueda = $this->filtros['busqueda'];
            $query->where(function ($q) use ($busqueda) {
                $q->whereHas('usuarioAfectado', function ($subQ) use ($busqueda) {
                    $subQ->where('nombres', 'like', '%' . $busqueda . '%')
                         ->orWhere('apellidos', 'like', '%' . $busqueda . '%')
                         ->orWhere('identificacion', 'like', '%' . $busqueda . '%');
                })
                ->orWhereHas('asesor', function ($subQ) use ($busqueda) {
                    $subQ->where('nombres', 'like', '%' . $busqueda . '%')
                         ->orWhere('apellidos', 'like', '%' . $busqueda . '%');
                })
                ->orWhere('motivo', 'like', '%' . $busqueda . '%');
            });
        }

        // Filtro por Punto de Pago
        if (!empty($this->filtros['puntoPagoId'])) {
            $query->where('punto_de_pago_id', $this->filtros['puntoPagoId']);
        }

        // Filtro por Caja
        if (!empty($this->filtros['cajaId'])) {
            $query->where('caja_id', $this->filtros['cajaId']);
        }

        // Filtro por Fecha (Default hoy si no se especifica, pero el componente siempre manda algo)
        if (!empty($this->filtros['fecha'])) {
            $query->whereDate('created_at', $this->filtros['fecha']);
        }

        return $query->latest();
    }

    public function headings(): array
    {
        return [
            'Fecha',
            'Usuario Afectado',
            'Identificación',
            'Asesor',
            'Caja',
            'Punto de Pago',
            'Actividad',
            'Compra ID',
            'Valor',
            'Motivo',
        ];
    }

    public function map($modificacion): array
    {
        return [
            $modificacion->created_at->format('d/m/Y H:i'),
            $modificacion->usuarioAfectado->nombre(3) ?? 'N/A',
            $modificacion->usuarioAfectado->identificacion ?? 'N/A',
            $modificacion->asesor->nombre(3) ?? 'N/A',
            $modificacion->caja->nombre ?? 'N/A',
            $modificacion->puntoDePago->nombre ?? 'N/A',
            $modificacion->actividad->nombre ?? 'N/A',
            $modificacion->compra_id,
            $modificacion->valor,
            $modificacion->motivo,
        ];
    }
}
