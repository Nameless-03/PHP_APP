<?php

namespace App\Manejadores;

use App\Models\Servicio;
use App\DataTypes\DtServicio;
use App\Enums\ModalidadEnum;
use Exception;

class ManejadorServicio
{
    /**
     * Crea un nuevo servicio.
     */
    public function crear(
        string $nombre,
        float $precio,
        string $modalidad,
        int $duracion,
        int $idProfesional,
        int $idCategoria,
        ?string $descripcion = null,
        ?string $ubicacion = null,
        bool $activo = true
    ): DtServicio {
        // Validar modalidad
        if (!ModalidadEnum::esValido($modalidad)) {
            throw new Exception("Modalidad inválida: {$modalidad}");
        }

        // Validar que modalidades presenciales tengan ubicación
        $modalidadEnum = ModalidadEnum::from($modalidad);
        if ($modalidadEnum->requiereUbicacion() && empty($ubicacion)) {
            throw new Exception("La modalidad {$modalidad} requiere ubicación");
        }

        $servicio = Servicio::create([
            'nombre' => $nombre,
            'descripcion' => $descripcion,
            'precio' => $precio,
            'modalidad' => $modalidad,
            'duracion' => $duracion,
            'ubicacion' => $ubicacion,
            'activo' => $activo,
            'id_profesional' => $idProfesional,
            'id_categoria' => $idCategoria,
        ]);

        $servicio->load(['profesional.usuario', 'categoria']);

        return DtServicio::desdeModelo($servicio);
    }

    /**
     * Obtiene un servicio por ID.
     */
    public function obtenerPorId(int $id): ?DtServicio
    {
        $servicio = Servicio::with(['profesional.usuario', 'categoria'])->find($id);

        return $servicio ? DtServicio::desdeModelo($servicio) : null;
    }

    /**
     * Lista todos los servicios activos.
     */
    public function listarActivos(): array
    {
        return Servicio::with(['profesional.usuario', 'categoria'])
            ->activos()
            ->get()
            ->map(fn($servicio) => DtServicio::desdeModelo($servicio))
            ->toArray();
    }

    /**
     * Lista todos los servicios.
     */
    public function listarTodos(): array
    {
        return Servicio::with(['profesional.usuario', 'categoria'])
            ->get()
            ->map(fn($servicio) => DtServicio::desdeModelo($servicio))
            ->toArray();
    }

    /**
     * Lista servicios por profesional.
     */
    public function listarPorProfesional(int $idProfesional): array
    {
        return Servicio::with(['profesional.usuario', 'categoria'])
            ->where('id_profesional', $idProfesional)
            ->get()
            ->map(fn($servicio) => DtServicio::desdeModelo($servicio))
            ->toArray();
    }

    /**
     * Lista servicios por categoría.
     */
    public function listarPorCategoria(int $idCategoria): array
    {
        return Servicio::with(['profesional.usuario', 'categoria'])
            ->where('id_categoria', $idCategoria)
            ->activos()
            ->get()
            ->map(fn($servicio) => DtServicio::desdeModelo($servicio))
            ->toArray();
    }

    /**
     * Lista servicios por modalidad.
     */
    public function listarPorModalidad(string $modalidad): array
    {
        if (!ModalidadEnum::esValido($modalidad)) {
            throw new Exception("Modalidad inválida: {$modalidad}");
        }

        return Servicio::with(['profesional.usuario', 'categoria'])
            ->porModalidad($modalidad)
            ->activos()
            ->get()
            ->map(fn($servicio) => DtServicio::desdeModelo($servicio))
            ->toArray();
    }

    /**
     * Busca servicios por nombre.
     */
    public function buscarPorNombre(string $nombre): array
    {
        return Servicio::with(['profesional.usuario', 'categoria'])
            ->where('nombre', 'LIKE', "%{$nombre}%")
            ->activos()
            ->get()
            ->map(fn($servicio) => DtServicio::desdeModelo($servicio))
            ->toArray();
    }

    /**
     * Filtra servicios por rango de precio.
     */
    public function filtrarPorPrecio(float $min, float $max): array
    {
        return Servicio::with(['profesional.usuario', 'categoria'])
            ->whereBetween('precio', [$min, $max])
            ->activos()
            ->get()
            ->map(fn($servicio) => DtServicio::desdeModelo($servicio))
            ->toArray();
    }

    /**
     * Actualiza un servicio.
     */
    public function actualizar(int $id, array $datos): DtServicio
    {
        $servicio = Servicio::with(['profesional.usuario', 'categoria'])->findOrFail($id);

        // Validar modalidad si se actualiza
        if (isset($datos['modalidad'])) {
            if (!ModalidadEnum::esValido($datos['modalidad'])) {
                throw new Exception("Modalidad inválida: {$datos['modalidad']}");
            }

            $modalidadEnum = ModalidadEnum::from($datos['modalidad']);
            if ($modalidadEnum->requiereUbicacion() && empty($datos['ubicacion'] ?? $servicio->ubicacion)) {
                throw new Exception("La modalidad {$datos['modalidad']} requiere ubicación");
            }
        }

        $servicio->update($datos);

        return DtServicio::desdeModelo($servicio->fresh(['profesional.usuario', 'categoria']));
    }

    /**
     * Activa un servicio.
     */
    public function activar(int $id): DtServicio
    {
        return $this->actualizar($id, ['activo' => true]);
    }

    /**
     * Desactiva un servicio.
     */
    public function desactivar(int $id): DtServicio
    {
        return $this->actualizar($id, ['activo' => false]);
    }

    /**
     * Elimina un servicio (soft delete).
     */
    public function eliminar(int $id): bool
    {
        $servicio = Servicio::findOrFail($id);

        return $servicio->delete();
    }
}
