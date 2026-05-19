<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExcepcionAgendaRequest;
use App\Http\Resources\ExcepcionAgendaResource;
use App\Models\ExcepcionAgenda;
use App\Services\ExcepcionAgendaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Exception;

class ExcepcionAgendaController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private ExcepcionAgendaService $excepcionService
    ) {}

    /**
     * Display a listing of the resource for a specific professional.
     */
    public function index(int $idProfesional): JsonResponse
    {
        $excepciones = $this->excepcionService->listarPorProfesional($idProfesional);

        return response()->json([
            'data' => ExcepcionAgendaResource::collection($excepciones),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreExcepcionAgendaRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['id_profesional'] = $request->user()->id;

        try {
            $excepcion = $this->excepcionService->crear($data);

            return response()->json([
                'message' => 'Regla de agenda configurada exitosamente',
                'data' => new ExcepcionAgendaResource($excepcion),
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ExcepcionAgenda $excepcion_agenda): JsonResponse
    {
        // El nombre de parámetro debe coincidir con el de la ruta
        $this->authorize('delete', $excepcion_agenda);

        $this->excepcionService->eliminar($excepcion_agenda);

        return response()->json(null, 204);
    }
}
