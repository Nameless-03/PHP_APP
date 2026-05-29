<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CompraPaqueteResource;
use App\Models\CompraPaquete;
use App\Models\Pago;
use App\Models\Paquete;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompraPaqueteController extends Controller
{
    /**
     * Adquirir/comprar un paquete de sesiones.
     */
    public function comprar(Request $request, Paquete $paquete): JsonResponse
    {
        if (!$request->user() || !$request->user()->esCliente()) {
            return response()->json([
                'message' => 'Solo los clientes pueden adquirir paquetes de sesiones.'
            ], 403);
        }

        $request->validate([
            'metodo' => 'required|in:paypal,efectivo,transferencia,otro',
            'simular_error' => 'nullable|boolean',
        ]);

        $compra = DB::transaction(function () use ($request, $paquete) {
            // Create pending package purchase initially with 0 sessions
            $compra = CompraPaquete::create([
                'sesiones_disponibles' => 0,
                'fecha_compra' => now(),
                'estado' => 'pendiente',
                'id_cliente' => $request->user()->id,
                'id_paquete' => $paquete->id,
            ]);

            // Call PagoService to initiate payment
            $pagoService = app(\App\Services\PagoService::class);
            $pagoService->iniciarPago([
                'id_compra' => $compra->id,
                'monto' => $paquete->precio,
                'metodo' => $request->metodo,
                'simular_error' => $request->boolean('simular_error'),
            ]);

            return $compra;
        });

        // Load relations, making sure we get the fresh status of the purchase and payments
        $compra->refresh();
        $compra->load(['paquete.servicios', 'pagos']);

        return response()->json([
            'message' => 'Compra registrada y proceso de pago iniciado.',
            'data' => new CompraPaqueteResource($compra),
        ], 201);
    }

    /**
     * Listar los paquetes adquiridos del cliente autenticado.
     */
    public function misPaquetes(Request $request): JsonResponse
    {
        if (!$request->user() || !$request->user()->esCliente()) {
            return response()->json([
                'message' => 'Solo los clientes pueden consultar sus paquetes.'
            ], 403);
        }

        $compras = CompraPaquete::where('id_cliente', $request->user()->id)
            ->with(['paquete.servicios', 'pagos'])
            ->latest()
            ->get();

        return response()->json([
            'data' => CompraPaqueteResource::collection($compras),
        ]);
    }

    /**
     * Cancelar y eliminar una compra de paquete pendiente.
     */
    public function destroy(Request $request, CompraPaquete $compraPaquete): JsonResponse
    {
        if (!$request->user() || $compraPaquete->id_cliente !== $request->user()->id) {
            return response()->json([
                'message' => 'No autorizado.'
            ], 403);
        }

        if ($compraPaquete->estado !== 'pendiente') {
            return response()->json([
                'message' => 'Solo se pueden cancelar compras de paquetes en estado pendiente.'
            ], 422);
        }

        // Delete associated pending payments first (cascading or manual)
        $compraPaquete->pagos()->delete();
        $compraPaquete->delete();

        return response()->json([
            'message' => 'Compra de paquete cancelada y eliminada con éxito.'
        ]);
    }
}
