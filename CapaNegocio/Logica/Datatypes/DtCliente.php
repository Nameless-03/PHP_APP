<?php

namespace App\DataTypes;

class DtCliente
{
    public function __construct(
        public readonly int $idUsuario,
        public readonly string $nombre,
        public readonly string $email,
        public readonly ?string $telefono,
        public readonly ?string $fotoPerfil,
        public readonly string $fechaRegistro,
    ) {}

    /**
     * Crea una instancia desde un modelo Eloquent.
     */
    public static function desdeModelo($cliente): self
    {
        return new self(
            idUsuario: $cliente->id_usuario,
            nombre: $cliente->usuario->nombre,
            email: $cliente->usuario->email,
            telefono: $cliente->telefono,
            fotoPerfil: $cliente->foto_perfil,
            fechaRegistro: $cliente->usuario->fecha_registro->format('Y-m-d H:i:s'),
        );
    }

    /**
     * Convierte a array.
     */
    public function toArray(): array
    {
        return [
            'id_usuario' => $this->idUsuario,
            'nombre' => $this->nombre,
            'email' => $this->email,
            'telefono' => $this->telefono,
            'foto_perfil' => $this->fotoPerfil,
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
