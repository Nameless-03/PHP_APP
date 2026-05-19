<?php

namespace App\Services;

use App\Models\Usuario;
use App\Models\Cliente;
use App\Models\Profesional;
use App\Enums\RoleEnum;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Exception;

class AuthService
{
    /**
     * Authenticate a user and generate a token.
     */
    public function login(array $credentials): array
    {
        $usuario = Usuario::where('email', $credentials['email'])->first();

        if (!$usuario || !Hash::check($credentials['password'], $usuario->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }

        // Generate token using Sanctum (assuming Sanctum will be installed)
        // Since we don't have Sanctum installed in this skeleton, we'll write the code for it
        // and it will work once Sanctum is set up.
        $token = $usuario->createToken('auth_token')->plainTextToken;

        return [
            'usuario' => $usuario,
            'token' => $token,
        ];
    }

    /**
     * Register a new Cliente.
     */
    public function registerCliente(array $data): Usuario
    {
        return DB::transaction(function () use ($data) {
            $usuario = Usuario::create([
                'nombre' => $data['nombre'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => RoleEnum::CLIENTE,
                'fecha_registro' => now(),
            ]);

            Cliente::create([
                'id_usuario' => $usuario->id,
                'telefono' => $data['telefono'] ?? null,
                'foto_perfil' => $data['foto_perfil'] ?? null,
            ]);

            return $usuario->load('cliente');
        });
    }

    /**
     * Register a new Profesional.
     */
    public function registerProfesional(array $data): Usuario
    {
        return DB::transaction(function () use ($data) {
            $usuario = Usuario::create([
                'nombre' => $data['nombre'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => RoleEnum::PROFESIONAL,
                'fecha_registro' => now(),
            ]);

            Profesional::create([
                'id_usuario' => $usuario->id,
                'descripcion' => $data['descripcion'] ?? null,
                'experiencia' => $data['experiencia'] ?? null,
                'ubicacion' => $data['ubicacion'] ?? null,
                'modalidad_preferida' => $data['modalidad_preferida'] ?? 'presencial',
                'reputacion' => 0.0,
            ]);

            return $usuario->load('profesional');
        });
    }

    /**
     * Logout a user (revoke tokens).
     */
    public function logout(Usuario $usuario): void
    {
        $usuario->tokens()->delete();
    }
}
