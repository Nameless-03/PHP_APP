<?php

namespace App\Manejadores;

use App\Models\Disponibilidad;
use App\DataTypes\DtDisponibilidad;
use App\Enums\DiaSemanaEnum;
use Exception;

class ManejadorDisponibilidad
{
    /**
     * Crea una nueva disponibilidad.
     */
    public function crear(
        int $idProfesional,
        string $diaSemana,
        string $horaInicio,
        string $horaFin,
        int $pausaMinutos = 0,
        int $bufferMinutos = 0
    ): DtDisponibilidad {
        // Validar día de semana
        if (!DiaSemanaEnum::esValido($diaSemana)) {
            throw new Exception("Día de semana inválido: {$diaSemana}");
        }

        // Validar que hora inicio sea menor que hora fin
        if ($horaInicio >= $horaFin) {
            throw new Exception("La hora de inicio debe ser menor que la hora de fin");
        }

        // Verificar que no exista ya disponibilidad para ese día
        $existente = Disponibilidad::where('id_profesional', $idProfesional)
            ->where('dia_semana', $diaSemana)
            ->exists();

        if ($existente) {
            throw new Exception("Ya existe disponibilidad configurada para {$diaSemana}");
        }

        $disponibilidad = Disponibilidad::create([
            'dia_semana' => $diaSemana,
            'hora_inicio' => $horaInicio,
            'hora_fin' => $horaFin,
            'pausa_minutos' => $pausaMinutos,
            'buffer_minutos' => $bufferMinutos,
            'id_profesional' => $idProfesional,
        ]);

        return DtDisponibilidad::desdeModelo($disponibilidad);
    }

    /**
     * Obtiene una disponibilidad por ID.
     */
    public function obtenerPorId(int $id): ?DtDisponibilidad
    {
        $disponibilidad = Disponibilidad::find($id);

        return $disponibilidad ? DtDisponibilidad::desdeModelo($disponibilidad) : null;
    }

    /**
     * Lista disponibilidades de un profesional.
     */
    public function listarPorProfesional(int $idProfesional): array
    {
        return Disponibilidad::where('id_profesional', $idProfesional)
            ->orderByRaw("CASE dia_semana
                WHEN 'lunes' THEN 1
                WHEN 'martes' THEN 2
                WHEN 'miercoles' THEN 3
                WHEN 'jueves' THEN 4
                WHEN 'viernes' THEN 5
                WHEN 'sabado' THEN 6
                WHEN 'domingo' THEN 7
            END")
            ->get()
            ->map(fn($disponibilidad) => DtDisponibilidad::desdeModelo($disponibilidad))
            ->toArray();
    }

    /**
     * Obtiene la disponibilidad de un profesional para un día específico.
     */
    public function obtenerPorDia(int $idProfesional, string $diaSemana): ?DtDisponibilidad
    {
        if (!DiaSemanaEnum::esValido($diaSemana)) {
            throw new Exception("Día de semana inválido: {$diaSemana}");
        }

        $disponibilidad = Disponibilidad::where('id_profesional', $idProfesional)
            ->porDia($diaSemana)
            ->first();

        return $disponibilidad ? DtDisponibilidad::desdeModelo($disponibilidad) : null;
    }

    /**
     * Actualiza una disponibilidad.
     */
    public function actualizar(int $id, array $datos): DtDisponibilidad
    {
        $disponibilidad = Disponibilidad::findOrFail($id);

        // Validar día de semana si se actualiza
        if (isset($datos['dia_semana'])) {
            if (!DiaSemanaEnum::esValido($datos['dia_semana'])) {
                throw new Exception("Día de semana inválido: {$datos['dia_semana']}");
            }

            // Verificar que no exista ya para ese día (excepto el actual)
            $existente = Disponibilidad::where('id_profesional', $disponibilidad->id_profesional)
                ->where('dia_semana', $datos['dia_semana'])
                ->where('id', '!=', $id)
                ->exists();

            if ($existente) {
                throw new Exception("Ya existe disponibilidad configurada para {$datos['dia_semana']}");
            }
        }

        // Validar horas si se actualizan
        $horaInicio = $datos['hora_inicio'] ?? $disponibilidad->hora_inicio;
        $horaFin = $datos['hora_fin'] ?? $disponibilidad->hora_fin;

        if ($horaInicio >= $horaFin) {
            throw new Exception("La hora de inicio debe ser menor que la hora de fin");
        }

        $disponibilidad->update($datos);

        return DtDisponibilidad::desdeModelo($disponibilidad->fresh());
    }

    /**
     * Elimina una disponibilidad.
     */
    public function eliminar(int $id): bool
    {
        $disponibilidad = Disponibilidad::findOrFail($id);

        return $disponibilidad->delete();
    }

    /**
     * Elimina todas las disponibilidades de un profesional.
     */
    public function eliminarTodasDelProfesional(int $idProfesional): int
    {
        return Disponibilidad::where('id_profesional', $idProfesional)->delete();
    }

    /**
     * Crea disponibilidad para toda la semana laboral (lunes a viernes).
     */
    public function crearSemanaLaboral(
        int $idProfesional,
        string $horaInicio,
        string $horaFin,
        int $pausaMinutos = 0,
        int $bufferMinutos = 0
    ): array {
        $diasLaborales = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes'];
        $disponibilidades = [];

        foreach ($diasLaborales as $dia) {
            try {
                $disponibilidades[] = $this->crear(
                    $idProfesional,
                    $dia,
                    $horaInicio,
                    $horaFin,
                    $pausaMinutos,
                    $bufferMinutos
                );
            } catch (Exception $e) {
                // Si ya existe, continuar con el siguiente día
                continue;
            }
        }

        return $disponibilidades;
    }

    /**
     * Obtiene los horarios disponibles para un profesional en una fecha específica.
     */
    public function obtenerHorariosDisponibles(
        int $idProfesional,
        string $fecha,
        int $duracionServicio
    ): array {
        // Obtener día de semana de la fecha
        $fechaObj = new \DateTime($fecha);
        $diaSemana = DiaSemanaEnum::desdeNombrePHP($fechaObj->format('l'));

        if (!$diaSemana) {
            return [];
        }

        // Obtener disponibilidad del día
        $disponibilidad = Disponibilidad::where('id_profesional', $idProfesional)
            ->where('dia_semana', $diaSemana->value)
            ->first();

        if (!$disponibilidad) {
            return [];
        }

        // Generar slots de tiempo disponibles
        $horariosDisponibles = [];
        $horaActual = new \DateTime($fecha . ' ' . $disponibilidad->hora_inicio);
        $horaFin = new \DateTime($fecha . ' ' . $disponibilidad->hora_fin);

        $duracionTotal = $duracionServicio + $disponibilidad->buffer_minutos;

        while ($horaActual < $horaFin) {
            $horaFinSlot = clone $horaActual;
            $horaFinSlot->modify("+{$duracionTotal} minutes");

            if ($horaFinSlot <= $horaFin) {
                $horariosDisponibles[] = $horaActual->format('H:i');
            }

            $horaActual->modify("+{$duracionTotal} minutes");

            // Agregar pausa si existe
            if ($disponibilidad->pausa_minutos > 0) {
                $horaActual->modify("+{$disponibilidad->pausa_minutos} minutes");
            }
        }

        return $horariosDisponibles;
    }
}
