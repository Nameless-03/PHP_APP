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
            self::PENDIENTE => 'Pendiente',
            self::CONFIRMADA => 'Confirmada',
            self::PAGADA => 'Pagada',
            self::EN_CURSO => 'En curso',
            self::FINALIZADA => 'Finalizada',
            self::CANCELADA => 'Cancelada',
            self::NO_ASISTIDA => 'No asistida',
        };
    }

    /**
     * Verifica si la reserva puede ser modificada.
     */
    public function puedeModificarse(): bool
    {
        return match($this) {
            self::PENDIENTE, self::CONFIRMADA => true,
            default => false,
        };
    }

    /**
     * Verifica si la reserva puede ser cancelada.
     */
    public function puedeCancelarse(): bool
    {
        return match($this) {
            self::PENDIENTE, self::CONFIRMADA, self::PAGADA => true,
            default => false,
        };
    }

    /**
     * Verifica si la reserva está activa.
     */
    public function estaActiva(): bool
    {
        return match($this) {
            self::CONFIRMADA, self::PAGADA, self::EN_CURSO => true,
            default => false,
        };
    }

    /**
     * Verifica si la reserva está finalizada.
     */
    public function estaFinalizada(): bool
    {
        return match($this) {
            self::FINALIZADA, self::CANCELADA, self::NO_ASISTIDA => true,
            default => false,
        };
    }

    /**
     * Obtiene el siguiente estado posible en el ciclo de vida.
     */
    public function siguienteEstado(): ?self
    {
        return match($this) {
            self::PENDIENTE => self::CONFIRMADA,
            self::CONFIRMADA => self::PAGADA,
            self::PAGADA => self::EN_CURSO,
            self::EN_CURSO => self::FINALIZADA,
            default => null,
        };
    }
}
