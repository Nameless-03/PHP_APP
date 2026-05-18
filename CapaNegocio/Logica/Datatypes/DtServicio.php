<?php

namespace App\DataTypes;

class DtServicio
{
    public function __construct(
        public readonly int $id,
        public readonly string $nombre,
        public readonly ?string $descripcion,
        public readonly float $precio,
        public readonly string $modalidad,
        public readonly int $duracion,
        public readonly ?string $ubicacion,
        public readonly bool $activo,
        public readonly int $idProfesional,
        public readonly string $nombreProfesional,
        public readonly int $idCategoria,
        public readonly string $nombreCategoria,
    ) {}

    /**
     * Crea una instancia desde un modelo Eloquent.
     */
    public static function desdeModelo($servicio): self
    {
        return new self(
            id: $servicio->id,
            nombre: $servicio->nombre,
            descripcion: $servicio->descripcion,
            precio: (float) $servicio->precio,
            modalidad: $servicio->modalidad,
            duracion: $servicio->duracion,
            ubicacion: $servicio->ubicacion,
            activo: $servicio->activo,
            idProfesional: $servicio->id_profesional,
            nombreProfesional: $servicio->profesional->usuario->nombre,
            idCategoria: $servicio->id_categoria,
            nombreCategoria: $servicio->categoria->nombre,
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
            'descripcion' => $this->descripcion,
            'precio' => $this->precio,
            'modalidad' => $this->modalidad,
            'duracion' => $this->duracion,
            'ubicacion' => $this->ubicacion,
            'activo' => $this->activo,
            'id_profesional' => $this->idProfesional,
            'nombre_profesional' => $this->nombreProfesional,
            'id_categoria' => $this->idCategoria,
            'nombre_categoria' => $this->nombreCategoria,
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
