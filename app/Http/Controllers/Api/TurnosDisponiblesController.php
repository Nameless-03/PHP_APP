<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Servicio;
use App\Services\CalculadorTurnosService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TurnosDisponiblesController extends Controller
{
    public function __construct(
        private CalculadorTurnosService $calculadorTurnos
    ) {}

    /**
     * Obtiene los turnos disponibles para un servicio en una fecha.
     */
    public function index(Request $request, Servicio $servicio): JsonResponse
    {
        $request->validate([
            'fecha' => ['required', 'date', 'after_or_equal:today'],
        ]);

        $turnos = $this->calculadorTurnos->obtenerTurnosDisponibles($servicio, $request->fecha);

        return response()->json([
            'data' => $turnos
        ]);
    }
}
