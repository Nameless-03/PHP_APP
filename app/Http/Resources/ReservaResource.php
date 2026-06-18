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
            'id_compra_paquete' => $this->id_compra_paquete,
            'created_at' => $this->created_at?->toIso8601String(),
            'reprogramacion_por' => \Illuminate\Support\Facades\Cache::has("reprogramada_por_cliente_{$this->id}") ? 'cliente' : null,
            'calificada' => $this->calificacion()->exists(),
            
            // Relaciones opcionales
            'cliente' => $this->whenLoaded('cliente', function () {
                return [
                    'id_usuario' => $this->cliente->id_usuario,
                    'nombre' => $this->cliente->usuario ? $this->cliente->usuario->nombre : null,
                    'email' => $this->cliente->usuario ? $this->cliente->usuario->email : null,
                    'telefono' => $this->cliente->telefono,
                ];
            }),
            'servicio' => new ServicioResource($this->whenLoaded('servicio')),
            'compra_paquete' => $this->whenLoaded('compraPaquete', function () {
                return [
                    'id' => $this->compraPaquete->id,
                    'paquete' => $this->compraPaquete->paquete ? [
                        'nombre' => $this->compraPaquete->paquete->nombre,
                    ] : null,
                ];
            }),
            'pago' => $this->whenLoaded('pago', function () {
                return [
                    'id' => $this->pago->id,
                    'monto' => $this->pago->monto,
                    'metodo' => $this->pago->metodo->value ?? $this->pago->metodo,
                    'estado' => $this->pago->estado->value ?? $this->pago->estado,
                    'referencia_externa' => $this->pago->referencia_externa,
                ];
            }),
        ];
    }
}
