<?php

namespace App\Enums;

enum EstadoCompraPaqueteEnum: string
{
    case ACTIVO = 'activo';
    case AGOTADO = 'agotado';
    case VENCIDO = 'vencido';

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
     * Obtiene la descripción del estado.
     */
    public function descripcion(): string
    {
        return match($this) {
            self::ACTIVO => 'Activo',
            self::AGOTADO => 'Agotado',
            self::VENCIDO => 'Vencido',
        };
    }

    /**
     * Verifica si el paquete puede ser utilizado.
     */
    public function puedeUtilizarse(): bool
    {
        return $this === self::ACTIVO;
    }

    /**
     * Verifica si el estado es final (no puede cambiar).
     */
    public function esFinal(): bool
    {
        return match($this) {
            self::AGOTADO, self::VENCIDO => true,
            self::ACTIVO => false,
        };
    }
}
