<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePagoRequest;
use App\Services\PagoService;
use Illuminate\Http\JsonResponse;
use Exception;

use App\Models\Pago;

class PagoController extends Controller
{
    public function __construct(
        private PagoService $pagoService
    ) {}

    /**
     * Iniciar un pago.
     */
    public function store(StorePagoRequest $request): JsonResponse
    {
        try {
            $pago = $this->pagoService->iniciarPago($request->validated());

            return response()->json([
                'message' => 'Proceso de pago iniciado en segundo plano.',
                'data' => $pago,
            ], 202); // 202 Accepted, indicating async process
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Consultar estado de un pago.
     */
    public function show(Pago $pago): JsonResponse
    {
        $usuario = auth()->user();
        
        // Validar propiedad del pago
        if ($pago->id_reserva) {
            if ($pago->reserva->id_cliente !== $usuario->id) {
                return response()->json(['message' => 'Unauthorized.'], 403);
            }
        } elseif ($pago->id_compra) {
            if ($pago->compraPaquete->id_cliente !== $usuario->id) {
                return response()->json(['message' => 'Unauthorized.'], 403);
            }
        }
        
        return response()->json([
            'data' => $pago
        ]);
    }
}
