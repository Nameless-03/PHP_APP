<?php

namespace App\DataTypes;

class DtProfesional
{
    public function __construct(
        public readonly int $idUsuario,
        public readonly string $nombre,
        public readonly string $email,
        public readonly ?string $descripcion,
        public readonly ?string $experiencia,
        public readonly ?string $ubicacion,
        public readonly string $modalidadPreferida,
        public readonly float $reputacion,
        public readonly string $fechaRegistro,
    ) {}

    /**
     * Crea una instancia desde un modelo Eloquent.
     */
    public static function desdeModelo($profesional): self
    {
        return new self(
            idUsuario: $profesional->id_usuario,
            nombre: $profesional->usuario->nombre,
            email: $profesional->usuario->email,
            descripcion: $profesional->descripcion,
            experiencia: $profesional->experiencia,
            ubicacion: $profesional->ubicacion,
            modalidadPreferida: $profesional->modalidad_preferida,
            reputacion: (float) $profesional->reputacion,
            fechaRegistro: $profesional->usuario->fecha_registro->format('Y-m-d H:i:s'),
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
            'descripcion' => $this->descripcion,
            'experiencia' => $this->experiencia,
            'ubicacion' => $this->ubicacion,
            'modalidad_preferida' => $this->modalidadPreferida,
            'reputacion' => $this->reputacion,
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
