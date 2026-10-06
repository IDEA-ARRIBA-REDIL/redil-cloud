<?php

namespace App\Enums;

enum EstadoInformeCola: string
{
    case Pendiente = 'pending';
    case Procesando = 'processing';
    case Completado = 'completed';
    case Fallido = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Pendiente => 'En cola',
            self::Procesando => 'Procesando',
            self::Completado => 'Completado',
            self::Fallido => 'Fallido',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Pendiente => 'bg-label-warning',
            self::Procesando => 'bg-label-info',
            self::Completado => 'bg-label-success',
            self::Fallido => 'bg-label-danger',
        };
    }
}
