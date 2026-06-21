<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompraPaqueteResource extends JsonResource
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

            // Mapeo del tracker de sesiones por servicio
            'servicios_tracker' => $this->relationLoaded('serviciosTracker') ? $this->serviciosTracker->map(function ($servicio) {
                return [
                    'id' => $servicio->id,
                    'nombre' => $servicio->nombre,
                    'sesiones_totales' => (int) $servicio->pivot->sesiones_totales,
                    'sesiones_disponibles' => (int) $servicio->pivot->sesiones_disponibles,
                ];
            }) : null,

            // Datos del cliente para el panel del profesional
            'cliente' => $this->relationLoaded('cliente') ? [
                'id' => $this->cliente->id,
                'nombre' => $this->cliente->usuario ? $this->cliente->usuario->nombre : 'Cliente',
            ] : null,
        ];
    }
}
