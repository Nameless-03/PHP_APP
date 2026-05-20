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
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Paquete::with('servicios');

        if ($request->has('id_profesional')) {
            $query->where('id_profesional', $request->id_profesional);
        } elseif ($request->user() && $request->user()->esProfesional()) {
            $query->where('id_profesional', $request->user()->id);
        }

        $paquetes = $query->latest()->get();

        return response()->json([
            'data' => PaqueteResource::collection($paquetes),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePaqueteRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['id_profesional'] = $request->user()->id;

        $paquete = Paquete::create($data);
        
        // Asociar los servicios vinculados al paquete
        if (isset($data['servicios'])) {
            $paquete->servicios()->sync($data['servicios']);
        }

        return response()->json([
            'message' => 'Paquete creado exitosamente',
            'data' => new PaqueteResource($paquete->load('servicios')),
        ], 201);
    }

    /**
     * Remove the specified resource from storage.
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
