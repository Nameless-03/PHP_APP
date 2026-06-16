<?php

namespace App\Enums;

enum MetodoPagoEnum: string
{
    case PAYPAL = 'paypal';
    case EFECTIVO = 'efectivo';

    public static function valores(): array
    {
        return array_column(self::cases(), 'value');
    }
}
