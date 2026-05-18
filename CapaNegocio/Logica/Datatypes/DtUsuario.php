<?php

namespace App\DataTypes;

class DtUsuario
{
    public function __construct(
        public readonly int $id,
        public readonly string $nombre,
        public readonly string $email,
        public readonly string $role,
        public readonly string $fechaRegistro,
    ) {}

    /**
     * Crea una instancia desde un modelo Eloquent.
     */
    public static function desdeModelo($usuario): self
    {
        return new self(
            id: $usuario->id,
            nombre: $usuario->nombre,
            email: $usuario->email,
            role: $usuario->role,
            fechaRegistro: $usuario->fecha_registro->format('Y-m-d H:i:s'),
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
            'email' => $this->email,
            'role' => $this->role,
            'fecha_registro' => $this->fechaRegistro,
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
