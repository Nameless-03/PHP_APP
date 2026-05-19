<?php

namespace App\Services;

use App\Models\Reserva;
use App\Models\Servicio;
use App\Enums\EstadoReservaEnum;
use App\Events\ReservaCreada;
use App\Events\ReservaEstadoCambiado;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Exception;

class ReservaService
{
    /**
     * Crear una nueva reserva manejando concurrencia.
     */
    public function crear(array $data): Reserva
    {
        return DB::transaction(function () use ($data) {
            $servicio = Servicio::findOrFail($data['id_servicio']);
            
            $inicio = Carbon::parse($data['fecha_hora_inicio']);
            $fin = (clone $inicio)->addMinutes($servicio->duracion);

            // Validar que la fecha sea en el futuro
            if ($inicio->isPast()) {
                throw new Exception("No puedes reservar en el pasado.");
            }

            // Validar solapamiento (Concurrency check usando un lock pesimista o simplemente validando)
            $solapamiento = Reserva::where('id_servicio', $servicio->id)
                ->whereIn('estado', [EstadoReservaEnum::PENDIENTE->value, EstadoReservaEnum::CONFIRMADA->value, EstadoReservaEnum::PAGADA->value])
                ->where(function ($query) use ($inicio, $fin) {
                    $query->whereBetween('fecha_hora_inicio', [$inicio, $fin])
                          ->orWhereBetween('fecha_hora_fin', [$inicio, $fin])
                          ->orWhere(function ($q) use ($inicio, $fin) {
                              $q->where('fecha_hora_inicio', '<=', $inicio)
                                ->where('fecha_hora_fin', '>=', $fin);
                          });
                })
                ->lockForUpdate()
                ->exists();

            if ($solapamiento) {
                throw new Exception("El horario seleccionado ya no está disponible.");
            }

            $reserva = Reserva::create([
                'fecha_hora_inicio' => $inicio,
                'fecha_hora_fin' => $fin,
                'estado' => EstadoReservaEnum::PENDIENTE,
                'observaciones' => $data['observaciones'] ?? null,
                'id_cliente' => $data['id_cliente'],
                'id_servicio' => $servicio->id,
            ]);

            // Disparar Evento de Dominio
            ReservaCreada::dispatch($reserva);

            return $reserva->load(['servicio', 'cliente.usuario']);
        });
    }

    /**
     * Obtener una reserva por ID.
     */
    public function obtenerPorId(int $id): Reserva
    {
        return Reserva::with(['servicio.profesional.usuario', 'cliente.usuario'])->findOrFail($id);
    }

    /**
     * Listar reservas del cliente.
     */
    public function listarPorCliente(int $idCliente): Collection
    {
        return Reserva::with(['servicio'])->where('id_cliente', $idCliente)->get();
    }

    /**
     * Listar reservas de un profesional.
     */
    public function listarPorProfesional(int $idProfesional): Collection
    {
        return Reserva::with(['cliente.usuario', 'servicio'])
            ->whereHas('servicio', function ($q) use ($idProfesional) {
                $q->where('id_profesional', $idProfesional);
            })->get();
    }

    /**
     * Cambiar el estado de una reserva.
     */
    public function cambiarEstado(Reserva $reserva, EstadoReservaEnum $nuevoEstado): Reserva
    {
        $estadoAnterior = $reserva->estado->value;
        
        $reserva->update([
            'estado' => $nuevoEstado
        ]);

        ReservaEstadoCambiado::dispatch($reserva->fresh(), $estadoAnterior);

        return $reserva;
    }
}
