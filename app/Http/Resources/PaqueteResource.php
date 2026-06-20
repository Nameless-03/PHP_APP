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
        $pivotCount = \DB::table('paquete_servicio')->where('id_paquete', $this->id)->count();
        $activeCount = \DB::table('paquete_servicio')
            ->join('servicios', 'paquete_servicio.id_servicio', '=', 'servicios.id')
            ->where('paquete_servicio.id_paquete', $this->id)
            ->where('servicios.activo', true)
            ->whereNull('servicios.deleted_at')
            ->count();
        $activo = ($pivotCount > 0 && $pivotCount === $activeCount);

        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'cantidad_sesiones' => (int) $this->cantidad_sesiones,
            'precio' => (float) $this->precio,
            'descuento' => (float) ($this->descuento ?? 0.00),
            'vencimiento' => $this->vencimiento ? (int) $this->vencimiento : null,
            'id_profesional' => (int) $this->id_profesional,
            'profesional_nombre' => $this->profesional && $this->profesional->usuario ? $this->profesional->usuario->nombre : 'Profesional',
            'activo' => $activo,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            
            // Relación con servicios mapeando la cantidad de sesiones del pivot
            'servicios' => $this->relationLoaded('servicios') ? $this->servicios->map(function ($servicio) {
                return [
                    'id' => $servicio->id,
                    'nombre' => $servicio->nombre,
                    'descripcion' => $servicio->descripcion,
                    'precio' => (float) $servicio->precio,
                    'duracion' => (int) $servicio->duracion,
                    'modalidad' => $servicio->modalidad,
                    'activo' => (bool) $servicio->activo,
                    'cantidad_sesiones' => $servicio->pivot ? (int) $servicio->pivot->cantidad_sesiones : 1,
                ];
            }) : [],
        ];
    }
}
