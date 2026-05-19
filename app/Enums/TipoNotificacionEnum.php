<?php

namespace App\Enums;

enum TipoNotificacionEnum: string
{
    case CONFIRMACION = 'confirmacion';
    case RECORDATORIO = 'recordatorio';
    case CANCELACION = 'cancelacion';
    case MODIFICACION = 'modificacion';
    case MENSAJE = 'mensaje';
    case OTRO = 'otro';

    public static function valores(): array
    {
        return array_column(self::cases(), 'value');
    }
}
