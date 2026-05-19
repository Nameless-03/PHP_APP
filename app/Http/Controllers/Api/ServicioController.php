<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServicioRequest;
use App\Http\Requests\UpdateServicioRequest;
use App\Http\Resources\ServicioResource;
use App\Models\Servicio;
use App\Services\ServicioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ServicioController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private ServicioService $servicioService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $servicios = $this->servicioService->listarTodos($request->all());

        return response()->json([
            'data' => ServicioResource::collection($servicios),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreServicioRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['id_profesional'] = $request->user()->id; // El id_profesional es el ID del usuario autenticado (si asumimos 1:1)

        $servicio = $this->servicioService->crear($data);

        return response()->json([
            'message' => 'Servicio creado exitosamente',
            'data' => new ServicioResource($servicio),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): JsonResponse
    {
        $servicio = $this->servicioService->obtenerPorId($id);

        return response()->json([
            'data' => new ServicioResource($servicio),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateServicioRequest $request, Servicio $servicio): JsonResponse
    {
        $this->authorize('update', $servicio);

        $updatedServicio = $this->servicioService->actualizar($servicio, $request->validated());

        return response()->json([
            'message' => 'Servicio actualizado exitosamente',
            'data' => new ServicioResource($updatedServicio),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Servicio $servicio): JsonResponse
    {
        $this->authorize('delete', $servicio);

        $this->servicioService->eliminar($servicio);

        return response()->json(null, 204);
    }
}
