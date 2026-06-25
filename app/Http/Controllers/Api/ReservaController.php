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
     * Almacena un recurso recién creado en el almacenamiento.
     */
    public function store(StoreReservaRequest $request): JsonResponse
    {
        if ($request->user()->activo === false) {
            return response()->json([
                'message' => 'Tu cuenta está deshabilitada. No puedes realizar reservas.'
            ], 403);
        }

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
     * Muestra el recurso especificado.
     */
    public function show(Reserva $reserva): JsonResponse
    {
        $this->authorize('view', $reserva);

        // Load relationships
        $reserva->load(['servicio.profesional.usuario', 'cliente.usuario', 'pago']);

        return response()->json([
            'data' => new ReservaResource($reserva),
        ]);
    }

    /**
     * Obtener la reserva actual o en curso para el usuario autenticado
     */
    public function actual(Request $request): JsonResponse
    {
        $usuario = $request->user();
        $now = \Carbon\Carbon::now();

        $query = Reserva::with(['servicio.profesional.usuario', 'cliente.usuario'])
            ->where('fecha_hora_inicio', '<=', $now)
            ->where('fecha_hora_fin', '>=', $now)
            ->whereIn('estado', [
                EstadoReservaEnum::PAGADA->value,
                EstadoReservaEnum::CONFIRMADA->value,
                EstadoReservaEnum::EN_CURSO->value
            ]);

        if ($usuario->esCliente()) {
            $query->where('id_cliente', $usuario->id)
                ->whereHas('servicio', function ($q) {
                    $q->whereIn('modalidad', ['remota', 'hibrida']);
                });
        } elseif ($usuario->esProfesional()) {
            $query->whereHas('servicio', function ($q) use ($usuario) {
                $q->where('id_profesional', $usuario->id);
            });
        } else {
            return response()->json(['data' => null]);
        }

        $reserva = $query->first();

        if (!$reserva) {
            return response()->json(['data' => null]);
        }

        return response()->json([
            'data' => new ReservaResource($reserva)
        ]);
    }

    /**
     * Update the state of the reservation.
     */
    public function updateEstado(UpdateEstadoReservaRequest $request, Reserva $reserva): JsonResponse
    {
        $this->authorize('updateEstado', $reserva);

        $nuevoEstado = EstadoReservaEnum::from($request->validated()['estado']);
        $usuario = $request->user();
        
        // El cliente solo puede cancelar o iniciar su reserva
        if ($usuario->esCliente() && !in_array($nuevoEstado, [EstadoReservaEnum::CANCELADA, EstadoReservaEnum::EN_CURSO])) {
            return response()->json(['message' => 'Los clientes solo pueden cancelar o iniciar reservas.'], 403);
        }

        try {
            if ($nuevoEstado === EstadoReservaEnum::CANCELADA) {
                $reservaActualizada = $this->reservaService->cancelar($reserva, $usuario);
            } else {
                $reservaActualizada = $this->reservaService->cambiarEstado($reserva, $nuevoEstado);
            }

            return response()->json([
                'message' => 'Estado de la reserva actualizado',
                'data' => new ReservaResource($reservaActualizada),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Reprogramar una reserva
     */
    public function reprogramar(Request $request, Reserva $reserva): JsonResponse
    {
        $this->authorize('updateEstado', $reserva); // Reutilizamos misma policy de acceso

        $request->validate([
            'fecha_hora_inicio' => ['required', 'date_format:Y-m-d H:i:s', 'after:now']
        ]);

        try {
            $usuario = $request->user();
            $reservaActualizada = $this->reservaService->reprogramar(
                $reserva, 
                $request->fecha_hora_inicio, 
                $usuario
            );

            // Indicar quién reprogramó (para que el frontend lo distinga)
            $reprogramacionPor = $usuario->esCliente() ? 'cliente' : 'profesional';

            return response()->json([
                'message' => 'Reserva reprogramada exitosamente',
                'data' => array_merge(
                    (new ReservaResource($reservaActualizada))->resolve(),
                    ['reprogramacion_por' => $reprogramacionPor]
                ),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }
}

