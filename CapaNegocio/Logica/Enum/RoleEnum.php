<?php

namespace App\Enums;

enum RoleEnum: string
{
    case CLIENTE = 'cliente';
    case PROFESIONAL = 'profesional';
    case ADMIN = 'admin';

    /**
     * Obtiene todos los valores posibles.
     */
    public static function valores(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Verifica si un valor es válido.
     */
    public static function esValido(string $valor): bool
    {
        return in_array($valor, self::valores());
    }

    /**
     * Obtiene la descripción del rol.
     */
    public function descripcion(): string
    {
        return match($this) {
            self::CLIENTE => 'Cliente',
            self::PROFESIONAL => 'Profesional',
            self::ADMIN => 'Administrador',
        };
    }
}
