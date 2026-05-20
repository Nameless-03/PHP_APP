<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reserva;
use App\Models\Calificacion;
use App\Models\Profesional;
use App\Enums\EstadoReservaEnum;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;

class CalificacionController extends Controller
{
    /**
     * Calificar una reserva finalizada.
     */
    public function calificar(Request $request, Reserva $reserva): JsonResponse
    {
        $usuario = $request->user();

        // 1. Validar que el usuario sea el cliente de la reserva
        if (!$usuario->esCliente() || $reserva->id_cliente !== $usuario->id_usuario) {
            return response()->json(['message' => 'No tienes permiso para calificar esta reserva.'], 403);
        }

        // 2. Validar que la reserva esté finalizada o la fecha ya haya pasado y esté confirmada/pagada
        $fechaPasada = Carbon::parse($reserva->fecha_hora_fin)->isPast();
        $estadosPermitidos = [EstadoReservaEnum::FINALIZADA->value, EstadoReservaEnum::CONFIRMADA->value, EstadoReservaEnum::PAGADA->value];
        
        if (!in_array($reserva->estado->value, $estadosPermitidos) || !$fechaPasada) {
            return response()->json(['message' => 'La reserva debe haber concluido para poder calificarla.'], 422);
        }

        // 3. Validar que no haya sido calificada previamente
        if (Calificacion::where('id_reserva', $reserva->id)->exists()) {
            return response()->json(['message' => 'Esta reserva ya fue calificada.'], 422);
        }

        // 4. Validar los datos de entrada
        $validated = $request->validate([
            'puntuacion' => ['required', 'integer', 'min:1', 'max:5'],
            'comentario' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            DB::transaction(function () use ($reserva, $validated) {
                // Insertar calificación
                Calificacion::create([
                    'puntuacion' => $validated['puntuacion'],
                    'comentario' => $validated['comentario'] ?? null,
                    'fecha' => now(),
                    'id_reserva' => $reserva->id,
                ]);

                // Actualizar reputación del profesional
                $idProfesional = $reserva->servicio->id_profesional;
                $promedio = Calificacion::whereHas('reserva.servicio', function ($q) use ($idProfesional) {
                    $q->where('id_profesional', $idProfesional);
                })->avg('puntuacion');

                Profesional::where('id_usuario', $idProfesional)->update([
                    'reputacion' => round($promedio, 2)
                ]);
            });

            return response()->json([
                'message' => '¡Gracias por calificar el servicio!',
            ], 201);
            
        } catch (Exception $e) {
            return response()->json(['message' => 'Ocurrió un error al guardar la calificación.', 'error' => $e->getMessage()], 500);
        }
    }
}
