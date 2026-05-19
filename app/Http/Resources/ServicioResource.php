<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServicioResource extends JsonResource
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
            'precio' => (float) $this->precio,
            'modalidad' => $this->modalidad?->value ?? $this->modalidad,
            'duracion' => $this->duracion,
            'ubicacion' => $this->ubicacion,
            'activo' => (bool) $this->activo,
            'id_profesional' => $this->id_profesional,
            'id_categoria' => $this->id_categoria,
            'created_at' => $this->created_at?->toIso8601String(),
            
            // Relaciones opcionales
            'profesional' => $this->whenLoaded('profesional', function () {
                // Return a lightweight array or another resource
                return [
                    'id_usuario' => $this->profesional->id_usuario,
                    'reputacion' => (float) $this->profesional->reputacion,
                    // If we load nested 'usuario', we can expose the name
                    'nombre' => $this->profesional->usuario ? $this->profesional->usuario->nombre : null,
                ];
            }),
            'categoria' => $this->whenLoaded('categoria', function () {
                return [
                    'id' => $this->categoria->id,
                    'nombre' => $this->categoria->nombre,
                ];
            }),
        ];
    }
}
