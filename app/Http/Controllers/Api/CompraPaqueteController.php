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

        if ($request->user()->activo === false) {
            return response()->json([
                'message' => 'Tu cuenta está deshabilitada. No puedes adquirir paquetes.'
            ], 403);
        }

        $request->validate([
            'metodo' => 'required|in:paypal,efectivo',
            'simular_error' => 'nullable|boolean',
        ]);

        // Verificar si el paquete está activo (todos sus servicios asociados están activos)
        $pivotCount = DB::table('paquete_servicio')->where('id_paquete', $paquete->id)->count();
        $activeCount = DB::table('paquete_servicio')
            ->join('servicios', 'paquete_servicio.id_servicio', '=', 'servicios.id')
            ->where('paquete_servicio.id_paquete', $paquete->id)
            ->where('servicios.activo', true)
            ->whereNull('servicios.deleted_at')
            ->count();
        $activo = ($pivotCount > 0 && $pivotCount === $activeCount);

        if (!$activo) {
            return response()->json([
                'message' => 'Este paquete no está disponible para compra porque uno o más de sus servicios asociados está inactivo o ha sido eliminado.'
            ], 422);
        }

        $compra = DB::transaction(function () use ($request, $paquete) {
            // Toda compra de paquete comienza en pendiente (tanto efectivo como paypal)
            // hasta que el pago correspondiente sea completado/aprobado.
            $compra = CompraPaquete::create([
                'sesiones_disponibles' => 0,
                'fecha_compra' => now(),
                'estado' => 'pendiente',
                'id_cliente' => $request->user()->id,
                'id_paquete' => $paquete->id,
            ]);

            // Inicializar las sesiones en la tabla tracker en 0 (disponibles = 0)
            foreach ($paquete->servicios as $servicio) {
                $cantidad = $servicio->pivot->cantidad_sesiones ?? 1;
                DB::table('compra_paquete_servicio')->insert([
                    'id_compra_paquete' => $compra->id,
                    'id_servicio' => $servicio->id,
                    'sesiones_totales' => $cantidad,
                    'sesiones_disponibles' => 0,
                ]);
            }

            // Llamar a PagoService para registrar el pago pendiente
            $pagoService = app(\App\Services\PagoService::class);
            $pagoService->iniciarPago([
                'id_compra' => $compra->id,
                'monto' => $paquete->precio,
                'metodo' => $request->metodo,
                'simular_error' => $request->boolean('simular_error'),
            ]);

            return $compra;
        });

        $compra->refresh();
        $compra->load(['paquete.servicios', 'pagos', 'serviciosTracker']);

        if ($compra->pagos->contains('estado', \App\Enums\EstadoPagoEnum::FALLIDO)) {
            $compra->pagos()->delete();
            $compra->delete();
            return response()->json([
                'message' => 'Error en el procesamiento del pago. Operación cancelada.'
            ], 422);
        }

        app(\App\Services\NoSqlLoggerService::class)->log("Compra de paquete de sesiones", 'info', [
            'compra_id' => $compra->id,
            'paquete' => $paquete->titulo,
            'monto' => $paquete->precio,
            'metodo' => $request->metodo
        ], $request->user()->id);

        return response()->json([
            'message' => 'Compra de paquete registrada y proceso de pago iniciado.',
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
            ->with(['paquete.servicios', 'paquete.profesional.usuario', 'pagos', 'serviciosTracker'])
            ->latest()
            ->get();

        return response()->json([
            'data' => CompraPaqueteResource::collection($compras),
        ]);
    }

    /**
     * Eliminar/limpiar una compra de paquete del inventario.
     */
    public function destroy(Request $request, CompraPaquete $compraPaquete): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $esDuenoCliente = ($compraPaquete->id_cliente === $user->id);
        $esProfesionalDelPaquete = ($compraPaquete->paquete->id_profesional === $user->id);

        if (!$esDuenoCliente && !$esProfesionalDelPaquete) {
            return response()->json([
                'message' => 'No estás autorizado para realizar esta acción.'
            ], 403);
        }

        $estadosPermitidos = ['pendiente', 'cancelado', 'agotado', 'vencido'];
        if (!in_array($compraPaquete->estado, $estadosPermitidos)) {
            return response()->json([
                'message' => 'Solo se pueden eliminar paquetes en estado pendiente, cancelado, agotado o vencido.'
            ], 422);
        }

        DB::transaction(function () use ($compraPaquete) {
            $compraPaquete->pagos()->delete();
            DB::table('compra_paquete_servicio')->where('id_compra_paquete', $compraPaquete->id)->delete();
            $compraPaquete->delete();
        });

        return response()->json([
            'message' => 'El paquete ha sido eliminado del inventario con éxito.'
        ]);
    }

    /**
     * Cancelar un paquete activo por parte del cliente.
     */
    public function cancelar(Request $request, CompraPaquete $compraPaquete): JsonResponse
    {
        if (!$request->user() || $compraPaquete->id_cliente !== $request->user()->id) {
            return response()->json([
                'message' => 'No autorizado.'
            ], 403);
        }

        if ($compraPaquete->estado !== 'activo') {
            return response()->json([
                'message' => 'Solo se pueden cancelar paquetes en estado activo.'
            ], 422);
        }

        DB::transaction(function () use ($compraPaquete) {
            $compraPaquete->update([
                'estado' => 'cancelado',
                'sesiones_disponibles' => 0,
            ]);

            DB::table('compra_paquete_servicio')
                ->where('id_compra_paquete', $compraPaquete->id)
                ->update(['sesiones_disponibles' => 0]);
        });

        return response()->json([
            'message' => 'El paquete ha sido cancelado con éxito. Las sesiones restantes han sido anuladas.'
        ]);
    }

    /**
     * Listar compras de paquetes pendientes de pago para el profesional.
     */
    public function comprasPendientes(Request $request): JsonResponse
    {
        if (!$request->user() || !$request->user()->esProfesional()) {
            return response()->json([
                'message' => 'Solo los profesionales pueden consultar pagos pendientes de paquetes.'
            ], 403);
        }

        $compras = CompraPaquete::whereHas('paquete', function ($query) use ($request) {
                $query->where('id_profesional', $request->user()->id);
            })
            ->where('estado', 'pendiente')
            ->with(['paquete.servicios', 'cliente.usuario', 'pagos'])
            ->latest()
            ->get();

        return response()->json([
            'data' => CompraPaqueteResource::collection($compras),
        ]);
    }

    /**
     * Aprobar el pago en efectivo/transferencia de un paquete pendiente.
     */
    public function aprobarPago(Request $request, CompraPaquete $compraPaquete): JsonResponse
    {
        if (!$request->user() || !$request->user()->esProfesional()) {
            return response()->json([
                'message' => 'Solo los profesionales pueden aprobar pagos de paquetes.'
            ], 403);
        }

        if ($compraPaquete->paquete->id_profesional !== $request->user()->id) {
            return response()->json([
                'message' => 'No estás autorizado para aprobar el pago de este paquete.'
            ], 403);
        }

        if ($compraPaquete->estado !== 'pendiente') {
            return response()->json([
                'message' => 'Solo se pueden aprobar compras de paquetes en estado pendiente.'
            ], 422);
        }

        DB::transaction(function () use ($compraPaquete) {
            // Habilitar sesiones globales
            $compraPaquete->update([
                'estado' => 'activo',
                'sesiones_disponibles' => $compraPaquete->paquete->cantidad_sesiones,
            ]);

            // Aprobar el pago asociado
            $compraPaquete->pagos()->where('estado', \App\Enums\EstadoPagoEnum::PENDIENTE)->update([
                'estado' => \App\Enums\EstadoPagoEnum::COMPLETADO,
                'referencia_externa' => 'CASH_PKG_' . strtoupper(uniqid()),
            ]);

            // Habilitar sesiones por servicio en la tabla tracker
            DB::table('compra_paquete_servicio')
                ->where('id_compra_paquete', $compraPaquete->id)
                ->update([
                    'sesiones_disponibles' => DB::raw('sesiones_totales')
                ]);
        });

        // Notificar al cliente
        $cliente = $compraPaquete->cliente->usuario;
        $mensaje = "Tu pago para el paquete '{$compraPaquete->paquete->nombre}' fue aprobado. El paquete está habilitado con {$compraPaquete->paquete->cantidad_sesiones} sesiones.";
        
        $cliente->notify(new \App\Notifications\PagoNotificacion(
            "Compra de Paquete Exitosa",
            $mensaje,
            'confirmacion'
        ));

        \App\Models\Notificacion::create([
            'titulo' => 'Compra de Paquete Exitosa',
            'mensaje' => $mensaje,
            'tipo' => \App\Enums\TipoNotificacionEnum::CONFIRMACION,
            'id_usuario' => $cliente->id,
        ]);

        return response()->json([
            'message' => 'Pago aprobado y paquete activado exitosamente.',
            'data' => new CompraPaqueteResource($compraPaquete->load(['paquete.servicios', 'pagos', 'serviciosTracker']))
        ]);
    }
}
