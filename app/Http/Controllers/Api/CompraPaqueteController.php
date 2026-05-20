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

        if ($request->boolean('simular_error')) {
            return response()->json([
                'message' => 'Error en el procesamiento del pago. Operación cancelada.'
            ], 422);
        }

        $compra = DB::transaction(function () use ($request, $paquete) {
            $compra = CompraPaquete::create([
                'sesiones_disponibles' => $paquete->cantidad_sesiones,
                'fecha_compra' => now(),
                'estado' => 'activo',
                'id_cliente' => $request->user()->id,
                'id_paquete' => $paquete->id,
            ]);

            Pago::create([
                'monto' => $paquete->precio,
                'fecha' => now(),
                'metodo' => $request->metodo,
                'estado' => 'completado',
                'referencia_externa' => 'TXN_' . strtoupper(uniqid()),
                'id_compra' => $compra->id,
            ]);

            return $compra;
        });

        $compra->load(['paquete.servicios', 'pagos']);

        return response()->json([
            'message' => 'Compra registrada exitosamente y sesiones habilitadas.',
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
}
