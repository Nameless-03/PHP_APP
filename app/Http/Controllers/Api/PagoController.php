<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePagoRequest;
use App\Services\PagoService;
use Illuminate\Http\JsonResponse;
use Exception;

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
}
