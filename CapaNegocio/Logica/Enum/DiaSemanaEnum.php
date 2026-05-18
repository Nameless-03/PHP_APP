<?php

namespace App\Enums;

enum DiaSemanaEnum: string
{
    case LUNES = 'lunes';
    case MARTES = 'martes';
    case MIERCOLES = 'miercoles';
    case JUEVES = 'jueves';
    case VIERNES = 'viernes';
    case SABADO = 'sabado';
    case DOMINGO = 'domingo';

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
     * Obtiene la descripción del día.
     */
    public function descripcion(): string
    {
        return match($this) {
            self::LUNES => 'Lunes',
            self::MARTES => 'Martes',
            self::MIERCOLES => 'Miércoles',
            self::JUEVES => 'Jueves',
            self::VIERNES => 'Viernes',
            self::SABADO => 'Sábado',
            self::DOMINGO => 'Domingo',
        };
    }

    /**
     * Obtiene el número del día (1 = lunes, 7 = domingo).
     */
    public function numero(): int
    {
        return match($this) {
            self::LUNES => 1,
            self::MARTES => 2,
            self::MIERCOLES => 3,
            self::JUEVES => 4,
            self::VIERNES => 5,
            self::SABADO => 6,
            self::DOMINGO => 7,
        };
    }

    /**
     * Verifica si es día de fin de semana.
     */
    public function esFinDeSemana(): bool
    {
        return match($this) {
            self::SABADO, self::DOMINGO => true,
            default => false,
        };
    }

    /**
     * Obtiene el enum desde un número de día.
     */
    public static function desdNumero(int $numero): ?self
    {
        return match($numero) {
            1 => self::LUNES,
            2 => self::MARTES,
            3 => self::MIERCOLES,
            4 => self::JUEVES,
            5 => self::VIERNES,
            6 => self::SABADO,
            7 => self::DOMINGO,
            default => null,
        };
    }

    /**
     * Obtiene el enum desde el nombre de día en PHP (DateTime->format('l')).
     */
    public static function desdeNombrePHP(string $nombre): ?self
    {
        return match(strtolower($nombre)) {
            'monday' => self::LUNES,
            'tuesday' => self::MARTES,
            'wednesday' => self::MIERCOLES,
            'thursday' => self::JUEVES,
            'friday' => self::VIERNES,
            'saturday' => self::SABADO,
            'sunday' => self::DOMINGO,
            default => null,
        };
    }
}
