<?php

namespace App\Services;

use App\Models\Usuario;
use App\Enums\RoleEnum;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Collection;

class UsuarioService
{
    /**
     * Get a user by ID.
     */
    public function obtenerPorId(int $id): Usuario
    {
        return Usuario::with(['cliente', 'profesional', 'admin'])->findOrFail($id);
    }

    /**
     * Get all users.
     */
    public function listarTodos(): Collection
    {
        return Usuario::with(['cliente', 'profesional', 'admin'])->get();
    }

    /**
     * Update user details.
     */
    public function actualizar(Usuario $usuario, array $data): Usuario
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $usuarioData = [];
        if (array_key_exists('nombre', $data)) $usuarioData['nombre'] = $data['nombre'];
        if (array_key_exists('email', $data)) $usuarioData['email'] = $data['email'];
        if (array_key_exists('password', $data)) $usuarioData['password'] = $data['password'];
        if (array_key_exists('activo', $data)) $usuarioData['activo'] = $data['activo'];

        if (!empty($usuarioData)) {
            $usuario->update($usuarioData);
        }

        if ($usuario->esProfesional() && $usuario->profesional) {
            $profData = [];
            if (array_key_exists('descripcion', $data)) $profData['descripcion'] = $data['descripcion'];
            if (array_key_exists('experiencia', $data)) $profData['experiencia'] = $data['experiencia'];
            if (array_key_exists('ubicacion', $data)) $profData['ubicacion'] = $data['ubicacion'];
            if (array_key_exists('modalidad_preferida', $data)) $profData['modalidad_preferida'] = $data['modalidad_preferida'];

            if (!empty($profData)) {
                $usuario->profesional->update($profData);
            }
        }

        return $usuario->fresh(['cliente', 'profesional', 'admin']);
    }

    /**
     * Soft delete a user.
     */
    public function eliminar(int $id): bool
    {
        $usuario = Usuario::findOrFail($id);
        return $usuario->delete();
    }
}
