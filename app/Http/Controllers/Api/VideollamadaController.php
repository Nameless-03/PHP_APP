<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reserva;
use App\Enums\EstadoReservaEnum;
use App\Services\LiveKitService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class VideollamadaController extends Controller
{
    public function __construct(
        private LiveKitService $liveKitService
    ) {}

    /**
     * Obtener token de acceso para la videocall de una reserva.
     */
    public function getToken(int $id, Request $request): JsonResponse
    {
        $usuarioAutenticado = $request->user();
        
        // 1. Obtener reserva con relaciones
        $reserva = Reserva::with(['servicio.profesional.usuario', 'cliente.usuario', 'videollamada'])
            ->findOrFail($id);

        // 2. Validar que corresponda a un servicio remoto o híbrido
        $servicio = $reserva->servicio;
        if ($servicio->modalidad !== 'remota' && $servicio->modalidad !== 'hibrida') {
            return response()->json([
                'message' => 'Esta reserva no califica para videollamada (servicio presencial).'
            ], 400);
        }

        // 3. Validar que la sesión exista en la base de datos
        if (!$reserva->videollamada) {
            return response()->json([
                'message' => 'No existe una sesión de videollamada activa para esta reserva.'
            ], 404);
        }

        // 4. Validar que el estado de la reserva permita videollamada (no cancelada ni no_asistida)
        if ($reserva->estado === EstadoReservaEnum::CANCELADA || $reserva->estado === EstadoReservaEnum::NO_ASISTIDA) {
            return response()->json([
                'message' => 'La sesión de videollamada está deshabilitada porque el turno fue cancelado o no asistido.'
            ], 400);
        }

        // 5. Validar autorización del usuario
        $esCliente = $usuarioAutenticado->esCliente() && $reserva->id_cliente === $usuarioAutenticado->id;
        $esProfesional = $usuarioAutenticado->esProfesional() && $servicio->id_profesional === $usuarioAutenticado->id;
        $esAdmin = $usuarioAutenticado->esAdmin();

        if (!$esCliente && !$esProfesional && !$esAdmin) {
            return response()->json([
                'message' => 'No tienes autorización para unirte a esta videollamada.'
            ], 403);
        }

        // 6. Generar Room Name e Identidad
        $room = 'reserva-' . $reserva->id;
        $identity = 'user-' . $usuarioAutenticado->id;
        $name = $usuarioAutenticado->nombre;

        // 7. Generar Token usando LiveKitService
        try {
            $token = $this->liveKitService->generateToken($room, $identity, $name);
            
            return response()->json([
                'token' => $token,
                'url' => $this->liveKitService->getLiveKitUrl(),
                'room' => $room,
                'identity' => $identity,
                'name' => $name,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al generar la sesión de videollamada.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
