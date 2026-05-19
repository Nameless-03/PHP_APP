<?php

namespace App\Enums;

enum MetodoPagoEnum: string
{
    case PAYPAL = 'paypal';
    case EFECTIVO = 'efectivo';
    case TRANSFERENCIA = 'transferencia';
    case OTRO = 'otro';

    public static function valores(): array
    {
        return array_column(self::cases(), 'value');
    }
}
