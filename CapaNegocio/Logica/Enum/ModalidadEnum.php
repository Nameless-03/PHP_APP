<?php

namespace App\Enums;

enum ModalidadEnum: string
{
    case PRESENCIAL = 'presencial';
    case REMOTA = 'remota';
    case HIBRIDA = 'hibrida';

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
     * Obtiene la descripción de la modalidad.
     */
    public function descripcion(): string
    {
        return match($this) {
            self::PRESENCIAL => 'Presencial',
            self::REMOTA => 'Remota',
            self::HIBRIDA => 'Híbrida',
        };
    }

    /**
     * Verifica si la modalidad requiere ubicación física.
     */
    public function requiereUbicacion(): bool
    {
        return match($this) {
            self::PRESENCIAL, self::HIBRIDA => true,
            self::REMOTA => false,
        };
    }

    /**
     * Verifica si la modalidad requiere videollamada.
     */
    public function requiereVideollamada(): bool
    {
        return match($this) {
            self::REMOTA, self::HIBRIDA => true,
            self::PRESENCIAL => false,
        };
    }
}
