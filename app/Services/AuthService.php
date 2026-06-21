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

use App\Services\NoSqlLoggerService;
use App\Jobs\EnviarEmailBienvenidaJob;

class AuthService
{
    public function __construct(
        private NoSqlLoggerService $logger
    ) {}
    /**
     * Authenticate a user and generate a token.
     */
    public function login(array $credentials): array
    {
        $usuario = Usuario::where('email', $credentials['email'])->first();

        if (!$usuario || !Hash::check($credentials['password'], $usuario->password)) {
            $this->logger->log("Intento fallido de inicio de sesión", 'warning', ['email' => $credentials['email']]);
            throw ValidationException::withMessages([
                'email' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }

        // Verificar que la cuenta esté activa
        if (!$usuario->activo) {
            $this->logger->log("Intento de inicio de sesión en cuenta desactivada", 'warning', ['email' => $usuario->email], $usuario->id);
            throw ValidationException::withMessages([
                'email' => ['Tu cuenta ha sido desactivada. Contacta al administrador.'],
            ]);
        }

        // Genera un token usando Sanctum (asumiendo que Sanctum está instalado)
        // Ya que no tenemos Sanctum instalado en este esqueleto, escribiremos el código para ello
        // y funcionará una vez que Sanctum esté configurado.
        $token = $usuario->createToken('auth_token')->plainTextToken;

        $this->logger->log("Inicio de sesión exitoso", 'info', ['email' => $usuario->email], $usuario->id);

        return [
            'usuario' => $usuario,
            'token' => $token,
        ];
    }

    /**
     * Registra un nuevo Cliente.
     */
    public function registerCliente(array $data): Usuario
    {
        $usuario = DB::transaction(function () use ($data) {
            $usuario = Usuario::create([
                'nombre' => $data['nombre'],
                'email' => $data['email'],
                'password' => Hash::make($data['password'] ?? \Illuminate\Support\Str::random(16)),
                'role' => RoleEnum::CLIENTE,
                'fecha_registro' => now(),
                'google_id' => $data['google_id'] ?? null,
            ]);

            Cliente::create([
                'id_usuario' => $usuario->id,
                'telefono' => $data['telefono'] ?? null,
                'foto_perfil' => $data['foto_perfil'] ?? null,
            ]);

            return $usuario->load('cliente');
        });

        // Enviar email de bienvenida asincrónicamente usando Redis
        EnviarEmailBienvenidaJob::dispatch($usuario);

        $this->logger->log("Registro de cliente exitoso", 'info', [
            'nombre' => $usuario->nombre,
            'email' => $usuario->email
        ], $usuario->id);

        return $usuario;
    }

    /**
     * Registra un nuevo Profesional.
     */
    public function registerProfesional(array $data): Usuario
    {
        $usuario = DB::transaction(function () use ($data) {
            $usuario = Usuario::create([
                'nombre' => $data['nombre'],
                'email' => $data['email'],
                'password' => Hash::make($data['password'] ?? \Illuminate\Support\Str::random(16)),
                'role' => RoleEnum::PROFESIONAL,
                'fecha_registro' => now(),
                'google_id' => $data['google_id'] ?? null,
            ]);

            Profesional::create([
                'id_usuario' => $usuario->id,
                'descripcion' => $data['descripcion'] ?? null,
                'experiencia' => $data['experiencia'] ?? null,
                'ubicacion' => $data['ubicacion'] ?? null,
                'modalidad_preferida' => $data['modalidad_preferida'] ?? 'presencial',
                'reputacion' => 0.0,
                'foto_perfil' => $data['foto_perfil'] ?? null,
            ]);

            return $usuario->load('profesional');
        });

        // Enviar email de bienvenida asincrónicamente usando Redis
        EnviarEmailBienvenidaJob::dispatch($usuario);

        $this->logger->log("Registro de profesional exitoso", 'info', [
            'nombre' => $usuario->nombre,
            'email' => $usuario->email
        ], $usuario->id);

        return $usuario;
    }

    /**
     * Cierra la sesión de un usuario (revoca los tokens).
     */
    public function logout(Usuario $usuario): void
    {
        $usuario->tokens()->delete();
    }
}
