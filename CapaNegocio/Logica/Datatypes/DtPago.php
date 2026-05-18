<?php

namespace App\DataTypes;

class DtPago
{
    public function __construct(
        public readonly int $id,
        public readonly float $monto,
        public readonly string $fecha,
        public readonly string $metodo,
        public readonly string $estado,
        public readonly ?string $referenciaExterna,
        public readonly ?int $idReserva,
        public readonly ?int $idCompra,
    ) {}

    /**
     * Crea una instancia desde un modelo Eloquent.
     */
    public static function desdeModelo($pago): self
    {
        return new self(
            id: $pago->id,
            monto: (float) $pago->monto,
            fecha: $pago->fecha->format('Y-m-d H:i:s'),
            metodo: $pago->metodo,
            estado: $pago->estado,
            referenciaExterna: $pago->referencia_externa,
            idReserva: $pago->id_reserva,
            idCompra: $pago->id_compra,
        );
    }

    /**
     * Convierte a array.
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'monto' => $this->monto,
            'fecha' => $this->fecha,
            'metodo' => $this->metodo,
            'estado' => $this->estado,
            'referencia_externa' => $this->referenciaExterna,
            'id_reserva' => $this->idReserva,
            'id_compra' => $this->idCompra,
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
