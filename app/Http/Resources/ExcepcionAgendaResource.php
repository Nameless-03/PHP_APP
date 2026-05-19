<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExcepcionAgendaResource extends JsonResource
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
            'id_profesional' => $this->id_profesional,
            'fecha' => $this->fecha->format('Y-m-d'),
            'motivo' => $this->motivo,
            'disponible' => (bool)$this->disponible,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
