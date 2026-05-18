<?php

namespace App\Enums;

enum EstadoPagoEnum: string
{
    case PENDIENTE = 'pendiente';
    case COMPLETADO = 'completado';
    case FALLIDO = 'fallido';
    case REEMBOLSADO = 'reembolsado';

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
     * Obtiene la descripción del estado de pago.
     */
    public function descripcion(): string
    {
        return match($this) {
            self::PENDIENTE => 'Pendiente',
            self::COMPLETADO => 'Completado',
            self::FALLIDO => 'Fallido',
            self::REEMBOLSADO => 'Reembolsado',
        };
    }

    /**
     * Verifica si el pago está completado.
     */
    public function estaCompletado(): bool
    {
        return $this === self::COMPLETADO;
    }

    /**
     * Verifica si el pago puede ser reembolsado.
     */
    public function puedeReembolsarse(): bool
    {
        return $this === self::COMPLETADO;
    }

    /**
     * Verifica si el pago está en un estado final.
     */
    public function esFinal(): bool
    {
        return match($this) {
            self::COMPLETADO, self::FALLIDO, self::REEMBOLSADO => true,
            self::PENDIENTE => false,
        };
    }
}
