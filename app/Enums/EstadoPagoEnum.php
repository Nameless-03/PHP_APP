<?php

namespace App\Enums;

enum EstadoPagoEnum: string
{
    case PENDIENTE = 'pendiente';
    case COMPLETADO = 'completado';
    case FALLIDO = 'fallido';
    case REEMBOLSADO = 'reembolsado';

    public static function valores(): array
    {
        return array_column(self::cases(), 'value');
    }
}
