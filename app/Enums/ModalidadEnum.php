<?php

namespace App\Enums;

enum ModalidadEnum: string
{
    case PRESENCIAL = 'presencial';
    case REMOTA = 'remota';
    case HIBRIDA = 'hibrida';

    public function requiereUbicacion(): bool
    {
        return match($this) {
            self::PRESENCIAL, self::HIBRIDA => true,
            self::REMOTA => false,
        };
    }

    public static function valores(): array
    {
        return array_column(self::cases(), 'value');
    }
}
