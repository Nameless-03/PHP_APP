<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReservaRequest;
use App\Http\Requests\UpdateEstadoReservaRequest;
use App\Http\Resources\ReservaResource;
use App\Models\Reserva;
use App\Services\ReservaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Enums\EstadoReservaEnum;
use Exception;

class ReservaController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private ReservaService $reservaService
    ) {}

    /**
     * Display a listing of the resource for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $usuario = $request->user();
        
        if ($usuario->esCliente()) {
            $reservas = $this->reservaService->listarPorCliente($usuario->id);
        } elseif ($usuario->esProfesional()) {
            $reservas = $this->reservaService->listarPorProfesional($usuario->id);
        } else {
            // Admin can see everything or we can leave it empty
            $reservas = collect(); 
        }

        return response()->json([
            'data' => ReservaResource::collection($reservas),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreReservaRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['id_cliente'] = $request->user()->id;

        try {
            $reserva = $this->reservaService->crear($data);

            return response()->json([
                'message' => 'Reserva creada exitosamente',
                'data' => new ReservaResource($reserva),
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Reserva $reserva): JsonResponse
    {
        $this->authorize('view', $reserva);

        // Load relationships
        $reserva->load(['servicio.profesional.usuario', 'cliente.usuario']);

        return response()->json([
            'data' => new ReservaResource($reserva),
        ]);
    }

    /**
     * Update the state of the reservation.
     */
    public function updateEstado(UpdateEstadoReservaRequest $request, Reserva $reserva): JsonResponse
    {
        $this->authorize('updateEstado', $reserva);

        $nuevoEstado = EstadoReservaEnum::from($request->validated()['estado']);
        
        // Aquí se podrían agregar validaciones extra (ej: el cliente solo puede cancelar, no confirmar)
        if ($request->user()->esCliente() && $nuevoEstado !== EstadoReservaEnum::CANCELADA) {
            return response()->json(['message' => 'Los clientes solo pueden cancelar reservas.'], 403);
        }

        $reservaActualizada = $this->reservaService->cambiarEstado($reserva, $nuevoEstado);

        return response()->json([
            'message' => 'Estado de la reserva actualizado',
            'data' => new ReservaResource($reservaActualizada),
        ]);
    }
}
