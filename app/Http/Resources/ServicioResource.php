<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServicioResource extends JsonResource
{
    /**
     * Transforma el recurso en un arreglo.
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
            'limite_cancelacion_horas' => (int) $this->limite_cancelacion_horas,
            'created_at' => $this->created_at?->toIso8601String(),
            
            // Relaciones opcionales
            'profesional' => $this->profesional ? [
                    'id_usuario' => $this->profesional->id_usuario,
                    'reputacion' => (float) $this->profesional->reputacion,
                    'telefono' => $this->profesional->telefono,
                    'nombre' => $this->profesional->usuario ? $this->profesional->usuario->nombre : null,
                    'foto_perfil' => $this->profesional->foto_perfil
                        ? (str_starts_with($this->profesional->foto_perfil, 'data:') || filter_var($this->profesional->foto_perfil, FILTER_VALIDATE_URL)
                            ? $this->profesional->foto_perfil
                            : asset('storage/' . $this->profesional->foto_perfil))
                        : null,
                ] : null,
            'categoria' => $this->whenLoaded('categoria', function () {
                return [
                    'id' => $this->categoria->id,
                    'nombre' => $this->categoria->nombre,
                ];
            }),
        ];
    }
}
