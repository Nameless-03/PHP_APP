<?php

namespace App\DataTypes;

class DtReserva
{
    public function __construct(
        public readonly int $id,
        public readonly string $fechaHoraInicio,
        public readonly string $fechaHoraFin,
        public readonly string $estado,
        public readonly ?string $observaciones,
        public readonly int $idCliente,
        public readonly string $nombreCliente,
        public readonly int $idServicio,
        public readonly string $nombreServicio,
        public readonly string $nombreProfesional,
        public readonly ?float $montoPago = null,
        public readonly ?string $estadoPago = null,
    ) {}

    /**
     * Crea una instancia desde un modelo Eloquent.
     */
    public static function desdeModelo($reserva): self
    {
        return new self(
            id: $reserva->id,
            fechaHoraInicio: $reserva->fecha_hora_inicio->format('Y-m-d H:i:s'),
            fechaHoraFin: $reserva->fecha_hora_fin->format('Y-m-d H:i:s'),
            estado: $reserva->estado,
            observaciones: $reserva->observaciones,
            idCliente: $reserva->id_cliente,
            nombreCliente: $reserva->cliente->usuario->nombre,
            idServicio: $reserva->id_servicio,
            nombreServicio: $reserva->servicio->nombre,
            nombreProfesional: $reserva->servicio->profesional->usuario->nombre,
            montoPago: $reserva->pago ? (float) $reserva->pago->monto : null,
            estadoPago: $reserva->pago?->estado,
        );
    }

    /**
     * Convierte a array.
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'fecha_hora_inicio' => $this->fechaHoraInicio,
            'fecha_hora_fin' => $this->fechaHoraFin,
            'estado' => $this->estado,
            'observaciones' => $this->observaciones,
            'id_cliente' => $this->idCliente,
            'nombre_cliente' => $this->nombreCliente,
            'id_servicio' => $this->idServicio,
            'nombre_servicio' => $this->nombreServicio,
            'nombre_profesional' => $this->nombreProfesional,
            'monto_pago' => $this->montoPago,
            'estado_pago' => $this->estadoPago,
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
