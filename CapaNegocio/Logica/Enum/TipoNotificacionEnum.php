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
     * Obtiene la descripción del tipo de notificación.
     */
    public function descripcion(): string
    {
        return match($this) {
            self::CONFIRMACION => 'Confirmación',
            self::RECORDATORIO => 'Recordatorio',
            self::CANCELACION => 'Cancelación',
            self::MODIFICACION => 'Modificación',
            self::MENSAJE => 'Mensaje',
            self::OTRO => 'Otro',
        };
    }

    /**
     * Obtiene la prioridad de la notificación (1 = alta, 3 = baja).
     */
    public function prioridad(): int
    {
        return match($this) {
            self::CANCELACION => 1,
            self::CONFIRMACION, self::MODIFICACION => 2,
            self::RECORDATORIO, self::MENSAJE, self::OTRO => 3,
        };
    }

    /**
     * Verifica si requiere acción inmediata del usuario.
     */
    public function requiereAccion(): bool
    {
        return match($this) {
            self::CONFIRMACION, self::MODIFICACION, self::CANCELACION => true,
            default => false,
        };
    }
}
