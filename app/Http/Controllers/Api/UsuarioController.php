<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UsuarioResource;
use App\Services\UsuarioService;
use App\Models\Usuario;
use App\Models\Reserva;
use App\Enums\RoleEnum;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class UsuarioController extends Controller
{
    public function __construct(
        private UsuarioService $usuarioService
    ) {}

    /**
     * Display a listing of the users (Admin only).
     */
    public function index(): JsonResponse
    {
        $usuarios = $this->usuarioService->listarTodos();

        return response()->json([
            'data' => UsuarioResource::collection($usuarios),
        ]);
    }

    /**
     * Display the specified user.
     */
    public function show(int $id, Request $request): JsonResponse
    {
        // Simple authorization check: user can only view themselves unless they are admin
        if (!$request->user()->esAdmin() && $request->user()->id !== $id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $usuario = $this->usuarioService->obtenerPorId($id);

        return response()->json([
            'data' => new UsuarioResource($usuario),
        ]);
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        // Simple authorization check
        if (!$request->user()->esAdmin() && $request->user()->id !== $id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validatedData = $request->validate([
            'nombre' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:usuarios,email,' . $id,
            'password' => 'sometimes|string|min:8|confirmed',
            'descripcion' => 'sometimes|nullable|string',
            'experiencia' => 'sometimes|nullable|string',
            'ubicacion' => 'sometimes|nullable|string',
            'modalidad_preferida' => 'sometimes|string|in:presencial,remota,hibrida',
            'activo' => 'sometimes|boolean',
        ]);

        $usuario = $this->usuarioService->obtenerPorId($id);
        $updatedUsuario = $this->usuarioService->actualizar($usuario, $validatedData);

        return response()->json([
            'message' => 'User updated successfully',
            'data' => new UsuarioResource($updatedUsuario),
        ]);
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $this->usuarioService->eliminar($id);

        return response()->json([
            'message' => 'User deleted successfully',
        ], 204);
    }

    /**
     * Dashboard stats for admin panel.
     * Returns global system statistics.
     */
    public function dashboardStats(): JsonResponse
    {
        // Users by role
        $usersByRole = Usuario::select('role', DB::raw('count(*) as total'))
            ->groupBy('role')
            ->pluck('total', 'role');

        // Total users
        $totalUsers = Usuario::count();
        $activeUsers = Usuario::where('activo', true)->count();
        $inactiveUsers = $totalUsers - $activeUsers;

        // Reservations by status
        $reservasByEstado = Reserva::select('estado', DB::raw('count(*) as total'))
            ->groupBy('estado')
            ->pluck('total', 'estado');

        $totalReservas = Reserva::count();

        // Latest 10 reservations
        $latestReservas = Reserva::with(['cliente.usuario', 'servicio'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(function ($r) {
                return [
                    'id' => $r->id,
                    'estado' => $r->estado?->value ?? $r->estado,
                    'fecha_hora_inicio' => $r->fecha_hora_inicio?->toIso8601String(),
                    'fecha_hora_fin' => $r->fecha_hora_fin?->toIso8601String(),
                    'cliente_nombre' => $r->cliente?->usuario?->nombre ?? 'N/A',
                    'servicio_nombre' => $r->servicio?->nombre ?? 'N/A',
                    'created_at' => $r->created_at?->toIso8601String(),
                ];
            });

        // Latest 10 registered users
        $latestUsers = Usuario::orderByDesc('fecha_registro')
            ->limit(10)
            ->get()
            ->map(function ($u) {
                return [
                    'id' => $u->id,
                    'nombre' => $u->nombre,
                    'email' => $u->email,
                    'role' => $u->role?->value ?? $u->role,
                    'activo' => $u->activo,
                    'fecha_registro' => $u->fecha_registro?->toIso8601String(),
                ];
            });

        return response()->json([
            'total_usuarios' => $totalUsers,
            'usuarios_activos' => $activeUsers,
            'usuarios_inactivos' => $inactiveUsers,
            'usuarios_por_rol' => $usersByRole,
            'total_reservas' => $totalReservas,
            'reservas_por_estado' => $reservasByEstado,
            'ultimas_reservas' => $latestReservas,
            'ultimos_usuarios' => $latestUsers,
        ]);
    }
}
