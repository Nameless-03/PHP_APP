<?php

namespace App\Enums;

enum MetodoPagoEnum: string
{
    case PAYPAL = 'paypal';
    case EFECTIVO = 'efectivo';
    case TRANSFERENCIA = 'transferencia';
    case OTRO = 'otro';

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
     * Obtiene la descripción del método de pago.
     */
    public function descripcion(): string
    {
        return match($this) {
            self::PAYPAL => 'PayPal',
            self::EFECTIVO => 'Efectivo',
            self::TRANSFERENCIA => 'Transferencia bancaria',
            self::OTRO => 'Otro',
        };
    }

    /**
     * Verifica si el método requiere procesamiento en línea.
     */
    public function esOnline(): bool
    {
        return match($this) {
            self::PAYPAL, self::TRANSFERENCIA => true,
            self::EFECTIVO, self::OTRO => false,
        };
    }

    /**
     * Verifica si el método requiere referencia externa.
     */
    public function requiereReferenciaExterna(): bool
    {
        return match($this) {
            self::PAYPAL, self::TRANSFERENCIA => true,
            default => false,
        };
    }
}
