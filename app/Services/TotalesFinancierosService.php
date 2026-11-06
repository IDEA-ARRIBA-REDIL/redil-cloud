<?php

namespace App\Services;

use App\Models\Moneda;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class TotalesFinancierosService
{
    public function porMoneda(Builder $consulta): Collection
    {
        $tabla = $consulta->getModel()->getTable();
        $totales = (clone $consulta)->withoutEagerLoads()->reorder()
            ->select($tabla.'.moneda_id')
            ->selectRaw('SUM('.$tabla.'.valor) AS total, COUNT(*) AS cantidad')
            ->groupBy($tabla.'.moneda_id')
            ->toBase()->get();
        $monedas = Moneda::query()->whereIn('id', $totales->pluck('moneda_id')->filter())->pluck('nombre', 'id');

        return $totales->mapWithKeys(fn (object $total): array => [
            $total->moneda_id ?? '' => [
                'moneda' => $monedas->get($total->moneda_id, 'Desconocida'),
                'total' => ImporteMonetario::normalizar($total->total),
                'count' => (int) $total->cantidad,
            ],
        ]);
    }
}
