<?php

namespace App\Services;

use App\Models\Servicio;
use App\Models\Disponibilidad;
use App\Models\ExcepcionAgenda;
use App\Models\Reserva;
use App\Enums\EstadoReservaEnum;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CalculadorTurnosService
{
    /**
     * Calcula los turnos disponibles para un servicio en una fecha específica.
     */
    public function obtenerTurnosDisponibles(Servicio $servicio, string $fechaStr): Collection
    {
        $fecha = Carbon::parse($fechaStr);
        $idProfesional = $servicio->id_profesional;

        // 1. Verificar Excepciones (Feriados, vacaciones, etc)
        $excepcion = ExcepcionAgenda::where('id_profesional', $idProfesional)
            ->whereDate('fecha', $fecha)
            ->first();

        // Si hay una excepción y el profesional NO está disponible ese día
        if ($excepcion && !$excepcion->disponible) {
            return collect(); // Retorna vacío
        }

        // 2. Obtener Disponibilidad semanal base
        // Mapear día de la semana (0 = domingo, 1 = lunes...) al enum DiaSemanaEnum
        $nombresDias = [
            0 => 'domingo',
            1 => 'lunes',
            2 => 'martes',
            3 => 'miercoles',
            4 => 'jueves',
            5 => 'viernes',
            6 => 'sabado'
        ];
        $diaSemanaString = $nombresDias[$fecha->dayOfWeek];

        $disponibilidad = Disponibilidad::where('id_profesional', $idProfesional)
            ->where('dia_semana', $diaSemanaString)
            ->first();

        if (!$disponibilidad) {
            return collect(); // No atiende este día
        }

        // 3. Generar franjas (slots)
        $horaInicio = Carbon::parse($fechaStr . ' ' . $disponibilidad->hora_inicio);
        $horaFin = Carbon::parse($fechaStr . ' ' . $disponibilidad->hora_fin);
        
        $duracionMinutos = $servicio->duracion;
        $bufferMinutos = $disponibilidad->buffer_minutos ?? 0;
        $pasoTotal = $duracionMinutos + $bufferMinutos;

        // Si la hora actual es posterior a horaInicio (para el día de hoy), ajustar
        if ($fecha->isToday() && now()->greaterThan($horaInicio)) {
            $horaInicio = now()->addMinutes(15)->ceilMinute(15); // Redondear al próximo cuarto de hora
        }

        $turnosGenerados = collect();

        $horaActualIteracion = clone $horaInicio;

        // Bucle para generar bloques
        while ($horaActualIteracion->copy()->addMinutes($duracionMinutos)->lessThanOrEqualTo($horaFin)) {
            $finTurno = $horaActualIteracion->copy()->addMinutes($duracionMinutos);
            
            $turnosGenerados->push([
                'inicio' => $horaActualIteracion->format('H:i'),
                'fin' => $finTurno->format('H:i'),
                'inicio_datetime' => clone $horaActualIteracion,
                'fin_datetime' => clone $finTurno,
            ]);

            $horaActualIteracion->addMinutes($pasoTotal);
        }

        // 4. Filtrar los turnos que se solapan con reservas existentes
        // Traer reservas del profesional en esa fecha (que estén pendientes, confirmadas o pagadas)
        $reservasExistentes = Reserva::whereHas('servicio', function ($q) use ($idProfesional) {
                $q->where('id_profesional', $idProfesional);
            })
            ->whereDate('fecha_hora_inicio', $fecha)
            ->whereIn('estado', [
                EstadoReservaEnum::PENDIENTE->value, 
                EstadoReservaEnum::CONFIRMADA->value, 
                EstadoReservaEnum::PAGADA->value
            ])
            ->get();

        $turnosDisponibles = $turnosGenerados->filter(function ($turno) use ($reservasExistentes) {
            $turnoInicio = $turno['inicio_datetime'];
            $turnoFin = $turno['fin_datetime'];

            foreach ($reservasExistentes as $reserva) {
                $reservaInicio = Carbon::parse($reserva->fecha_hora_inicio);
                $reservaFin = Carbon::parse($reserva->fecha_hora_fin);

                // Comprobar solapamiento: (InicioA < FinB) y (FinA > InicioB)
                if ($turnoInicio->lessThan($reservaFin) && $turnoFin->greaterThan($reservaInicio)) {
                    return false; // Se solapa, no está disponible
                }
            }
            return true;
        });

        // Formatear la salida final
        return $turnosDisponibles->map(function ($turno) {
            return $turno['inicio'];
        })->values();
    }
}
