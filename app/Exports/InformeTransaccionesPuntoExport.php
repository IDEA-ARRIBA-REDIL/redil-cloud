<?php

namespace App\Exports;

use App\Models\Pago;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\DB;

class InformeTransaccionesPuntoExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $fechaInicio;
    protected $fechaFin;
    protected $cajasSeleccionadas;
    protected $estadoTransaccion;

    public function __construct($fechaInicio, $fechaFin, $cajasSeleccionadas, $estadoTransaccion)
    {
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
        $this->cajasSeleccionadas = $cajasSeleccionadas;
        $this->estadoTransaccion = $estadoTransaccion;
    }

    public function collection()
    {
        $query = Pago::query()
            ->with(['compra.user', 'caja.usuario', 'tipoPago', 'estadoPago'])
            ->whereIn('registro_caja_id', $this->cajasSeleccionadas)
            ->whereBetween(DB::raw('DATE(created_at)'), [$this->fechaInicio, $this->fechaFin]);

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

        return $query->latest()->get();
    }

    public function headings(): array
    {
        return [
            'ID Transacción',
            'Fecha',
            'Hora',
            'Caja',
            'Encargado Caja',
            'Tipo de Pago',
            'Voucher',
            'Valor',
            'Estado',
            'Comprador',
            'Identificación Comprador',
            'Email Comprador',
        ];
    }

    public function map($pago): array
    {
        $estado = 'Desconocido';
        if ($pago->estadoPago) {
            $estado = $pago->estadoPago->nombre;
        } elseif ($pago->compra && $pago->compra->estadoPago) {
             $estado = $pago->compra->estadoPago->nombre;
        }

        return [
            $pago->id,
            $pago->created_at->format('Y-m-d'),
            $pago->created_at->format('H:i:s'),
            $pago->caja->nombre ?? 'N/A',
            $pago->caja->usuario->nombre(2) ?? 'Sin usuario',
            $pago->tipoPago->nombre ?? 'N/A',
            $pago->codigo_vaucher ?? '',
            $pago->valor,
            $estado,
            $pago->compra->nombre_completo_comprador ?? 'N/A',
            $pago->compra->identificacion_comprador ?? 'N/A',
            $pago->compra->email_comprador ?? 'N/A',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1    => ['font' => ['bold' => true]],
        ];
    }
}
