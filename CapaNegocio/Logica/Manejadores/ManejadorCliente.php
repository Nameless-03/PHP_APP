<?php

namespace App\Manejadores;

use App\Models\Cliente;
use App\Models\Usuario;
use App\DataTypes\DtCliente;
use App\Enums\RoleEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Exception;

class ManejadorCliente
{
    /**
     * Registra un nuevo cliente (crea usuario + cliente).
     */
    public function registrar(
        string $nombre,
        string $email,
        string $password,
        ?string $telefono = null,
        ?string $fotoPerfil = null
    ): DtCliente {
        return DB::transaction(function () use ($nombre, $email, $password, $telefono, $fotoPerfil) {
            // Crear el usuario
            $usuario = Usuario::create([
                'nombre' => $nombre,
                'email' => $email,
                'password' => Hash::make($password),
                'role' => RoleEnum::CLIENTE->value,
                'fecha_registro' => now(),
            ]);

            // Crear el cliente
            $cliente = Cliente::create([
                'id_usuario' => $usuario->id,
                'telefono' => $telefono,
                'foto_perfil' => $fotoPerfil,
            ]);

            $cliente->load('usuario');

            return DtCliente::desdeModelo($cliente);
        });
    }

    /**
     * Obtiene un cliente por ID de usuario.
     */
    public function obtenerPorId(int $idUsuario): ?DtCliente
    {
        $cliente = Cliente::with('usuario')->find($idUsuario);

        return $cliente ? DtCliente::desdeModelo($cliente) : null;
    }

    /**
     * Lista todos los clientes.
     */
    public function listarTodos(): array
    {
        return Cliente::with('usuario')
            ->get()
            ->map(fn($cliente) => DtCliente::desdeModelo($cliente))
            ->toArray();
    }

    /**
     * Actualiza los datos de un cliente.
     */
    public function actualizar(int $idUsuario, array $datos): DtCliente
    {
        $cliente = Cliente::with('usuario')->findOrFail($idUsuario);

        // Separar datos de usuario y cliente
        $datosUsuario = [];
        $datosCliente = [];

        foreach ($datos as $key => $value) {
            if (in_array($key, ['nombre', 'email', 'password'])) {
                $datosUsuario[$key] = $value;
            } elseif (in_array($key, ['telefono', 'foto_perfil'])) {
                $datosCliente[$key] = $value;
            }
        }

        return DB::transaction(function () use ($cliente, $datosUsuario, $datosCliente) {
            // Actualizar usuario si hay datos
            if (!empty($datosUsuario)) {
                if (isset($datosUsuario['password'])) {
                    $datosUsuario['password'] = Hash::make($datosUsuario['password']);
                }
                $cliente->usuario->update($datosUsuario);
            }

            // Actualizar cliente si hay datos
            if (!empty($datosCliente)) {
                $cliente->update($datosCliente);
            }

            return DtCliente::desdeModelo($cliente->fresh('usuario'));
        });
    }

    /**
     * Elimina un cliente.
     */
    public function eliminar(int $idUsuario): bool
    {
        return DB::transaction(function () use ($idUsuario) {
            $cliente = Cliente::with('usuario')->findOrFail($idUsuario);

            // Eliminar cliente
            $cliente->delete();

            // Eliminar usuario (soft delete)
            $cliente->usuario->delete();

            return true;
        });
    }

    /**
     * Obtiene las reservas de un cliente.
     */
    public function obtenerReservas(int $idUsuario): array
    {
        $cliente = Cliente::findOrFail($idUsuario);

        return $cliente->reservas()
            ->with(['servicio.profesional.usuario', 'pago'])
            ->orderBy('fecha_hora_inicio', 'desc')
            ->get()
            ->map(fn($reserva) => \App\DataTypes\DtReserva::desdeModelo($reserva))
            ->toArray();
    }

    /**
     * Obtiene los paquetes comprados por un cliente.
     */
    public function obtenerPaquetes(int $idUsuario): array
    {
        $cliente = Cliente::findOrFail($idUsuario);

        return $cliente->comprasPaquetes()
            ->with('paquete.profesional.usuario')
            ->orderBy('fecha_compra', 'desc')
            ->get()
            ->toArray();
    }
}
