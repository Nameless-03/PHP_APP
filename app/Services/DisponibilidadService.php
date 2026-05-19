<?php

namespace App\Services;

use App\Models\Disponibilidad;
use Illuminate\Database\Eloquent\Collection;
use Exception;

class DisponibilidadService
{
    /**
     * Crear una nueva franja de disponibilidad.
     */
    public function crear(array $data): Disponibilidad
    {
        // Evitar solapamientos simples por día
        $existe = Disponibilidad::where('id_profesional', $data['id_profesional'])
            ->where('dia_semana', $data['dia_semana'])
            ->exists();

        if ($existe) {
            throw new Exception("Ya existe una disponibilidad configurada para el día seleccionado.");
        }

        return Disponibilidad::create($data);
    }

    /**
     * Listar disponibilidad de un profesional.
     */
    public function listarPorProfesional(int $idProfesional): Collection
    {
        return Disponibilidad::where('id_profesional', $idProfesional)->get();
    }

    /**
     * Actualizar disponibilidad.
     */
    public function actualizar(Disponibilidad $disponibilidad, array $data): Disponibilidad
    {
        $disponibilidad->update($data);
        return $disponibilidad->fresh();
    }

    /**
     * Eliminar disponibilidad.
     */
    public function eliminar(Disponibilidad $disponibilidad): bool
    {
        return $disponibilidad->delete();
    }
}
