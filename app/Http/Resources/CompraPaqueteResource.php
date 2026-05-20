<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompraPaqueteResource extends JsonResource
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
            'sesiones_disponibles' => (int) $this->sesiones_disponibles,
            'fecha_compra' => $this->fecha_compra?->toIso8601String(),
            'estado' => $this->estado,
            'id_cliente' => (int) $this->id_cliente,
            'id_paquete' => (int) $this->id_paquete,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),

            // Relación con el paquete comprado
            'paquete' => new PaqueteResource($this->whenLoaded('paquete')),
            
            // Relación con los pagos de la compra
            'pagos' => $this->whenLoaded('pagos'),
        ];
    }
}
