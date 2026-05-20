<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaqueteResource extends JsonResource
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
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'cantidad_sesiones' => (int) $this->cantidad_sesiones,
            'precio' => (float) $this->precio,
            'vencimiento' => $this->vencimiento ? (int) $this->vencimiento : null,
            'id_profesional' => (int) $this->id_profesional,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            
            // Relación con servicios
            'servicios' => ServicioResource::collection($this->whenLoaded('servicios')),
        ];
    }
}
