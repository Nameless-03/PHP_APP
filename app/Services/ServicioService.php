<?php

namespace App\Services;

use App\Models\Servicio;
use App\Models\Categoria;
use Illuminate\Database\Eloquent\Collection;

class ServicioService
{
    /**
     * Crear un nuevo servicio.
     */
    public function crear(array $data): Servicio
    {
        $data = $this->resolverCategoria($data);
        $servicio = Servicio::create($data);
        return $servicio->load(['profesional.usuario', 'categoria']);
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

        if (isset($filtros['id_profesional'])) {
            $query->where('id_profesional', $filtros['id_profesional']);
        }

        if (isset($filtros['modalidad'])) {
            $query->where('modalidad', $filtros['modalidad']);
        }

        if (isset($filtros['precio_min'])) {
            $query->where('precio', '>=', $filtros['precio_min']);
        }
        
        if (isset($filtros['precio_max'])) {
            $query->where('precio', '<=', $filtros['precio_max']);
        }

        if (isset($filtros['keyword'])) {
            $keyword = strtolower($filtros['keyword']);
            $query->where(function ($q) use ($keyword) {
                $q->whereRaw('LOWER(servicios.nombre) LIKE ?', ['%' . $keyword . '%'])
                  ->orWhereRaw('LOWER(servicios.descripcion) LIKE ?', ['%' . $keyword . '%'])
                  ->orWhereHas('profesional.usuario', function ($q2) use ($keyword) {
                      $q2->whereRaw('LOWER(usuarios.nombre) LIKE ?', ['%' . $keyword . '%']);
                  });
            });
        }

        if (isset($filtros['ubicacion'])) {
            $ubicacion = strtolower($filtros['ubicacion']);
            $query->whereHas('profesional', function ($q) use ($ubicacion) {
                $q->whereRaw('LOWER(ubicacion) LIKE ?', ['%' . $ubicacion . '%']);
            });
        }

        if (isset($filtros['reputacion'])) {
            $query->whereHas('profesional', function ($q) use ($filtros) {
                $q->where('reputacion', '>=', $filtros['reputacion']);
            });
        }

        return $query->get();
    }

    /**
     * Actualizar un servicio existente.
     */
    public function actualizar(Servicio $servicio, array $data): Servicio
    {
        $data = $this->resolverCategoria($data);
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

    /**
     * Resuelve el id_categoria si es un nombre de categoría en lugar de un ID numérico.
     */
    private function resolverCategoria(array $data): array
    {
        if (isset($data['id_categoria']) && !is_numeric($data['id_categoria'])) {
            $categoria = Categoria::firstOrCreate([
                'nombre' => trim($data['id_categoria'])
            ]);
            $data['id_categoria'] = $categoria->id;
        }
        return $data;
    }
}
