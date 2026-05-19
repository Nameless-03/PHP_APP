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

    /**
     * Cancelar una reserva validando políticas.
     */
    public function cancelar(Reserva $reserva, \App\Models\Usuario $usuario): Reserva
    {
        // Si el usuario es cliente, validar política de 10 horas
        if ($usuario->esCliente()) {
            $horasFaltantes = now()->diffInHours(Carbon::parse($reserva->fecha_hora_inicio), false);
            
            if ($horasFaltantes < 10) {
                throw new Exception("Política de cancelación: No puedes cancelar con menos de 10 horas de anticipación.");
            }
        }

        return $this->cambiarEstado($reserva, EstadoReservaEnum::CANCELADA);
    }

    /**
     * Reprogramar una reserva validando disponibilidad.
     */
    public function reprogramar(Reserva $reserva, string $nuevaFechaHora, \App\Models\Usuario $usuario): Reserva
    {
        return DB::transaction(function () use ($reserva, $nuevaFechaHora, $usuario) {
            $nuevoInicio = Carbon::parse($nuevaFechaHora);
            $nuevoFin = (clone $nuevoInicio)->addMinutes($reserva->servicio->duracion);

            if ($nuevoInicio->isPast()) {
                throw new Exception("No puedes reprogramar para una fecha en el pasado.");
            }

            // Validar que el nuevo horario esté disponible (ignorando esta misma reserva)
            $solapamiento = Reserva::where('id_servicio', $reserva->id_servicio)
                ->where('id', '!=', $reserva->id) // Ignorar la reserva actual
                ->whereIn('estado', [EstadoReservaEnum::PENDIENTE->value, EstadoReservaEnum::CONFIRMADA->value, EstadoReservaEnum::PAGADA->value])
                ->where(function ($query) use ($nuevoInicio, $nuevoFin) {
                    $query->whereBetween('fecha_hora_inicio', [$nuevoInicio, $nuevoFin])
                          ->orWhereBetween('fecha_hora_fin', [$nuevoInicio, $nuevoFin])
                          ->orWhere(function ($q) use ($nuevoInicio, $nuevoFin) {
                              $q->where('fecha_hora_inicio', '<=', $nuevoInicio)
                                ->where('fecha_hora_fin', '>=', $nuevoFin);
                          });
                })
                ->lockForUpdate()
                ->exists();

            if ($solapamiento) {
                throw new Exception("El horario seleccionado ya no está disponible.");
            }

            // Actualizar horas y volver a pendiente
            $reserva->update([
                'fecha_hora_inicio' => $nuevoInicio,
                'fecha_hora_fin' => $nuevoFin,
                'estado' => EstadoReservaEnum::PENDIENTE
            ]);

            return $reserva->fresh();
        });
    }
}
