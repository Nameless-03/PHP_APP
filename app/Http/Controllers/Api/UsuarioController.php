<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UsuarioResource;
use App\Services\UsuarioService;
use App\Models\Usuario;
use App\Models\Reserva;
use App\Models\Servicio;
use App\Models\Pago;
use App\Models\Calificacion;
use App\Enums\RoleEnum;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Services\NoSqlLoggerService;

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
            'foto_perfil' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ]);

        $usuario = $this->usuarioService->obtenerPorId($id);

        if ($request->hasFile('foto_perfil')) {
            // Delete old file if it was a disk path
            $oldPhoto = $usuario->profesional->foto_perfil ?? $usuario->cliente->foto_perfil ?? null;
            if ($oldPhoto && !str_starts_with($oldPhoto, 'data:') && !filter_var($oldPhoto, FILTER_VALIDATE_URL)) {
                try {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($oldPhoto);
                } catch (\Exception $e) {
                    // Ignore disk errors
                }
            }

            $file = $request->file('foto_perfil');
            $base64 = 'data:' . $file->getMimeType() . ';base64,' . base64_encode(file_get_contents($file->getPathname()));
            $validatedData['foto_perfil'] = $base64;
        }

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
     * Returns global system statistics for the "Ver métricas básicas" use case.
     */
    public function dashboardStats(): JsonResponse
    {
        // ── Usuarios ──────────────────────────────────────────────────────────
        $usersByRole = Usuario::select('role', DB::raw('count(*) as total'))
            ->groupBy('role')
            ->pluck('total', 'role');

        $totalUsers   = Usuario::count();
        $activeUsers  = Usuario::where('activo', true)->count();
        $inactiveUsers = $totalUsers - $activeUsers;

        // ── Servicios ─────────────────────────────────────────────────────────
        $totalServicios  = Servicio::count();
        $serviciosActivos = Servicio::where('activo', true)->count();

        // ── Reservas ──────────────────────────────────────────────────────────
        $reservasByEstado = Reserva::select('estado', DB::raw('count(*) as total'))
            ->groupBy('estado')
            ->pluck('total', 'estado');

        $totalReservas      = Reserva::count();
        $reservasFinalizadas = Reserva::where('estado', 'finalizada')->count();
        $reservasCanceladas  = Reserva::where('estado', 'cancelada')->count();

        // ── Ingresos (pagos completados) ───────────────────────────────────────
        $ingresosTotales    = Pago::where('estado', 'completado')->sum('monto');
        $ingresosPorMetodo  = Pago::where('estado', 'completado')
            ->select('metodo', DB::raw('sum(monto) as total'))
            ->groupBy('metodo')
            ->pluck('total', 'metodo');

        // ── Calificaciones ────────────────────────────────────────────────────
        $promedioCalificacion = Calificacion::avg('puntuacion');
        $totalCalificaciones  = Calificacion::count();
        $calificacionesPorPuntuacion = Calificacion::select('puntuacion', DB::raw('count(*) as total'))
            ->groupBy('puntuacion')
            ->orderBy('puntuacion', 'desc')
            ->pluck('total', 'puntuacion');

        // ── Reservas por mes (últimos 6 meses) ────────────────────────────────
        $reservasPorMes = Reserva::select(
                DB::raw("TO_CHAR(created_at, 'YYYY-MM') as mes"),
                DB::raw('count(*) as total')
            )
            ->where('created_at', '>=', now()->subMonths(6)->startOfMonth())
            ->groupBy('mes')
            ->orderBy('mes')
            ->get()
            ->map(fn($r) => ['mes' => $r->mes, 'total' => (int)$r->total]);

        // ── Top 5 servicios más reservados ────────────────────────────────────
        $topServicios = Reserva::select('id_servicio', DB::raw('count(*) as total_reservas'))
            ->with('servicio:id,nombre,precio')
            ->groupBy('id_servicio')
            ->orderByDesc('total_reservas')
            ->limit(5)
            ->get()
            ->map(fn($r) => [
                'nombre'         => $r->servicio?->nombre ?? 'N/A',
                'precio'         => $r->servicio?->precio ?? 0,
                'total_reservas' => (int)$r->total_reservas,
            ]);

        // ── Últimas 10 reservas ───────────────────────────────────────────────
        $latestReservas = Reserva::with(['cliente.usuario', 'servicio'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(fn($r) => [
                'id'               => $r->id,
                'estado'           => $r->estado?->value ?? $r->estado,
                'fecha_hora_inicio' => $r->fecha_hora_inicio?->toIso8601String(),
                'fecha_hora_fin'   => $r->fecha_hora_fin?->toIso8601String(),
                'cliente_nombre'   => $r->cliente?->usuario?->nombre ?? 'N/A',
                'servicio_nombre'  => $r->servicio?->nombre ?? 'N/A',
                'created_at'       => $r->created_at?->toIso8601String(),
            ]);

        // ── Últimos 10 usuarios registrados ───────────────────────────────────
        $latestUsers = Usuario::orderByDesc('fecha_registro')
            ->limit(10)
            ->get()
            ->map(fn($u) => [
                'id'             => $u->id,
                'nombre'         => $u->nombre,
                'email'          => $u->email,
                'role'           => $u->role?->value ?? $u->role,
                'activo'         => $u->activo,
                'fecha_registro' => $u->fecha_registro?->toIso8601String(),
            ]);

        return response()->json([
            // Usuarios
            'total_usuarios'    => $totalUsers,
            'usuarios_activos'  => $activeUsers,
            'usuarios_inactivos' => $inactiveUsers,
            'usuarios_por_rol'  => $usersByRole,
            // Servicios
            'total_servicios'   => $totalServicios,
            'servicios_activos' => $serviciosActivos,
            // Reservas
            'total_reservas'         => $totalReservas,
            'reservas_finalizadas'   => $reservasFinalizadas,
            'reservas_canceladas'    => $reservasCanceladas,
            'reservas_por_estado'    => $reservasByEstado,
            'reservas_por_mes'       => $reservasPorMes,
            // Ingresos
            'ingresos_totales'   => (float)$ingresosTotales,
            'ingresos_por_metodo' => $ingresosPorMetodo,
            // Calificaciones
            'promedio_calificacion'           => $promedioCalificacion ? round((float)$promedioCalificacion, 2) : null,
            'total_calificaciones'            => $totalCalificaciones,
            'calificaciones_por_puntuacion'   => $calificacionesPorPuntuacion,
            // Listas
            'top_servicios'    => $topServicios,
            'ultimas_reservas' => $latestReservas,
            'ultimos_usuarios' => $latestUsers,
        ]);
    }

    /**
     * Get system activity logs from NoSQL.
     */
    public function systemLogs(NoSqlLoggerService $loggerService): JsonResponse
    {
        $logs = $loggerService->getLogs(100);
        return response()->json([
            'data' => $logs
        ]);
    }
}
