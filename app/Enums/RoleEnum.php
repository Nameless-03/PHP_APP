<?php

namespace App\Enums;

enum RoleEnum: string
{
    case CLIENTE = 'cliente';
    case PROFESIONAL = 'profesional';
    case ADMIN = 'admin';

    public static function valores(): array
    {
        return array_column(self::cases(), 'value');
    }
}
