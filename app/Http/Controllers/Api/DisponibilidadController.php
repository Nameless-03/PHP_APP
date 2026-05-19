<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDisponibilidadRequest;
use App\Http\Resources\DisponibilidadResource;
use App\Models\Disponibilidad;
use App\Services\DisponibilidadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Exception;

class DisponibilidadController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private DisponibilidadService $disponibilidadService
    ) {}

    /**
     * Display a listing of the resource for a specific professional.
     */
    public function index(int $idProfesional): JsonResponse
    {
        $disponibilidades = $this->disponibilidadService->listarPorProfesional($idProfesional);

        return response()->json([
            'data' => DisponibilidadResource::collection($disponibilidades),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDisponibilidadRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['id_profesional'] = $request->user()->id;

        try {
            $disponibilidad = $this->disponibilidadService->crear($data);

            return response()->json([
                'message' => 'Disponibilidad configurada exitosamente',
                'data' => new DisponibilidadResource($disponibilidad),
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreDisponibilidadRequest $request, Disponibilidad $disponibilidad): JsonResponse
    {
        $this->authorize('update', $disponibilidad);

        $data = $request->validated();
        
        try {
            $disponibilidad = $this->disponibilidadService->actualizar($disponibilidad, $data);

            return response()->json([
                'message' => 'Disponibilidad actualizada exitosamente',
                'data' => new DisponibilidadResource($disponibilidad),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Disponibilidad $disponibilidad): JsonResponse
    {
        $this->authorize('delete', $disponibilidad);

        $this->disponibilidadService->eliminar($disponibilidad);

        return response()->json(null, 204);
    }
}
