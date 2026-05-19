<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReservaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'fecha_hora_inicio' => $this->fecha_hora_inicio?->toIso8601String(),
            'fecha_hora_fin' => $this->fecha_hora_fin?->toIso8601String(),
            'estado' => $this->estado?->value ?? $this->estado,
            'observaciones' => $this->observaciones,
            'id_cliente' => $this->id_cliente,
            'id_servicio' => $this->id_servicio,
            'created_at' => $this->created_at?->toIso8601String(),
            
            // Relaciones opcionales
            'cliente' => clone $this->whenLoaded('cliente', function () {
                return [
                    'id_usuario' => $this->cliente->id_usuario,
                    'nombre' => $this->cliente->usuario ? $this->cliente->usuario->nombre : null,
                ];
            }),
            'servicio' => new ServicioResource($this->whenLoaded('servicio')),
        ];
    }
}
