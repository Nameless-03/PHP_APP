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

    public static function valores(): array
    {
        return array_column(self::cases(), 'value');
    }
}
