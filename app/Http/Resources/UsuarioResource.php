<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UsuarioResource extends JsonResource
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
            'email' => $this->email,
            'role' => $this->role?->value ?? $this->role,
            'activo' => $this->activo ?? true,
            'fecha_registro' => $this->fecha_registro?->toIso8601String(),
            // Include related models dynamically if they are loaded
            'cliente' => $this->whenLoaded('cliente', function () {
                return [
                    'telefono' => $this->cliente->telefono,
                    'foto_perfil' => $this->cliente->foto_perfil,
                    'foto_perfil_url' => $this->cliente->foto_perfil
                        ? (str_starts_with($this->cliente->foto_perfil, 'data:') || filter_var($this->cliente->foto_perfil, FILTER_VALIDATE_URL)
                            ? $this->cliente->foto_perfil
                            : asset('storage/' . $this->cliente->foto_perfil))
                        : null,
                ];
            }),
            'profesional' => $this->whenLoaded('profesional', function () {
                return [
                    'descripcion' => $this->profesional->descripcion,
                    'experiencia' => $this->profesional->experiencia,
                    'ubicacion' => $this->profesional->ubicacion,
                    'modalidad_preferida' => $this->profesional->modalidad_preferida,
                    'reputacion' => $this->profesional->reputacion,
                    'foto_perfil' => $this->profesional->foto_perfil,
                    'foto_perfil_url' => $this->profesional->foto_perfil
                        ? (str_starts_with($this->profesional->foto_perfil, 'data:') || filter_var($this->profesional->foto_perfil, FILTER_VALIDATE_URL)
                            ? $this->profesional->foto_perfil
                            : asset('storage/' . $this->profesional->foto_perfil))
                        : null,
                ];
            }),
        ];
    }
}
