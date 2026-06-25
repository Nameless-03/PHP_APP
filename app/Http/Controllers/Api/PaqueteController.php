<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaqueteRequest;
use App\Http\Resources\PaqueteResource;
use App\Models\Paquete;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaqueteController extends Controller
{
    /**
     * Muestra una lista del recurso.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Paquete::with(['servicios', 'profesional.usuario']);

        $user = $request->user() ?? auth('sanctum')->user();

        if ($request->has('id_profesional')) {
            $query->where('id_profesional', $request->id_profesional);
        } elseif ($user && $user->esProfesional()) {
            $query->where('id_profesional', $user->id);
        }

        // Si es un cliente o invitado, filtrar por profesionales activos
        $esPropio = ($user && $user->esProfesional() && (!$request->has('id_profesional') || (int)$request->id_profesional === $user->id));
        if (!$esPropio) {
            $query->whereHas('profesional.usuario', function ($q) {
                $q->where('activo', true);
            });
        }

        $paquetes = $query->latest()->get();

        // Si es un cliente o invitado, filtrar los paquetes que tienen servicios inactivos
        if (!$esPropio) {
            $paquetes = $paquetes->filter(function ($paquete) {
                $pivotCount = \DB::table('paquete_servicio')->where('id_paquete', $paquete->id)->count();
                $activeCount = \DB::table('paquete_servicio')
                    ->join('servicios', 'paquete_servicio.id_servicio', '=', 'servicios.id')
                    ->where('paquete_servicio.id_paquete', $paquete->id)
                    ->where('servicios.activo', true)
                    ->whereNull('servicios.deleted_at')
                    ->count();
                return $pivotCount > 0 && $pivotCount === $activeCount;
            });
        }

        return response()->json([
            'data' => PaqueteResource::collection($paquetes),
        ]);
    }

    /**
     * Almacena un recurso recién creado en el almacenamiento.
     */
    public function store(StorePaqueteRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['id_profesional'] = $request->user()->id;

        // Calcular cantidad de sesiones y el precio final del paquete
        $totalSesiones = 0;
        $totalPrecioSinDescuento = 0.00;
        $serviciosInput = $data['servicios'];

        foreach ($serviciosInput as $item) {
            $servicio = \App\Models\Servicio::find($item['id']);
            $cantidad = (int) $item['cantidad_sesiones'];
            $totalSesiones += $cantidad;
            $totalPrecioSinDescuento += ((float) $servicio->precio) * $cantidad;
        }

        $descuento = (float) $data['descuento'];
        $precioFinal = max(0.00, $totalPrecioSinDescuento - $descuento);

        $paquete = Paquete::create([
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'] ?? null,
            'cantidad_sesiones' => $totalSesiones,
            'precio' => $precioFinal,
            'descuento' => $descuento,
            'vencimiento' => $data['vencimiento'] ?? null,
            'id_profesional' => $data['id_profesional'],
        ]);
        
        // Asociar los servicios vinculados al paquete con sus sesiones
        $syncData = [];
        foreach ($serviciosInput as $item) {
            $syncData[$item['id']] = ['cantidad_sesiones' => $item['cantidad_sesiones']];
        }
        $paquete->servicios()->sync($syncData);

        return response()->json([
            'message' => 'Paquete creado exitosamente',
            'data' => new PaqueteResource($paquete->load(['servicios', 'profesional.usuario'])),
        ], 201);
    }

    /**
     * Elimina el recurso especificado del almacenamiento.
     */
    public function destroy(Request $request, Paquete $paquete): JsonResponse
    {
        if ($paquete->id_profesional !== $request->user()->id) {
            return response()->json([
                'message' => 'No estás autorizado para eliminar este paquete.'
            ], 403);
        }

        $paquete->delete();

        return response()->json(null, 204);
    }
}

