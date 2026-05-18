<?php

namespace App\DataTypes;

class DtPaquete
{
    public function __construct(
        public readonly int $id,
        public readonly string $nombre,
        public readonly int $cantidadSesiones,
        public readonly float $precio,
        public readonly ?int $vencimiento,
        public readonly int $idProfesional,
        public readonly string $nombreProfesional,
    ) {}

    /**
     * Crea una instancia desde un modelo Eloquent.
     */
    public static function desdeModelo($paquete): self
    {
        return new self(
            id: $paquete->id,
            nombre: $paquete->nombre,
            cantidadSesiones: $paquete->cantidad_sesiones,
            precio: (float) $paquete->precio,
            vencimiento: $paquete->vencimiento,
            idProfesional: $paquete->id_profesional,
            nombreProfesional: $paquete->profesional->usuario->nombre,
        );
    }

    /**
     * Convierte a array.
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'cantidad_sesiones' => $this->cantidadSesiones,
            'precio' => $this->precio,
            'vencimiento' => $this->vencimiento,
            'id_profesional' => $this->idProfesional,
            'nombre_profesional' => $this->nombreProfesional,
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
