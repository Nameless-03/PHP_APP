<?php

namespace App\Services;

use App\Models\Servicio;
use Illuminate\Database\Eloquent\Collection;

class ServicioService
{
    /**
     * Crear un nuevo servicio.
     */
    public function crear(array $data): Servicio
    {
        // El id_profesional vendrá inyectado desde el Controller (auth()->id())
        return Servicio::create($data);
    }

    /**
     * Obtener un servicio por ID.
     */
    public function obtenerPorId(int $id): Servicio
    {
        return Servicio::with(['profesional.usuario', 'categoria'])->findOrFail($id);
    }

    /**
     * Obtener todos los servicios (con paginación o filtros en el futuro).
     */
    public function listarTodos(array $filtros = []): Collection
    {
        $query = Servicio::with(['profesional.usuario', 'categoria'])->where('activo', true);

        if (isset($filtros['modalidad'])) {
            $query->where('modalidad', $filtros['modalidad']);
        }

        if (isset($filtros['precio_min']) && isset($filtros['precio_max'])) {
            $query->whereBetween('precio', [$filtros['precio_min'], $filtros['precio_max']]);
        }

        if (isset($filtros['keyword'])) {
            $query->where('nombre', 'LIKE', '%' . $filtros['keyword'] . '%');
        }

        return $query->get();
    }

    /**
     * Actualizar un servicio existente.
     */
    public function actualizar(Servicio $servicio, array $data): Servicio
    {
        $servicio->update($data);
        return $servicio->fresh(['profesional.usuario', 'categoria']);
    }

    /**
     * Eliminar (soft delete) un servicio.
     */
    public function eliminar(Servicio $servicio): bool
    {
        return $servicio->delete();
    }
}
