<?php

namespace App\DataTypes;

class DtDisponibilidad
{
    public function __construct(
        public readonly int $id,
        public readonly string $diaSemana,
        public readonly string $horaInicio,
        public readonly string $horaFin,
        public readonly int $pausaMinutos,
        public readonly int $bufferMinutos,
        public readonly int $idProfesional,
    ) {}

    /**
     * Crea una instancia desde un modelo Eloquent.
     */
    public static function desdeModelo($disponibilidad): self
    {
        return new self(
            id: $disponibilidad->id,
            diaSemana: $disponibilidad->dia_semana,
            horaInicio: $disponibilidad->hora_inicio,
            horaFin: $disponibilidad->hora_fin,
            pausaMinutos: $disponibilidad->pausa_minutos,
            bufferMinutos: $disponibilidad->buffer_minutos,
            idProfesional: $disponibilidad->id_profesional,
        );
    }

    /**
     * Convierte a array.
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'dia_semana' => $this->diaSemana,
            'hora_inicio' => $this->horaInicio,
            'hora_fin' => $this->horaFin,
            'pausa_minutos' => $this->pausaMinutos,
            'buffer_minutos' => $this->bufferMinutos,
            'id_profesional' => $this->idProfesional,
        ];
    }

    /**
     * Convierte a JSON.
     */
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
}
