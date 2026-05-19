<?php

namespace App\Enums;

enum EstadoReservaEnum: string
{
    case PENDIENTE = 'pendiente';
    case CONFIRMADA = 'confirmada';
    case PAGADA = 'pagada';
    case EN_CURSO = 'en_curso';
    case FINALIZADA = 'finalizada';
    case CANCELADA = 'cancelada';
    case NO_ASISTIDA = 'no_asistida';

    public static function valores(): array
    {
        return array_column(self::cases(), 'value');
    }
}
