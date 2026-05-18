<?php

namespace App\DataTypes;

class DtNotificacion
{
    public function __construct(
        public readonly int $id,
        public readonly string $titulo,
        public readonly string $mensaje,
        public readonly string $tipo,
        public readonly bool $leida,
        public readonly string $fecha,
        public readonly int $idUsuario,
    ) {}

    /**
     * Crea una instancia desde un modelo Eloquent.
     */
    public static function desdeModelo($notificacion): self
    {
        return new self(
            id: $notificacion->id,
            titulo: $notificacion->titulo,
            mensaje: $notificacion->mensaje,
            tipo: $notificacion->tipo,
            leida: $notificacion->leida,
            fecha: $notificacion->fecha->format('Y-m-d H:i:s'),
            idUsuario: $notificacion->id_usuario,
        );
    }

    /**
     * Convierte a array.
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'titulo' => $this->titulo,
            'mensaje' => $this->mensaje,
            'tipo' => $this->tipo,
            'leida' => $this->leida,
            'fecha' => $this->fecha,
            'id_usuario' => $this->idUsuario,
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
