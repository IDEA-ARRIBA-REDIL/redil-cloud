<?php

namespace App\Services;

use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;

class ImporteMonetario
{
    public static function normalizar(string|int|float|null $valor): string
    {
        return (string) BigDecimal::of((string) ($valor ?? 0))->toScale(2, RoundingMode::HALF_UP);
    }

    public static function sumar(iterable $valores): string
    {
        $total = BigDecimal::zero();
        foreach ($valores as $valor) {
            $total = $total->plus(self::normalizar($valor));
        }

        return (string) $total->toScale(2);
    }

    public static function comparar(string|int|float|null $primero, string|int|float|null $segundo): int
    {
        return BigDecimal::of(self::normalizar($primero))->compareTo(self::normalizar($segundo));
    }
}
