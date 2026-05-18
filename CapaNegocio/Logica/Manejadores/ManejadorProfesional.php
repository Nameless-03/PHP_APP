<?php

namespace App\Manejadores;

use App\Models\Profesional;
use App\Models\Usuario;
use App\DataTypes\DtProfesional;
use App\Enums\RoleEnum;
use App\Enums\ModalidadEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Exception;

class ManejadorProfesional
{
    /**
     * Registra un nuevo profesional (crea usuario + profesional).
     */
    public function registrar(
        string $nombre,
        string $email,
        string $password,
        string $modalidadPreferida,
        ?string $descripcion = null,
        ?string $experiencia = null,
        ?string $ubicacion = null
    ): DtProfesional {
        // Validar modalidad
        if (!ModalidadEnum::esValido($modalidadPreferida)) {
            throw new Exception("Modalidad inválida: {$modalidadPreferida}");
        }

        return DB::transaction(function () use (
            $nombre,
            $email,
            $password,
            $modalidadPreferida,
            $descripcion,
            $experiencia,
            $ubicacion
        ) {
            // Crear el usuario
            $usuario = Usuario::create([
                'nombre' => $nombre,
                'email' => $email,
                'password' => Hash::make($password),
                'role' => RoleEnum::PROFESIONAL->value,
                'fecha_registro' => now(),
            ]);

            // Crear el profesional
            $profesional = Profesional::create([
                'id_usuario' => $usuario->id,
                'descripcion' => $descripcion,
                'experiencia' => $experiencia,
                'ubicacion' => $ubicacion,
                'modalidad_preferida' => $modalidadPreferida,
                'reputacion' => 0.0,
            ]);

            $profesional->load('usuario');

            return DtProfesional::desdeModelo($profesional);
        });
    }

    /**
     * Obtiene un profesional por ID de usuario.
     */
    public function obtenerPorId(int $idUsuario): ?DtProfesional
    {
        $profesional = Profesional::with('usuario')->find($idUsuario);

        return $profesional ? DtProfesional::desdeModelo($profesional) : null;
    }

    /**
     * Lista todos los profesionales.
     */
    public function listarTodos(): array
    {
        return Profesional::with('usuario')
            ->get()
            ->map(fn($profesional) => DtProfesional::desdeModelo($profesional))
            ->toArray();
    }

    /**
     * Lista profesionales por modalidad.
     */
    public function listarPorModalidad(string $modalidad): array
    {
        if (!ModalidadEnum::esValido($modalidad)) {
            throw new Exception("Modalidad inválida: {$modalidad}");
        }

        return Profesional::with('usuario')
            ->where('modalidad_preferida', $modalidad)
            ->get()
            ->map(fn($profesional) => DtProfesional::desdeModelo($profesional))
            ->toArray();
    }

    /**
     * Busca profesionales por ubicación.
     */
    public function buscarPorUbicacion(string $ubicacion): array
    {
        return Profesional::with('usuario')
            ->where('ubicacion', 'LIKE', "%{$ubicacion}%")
            ->get()
            ->map(fn($profesional) => DtProfesional::desdeModelo($profesional))
            ->toArray();
    }

    /**
     * Actualiza los datos de un profesional.
     */
    public function actualizar(int $idUsuario, array $datos): DtProfesional
    {
        $profesional = Profesional::with('usuario')->findOrFail($idUsuario);

        // Validar modalidad si se actualiza
        if (isset($datos['modalidad_preferida']) && !ModalidadEnum::esValido($datos['modalidad_preferida'])) {
            throw new Exception("Modalidad inválida: {$datos['modalidad_preferida']}");
        }

        // Separar datos de usuario y profesional
        $datosUsuario = [];
        $datosProfesional = [];

        foreach ($datos as $key => $value) {
            if (in_array($key, ['nombre', 'email', 'password'])) {
                $datosUsuario[$key] = $value;
            } elseif (in_array($key, ['descripcion', 'experiencia', 'ubicacion', 'modalidad_preferida', 'reputacion'])) {
                $datosProfesional[$key] = $value;
            }
        }

        return DB::transaction(function () use ($profesional, $datosUsuario, $datosProfesional) {
            // Actualizar usuario si hay datos
            if (!empty($datosUsuario)) {
                if (isset($datosUsuario['password'])) {
                    $datosUsuario['password'] = Hash::make($datosUsuario['password']);
                }
                $profesional->usuario->update($datosUsuario);
            }

            // Actualizar profesional si hay datos
            if (!empty($datosProfesional)) {
                $profesional->update($datosProfesional);
            }

            return DtProfesional::desdeModelo($profesional->fresh('usuario'));
        });
    }

    /**
     * Actualiza la reputación de un profesional.
     */
    public function actualizarReputacion(int $idUsuario): float
    {
        $profesional = Profesional::findOrFail($idUsuario);

        // Calcular promedio de calificaciones
        $promedio = DB::table('calificaciones')
            ->join('reservas', 'calificaciones.id_reserva', '=', 'reservas.id')
            ->join('servicios', 'reservas.id_servicio', '=', 'servicios.id')
            ->where('servicios.id_profesional', $idUsuario)
            ->avg('calificaciones.puntuacion');

        $nuevaReputacion = $promedio ?? 0.0;

        $profesional->update(['reputacion' => $nuevaReputacion]);

        return $nuevaReputacion;
    }

    /**
     * Elimina un profesional.
     */
    public function eliminar(int $idUsuario): bool
    {
        return DB::transaction(function () use ($idUsuario) {
            $profesional = Profesional::with('usuario')->findOrFail($idUsuario);

            // Eliminar profesional
            $profesional->delete();

            // Eliminar usuario (soft delete)
            $profesional->usuario->delete();

            return true;
        });
    }

    /**
     * Obtiene los servicios de un profesional.
     */
    public function obtenerServicios(int $idUsuario): array
    {
        $profesional = Profesional::findOrFail($idUsuario);

        return $profesional->servicios()
            ->with('categoria')
            ->get()
            ->map(fn($servicio) => \App\DataTypes\DtServicio::desdeModelo($servicio))
            ->toArray();
    }

    /**
     * Obtiene las disponibilidades de un profesional.
     */
    public function obtenerDisponibilidades(int $idUsuario): array
    {
        $profesional = Profesional::findOrFail($idUsuario);

        return $profesional->disponibilidades()
            ->orderByRaw("CASE dia_semana
                WHEN 'lunes' THEN 1
                WHEN 'martes' THEN 2
                WHEN 'miercoles' THEN 3
                WHEN 'jueves' THEN 4
                WHEN 'viernes' THEN 5
                WHEN 'sabado' THEN 6
                WHEN 'domingo' THEN 7
            END")
            ->get()
            ->map(fn($disponibilidad) => \App\DataTypes\DtDisponibilidad::desdeModelo($disponibilidad))
            ->toArray();
    }
}
