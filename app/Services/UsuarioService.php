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

        $usuario->update($data);

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
