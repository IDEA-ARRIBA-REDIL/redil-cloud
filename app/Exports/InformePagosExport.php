<?php

namespace App\Exports;

use App\Models\Pago;
use App\Models\Moneda; // Added for Moneda::find(1)
use Carbon\Carbon; // Added for age calculation
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class InformePagosExport implements FromCollection, WithHeadings, WithMapping
{
    protected $monedaDefault;
    protected $filters;

    public function __construct($filters)
    {
        $this->filters = $filters;
        $this->monedaDefault = Moneda::find(1);
    }

    public function collection()
    {
        $query = Pago::with([
            'compra.user',
            'compra.actividad',
            'moneda',
            'tipoPago',
            'estadoPago',
            'compra.inscripciones.user',
            'compra.inscripciones.categoriaActividad'
        ])
            ->orderBy('fecha', 'asc')
            ->orderBy('id', 'asc');

        // Apply filters
        if (!empty($this->filters['actividad'])) {
            $query->whereHas('compra', function($q) {
                $q->where('actividad_id', $this->filters['actividad']);
            });
        }

        if (!empty($this->filters['user_id'])) {
            $query->whereHas('compra', function($q) {
                $q->where('user_id', $this->filters['user_id']);
            });
        }

        if (!empty($this->filters['grupo_id'])) {
            $query->whereHas('compra.user.gruposDondeAsiste', function($q) {
                $q->where('grupos.id', $this->filters['grupo_id']);
            });
        }

        if (!empty($this->filters['sucursales'])) {
            $query->whereHas('compra', function($q) {
                $q->whereIn('destinatario_id', $this->filters['sucursales']);
            });
        }

        if (!empty($this->filters['estado_pago_id'])) {
            $query->where('estado_pago_id', $this->filters['estado_pago_id']);
        }

        if (!empty($this->filters['tipo_pago_id'])) {
            $query->where('tipo_pago_id', $this->filters['tipo_pago_id']);
        }

        if (!empty($this->filters['moneda_id'])) {
            $query->where('moneda_id', $this->filters['moneda_id']);
        }

        if (!empty($this->filters['fecha_inicio'])) {
            $query->whereDate('fecha', '>=', $this->filters['fecha_inicio']);
        }

        if (!empty($this->filters['fecha_fin'])) {
            $query->whereDate('fecha', '<=', $this->filters['fecha_fin']);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'ID Pago interno',
            'Fecha',
            'Ref. Transacción',
            'Actividad',
            'Usuario (Comprador)',
            'Identificación',
            'Inscrito(s)',
            'ID Inscrito(s)',
            'Edad Inscrito(s)',
            'Categoría(s)',
            'Valor',
            'Moneda',
            'Tipo Pago',
            'Estado Pago',
            'ID Compra interna'
        ];
    }

    public function map($pago): array
    {
        $compra = $pago->compra;
        $inscritosNames = [];
        $idsInscritos = [];
        $edadesInscritos = [];
        $categoriasNames = [];

        if ($compra && $compra->inscripciones) {
            foreach($compra->inscripciones as $inscripcion) {
                $inscritosNames[] = $inscripcion->nombre_inscrito;
                $categoriasNames[] = $inscripcion->categoriaActividad->nombre ?? '';

                $userTarget = null;
                if ($inscripcion->user) {
                    $userTarget = $inscripcion->user;
                } elseif ($compra->user) {
                    $userTarget = $compra->user;
                }

                if ($userTarget) {
                    $idsInscritos[] = $userTarget->identificacion;
                    try {
                        $edadesInscritos[] = Carbon::parse($userTarget->fecha_nacimiento)->age;
                    } catch (\Exception $e) {
                        $edadesInscritos[] = 'N/A';
                    }
                } else {
                    $idsInscritos[] = 'N/A';
                    $edadesInscritos[] = 'N/A';
                }
            }
        }

        $monedaNombre = $pago->moneda ? $pago->moneda->nombre : ($this->monedaDefault ? $this->monedaDefault->nombre : 'N/A');

        return [
            $pago->id,
            $pago->fecha,
            $pago->referencia_pago,
            $compra->actividad->nombre ?? 'N/A',
            $compra->user ? $compra->user->nombre(3) : 'N/A',
            $compra->user ? $compra->user->identificacion : 'N/A',
            implode(', ', array_filter($inscritosNames)),
            implode(', ', $idsInscritos),
            implode(', ', $edadesInscritos),
            implode(', ', array_unique(array_filter($categoriasNames))),
            $pago->valor,
            $monedaNombre,
            $pago->tipoPago->nombre ?? 'N/A',
            $pago->estadoPago->nombre ?? 'N/A',
            $pago->compra_id,
        ];
    }
}
