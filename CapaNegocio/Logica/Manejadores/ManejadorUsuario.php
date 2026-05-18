<?php

namespace App\Manejadores;

use App\Models\Usuario;
use App\DataTypes\DtUsuario;
use App\Enums\RoleEnum;
use Illuminate\Support\Facades\Hash;
use Exception;

class ManejadorUsuario
{
    /**
     * Crea un nuevo usuario.
     */
    public function crear(string $nombre, string $email, string $password, string $role): DtUsuario
    {
        // Validar el rol
        if (!RoleEnum::esValido($role)) {
            throw new Exception("Rol inválido: {$role}");
        }

        // Verificar si el email ya existe
        if (Usuario::where('email', $email)->exists()) {
            throw new Exception("El email ya está registrado");
        }

        $usuario = Usuario::create([
            'nombre' => $nombre,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => $role,
            'fecha_registro' => now(),
        ]);

        return DtUsuario::desdeModelo($usuario);
    }

    /**
     * Obtiene un usuario por ID.
     */
    public function obtenerPorId(int $id): ?DtUsuario
    {
        $usuario = Usuario::find($id);

        return $usuario ? DtUsuario::desdeModelo($usuario) : null;
    }

    /**
     * Obtiene un usuario por email.
     */
    public function obtenerPorEmail(string $email): ?DtUsuario
    {
        $usuario = Usuario::where('email', $email)->first();

        return $usuario ? DtUsuario::desdeModelo($usuario) : null;
    }

    /**
     * Lista todos los usuarios.
     */
    public function listarTodos(): array
    {
        return Usuario::all()->map(fn($usuario) => DtUsuario::desdeModelo($usuario))->toArray();
    }

    /**
     * Lista usuarios por rol.
     */
    public function listarPorRole(string $role): array
    {
        if (!RoleEnum::esValido($role)) {
            throw new Exception("Rol inválido: {$role}");
        }

        return Usuario::porRole($role)
            ->get()
            ->map(fn($usuario) => DtUsuario::desdeModelo($usuario))
            ->toArray();
    }

    /**
     * Actualiza los datos de un usuario.
     */
    public function actualizar(int $id, array $datos): DtUsuario
    {
        $usuario = Usuario::findOrFail($id);

        // Si se actualiza el email, verificar que no esté en uso
        if (isset($datos['email']) && $datos['email'] !== $usuario->email) {
            if (Usuario::where('email', $datos['email'])->exists()) {
                throw new Exception("El email ya está registrado");
            }
        }

        // Si se actualiza el password, hashearlo
        if (isset($datos['password'])) {
            $datos['password'] = Hash::make($datos['password']);
        }

        // Si se actualiza el rol, validarlo
        if (isset($datos['role']) && !RoleEnum::esValido($datos['role'])) {
            throw new Exception("Rol inválido: {$datos['role']}");
        }

        $usuario->update($datos);

        return DtUsuario::desdeModelo($usuario->fresh());
    }

    /**
     * Elimina un usuario (soft delete).
     */
    public function eliminar(int $id): bool
    {
        $usuario = Usuario::findOrFail($id);

        return $usuario->delete();
    }

    /**
     * Verifica las credenciales de un usuario.
     */
    public function verificarCredenciales(string $email, string $password): ?DtUsuario
    {
        $usuario = Usuario::where('email', $email)->first();

        if (!$usuario || !Hash::check($password, $usuario->password)) {
            return null;
        }

        return DtUsuario::desdeModelo($usuario);
    }

    /**
     * Cambia la contraseña de un usuario.
     */
    public function cambiarPassword(int $id, string $passwordActual, string $passwordNuevo): bool
    {
        $usuario = Usuario::findOrFail($id);

        if (!Hash::check($passwordActual, $usuario->password)) {
            throw new Exception("La contraseña actual es incorrecta");
        }

        $usuario->update([
            'password' => Hash::make($passwordNuevo),
        ]);

        return true;
    }
}
