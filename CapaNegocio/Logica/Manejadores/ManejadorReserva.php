<?php

namespace App\Manejadores;

use App\Models\Reserva;
use App\Models\Servicio;
use App\DataTypes\DtReserva;
use App\Enums\EstadoReservaEnum;
use DateTime;
use Exception;

class ManejadorReserva
{
    /**
     * Crea una nueva reserva.
     */
    public function crear(
        int $idCliente,
        int $idServicio,
        string $fechaHoraInicio,
        ?string $observaciones = null
    ): DtReserva {
        // Obtener el servicio
        $servicio = Servicio::findOrFail($idServicio);

        // Calcular fecha/hora de fin
        $inicio = new DateTime($fechaHoraInicio);
        $fin = clone $inicio;
        $fin->modify("+{$servicio->duracion} minutes");

        // Verificar disponibilidad
        if (!$this->verificarDisponibilidad($idServicio, $fechaHoraInicio, $fin->format('Y-m-d H:i:s'))) {
            throw new Exception("El horario no está disponible");
        }

        $reserva = Reserva::create([
            'id_cliente' => $idCliente,
            'id_servicio' => $idServicio,
            'fecha_hora_inicio' => $fechaHoraInicio,
            'fecha_hora_fin' => $fin->format('Y-m-d H:i:s'),
            'estado' => EstadoReservaEnum::PENDIENTE->value,
            'observaciones' => $observaciones,
        ]);

        $reserva->load(['cliente.usuario', 'servicio.profesional.usuario', 'pago']);

        return DtReserva::desdeModelo($reserva);
    }

    /**
     * Verifica si un horario está disponible.
     */
    public function verificarDisponibilidad(int $idServicio, string $fechaHoraInicio, string $fechaHoraFin): bool
    {
        $reservasConflicto = Reserva::where('id_servicio', $idServicio)
            ->whereIn('estado', [
                EstadoReservaEnum::CONFIRMADA->value,
                EstadoReservaEnum::PAGADA->value,
                EstadoReservaEnum::EN_CURSO->value
            ])
            ->where(function ($query) use ($fechaHoraInicio, $fechaHoraFin) {
                $query->whereBetween('fecha_hora_inicio', [$fechaHoraInicio, $fechaHoraFin])
                    ->orWhereBetween('fecha_hora_fin', [$fechaHoraInicio, $fechaHoraFin])
                    ->orWhere(function ($q) use ($fechaHoraInicio, $fechaHoraFin) {
                        $q->where('fecha_hora_inicio', '<=', $fechaHoraInicio)
                          ->where('fecha_hora_fin', '>=', $fechaHoraFin);
                    });
            })
            ->exists();

        return !$reservasConflicto;
    }

    /**
     * Obtiene una reserva por ID.
     */
    public function obtenerPorId(int $id): ?DtReserva
    {
        $reserva = Reserva::with(['cliente.usuario', 'servicio.profesional.usuario', 'pago'])->find($id);

        return $reserva ? DtReserva::desdeModelo($reserva) : null;
    }

    /**
     * Lista reservas por cliente.
     */
    public function listarPorCliente(int $idCliente): array
    {
        return Reserva::with(['cliente.usuario', 'servicio.profesional.usuario', 'pago'])
            ->where('id_cliente', $idCliente)
            ->orderBy('fecha_hora_inicio', 'desc')
            ->get()
            ->map(fn($reserva) => DtReserva::desdeModelo($reserva))
            ->toArray();
    }

    /**
     * Lista reservas por profesional.
     */
    public function listarPorProfesional(int $idProfesional): array
    {
        return Reserva::with(['cliente.usuario', 'servicio.profesional.usuario', 'pago'])
            ->whereHas('servicio', fn($query) => $query->where('id_profesional', $idProfesional))
            ->orderBy('fecha_hora_inicio', 'desc')
            ->get()
            ->map(fn($reserva) => DtReserva::desdeModelo($reserva))
            ->toArray();
    }

    /**
     * Lista reservas por estado.
     */
    public function listarPorEstado(string $estado): array
    {
        if (!EstadoReservaEnum::esValido($estado)) {
            throw new Exception("Estado inválido: {$estado}");
        }

        return Reserva::with(['cliente.usuario', 'servicio.profesional.usuario', 'pago'])
            ->porEstado($estado)
            ->orderBy('fecha_hora_inicio', 'desc')
            ->get()
            ->map(fn($reserva) => DtReserva::desdeModelo($reserva))
            ->toArray();
    }

    /**
     * Cambia el estado de una reserva.
     */
    public function cambiarEstado(int $id, string $nuevoEstado): DtReserva
    {
        if (!EstadoReservaEnum::esValido($nuevoEstado)) {
            throw new Exception("Estado inválido: {$nuevoEstado}");
        }

        $reserva = Reserva::with(['cliente.usuario', 'servicio.profesional.usuario', 'pago'])->findOrFail($id);

        $estadoActual = EstadoReservaEnum::from($reserva->estado);
        $estadoNuevo = EstadoReservaEnum::from($nuevoEstado);

        // Validar transición de estados
        if ($estadoActual->estaFinalizada()) {
            throw new Exception("No se puede cambiar el estado de una reserva finalizada");
        }

        $reserva->update(['estado' => $nuevoEstado]);

        return DtReserva::desdeModelo($reserva->fresh(['cliente.usuario', 'servicio.profesional.usuario', 'pago']));
    }

    /**
     * Confirma una reserva.
     */
    public function confirmar(int $id): DtReserva
    {
        return $this->cambiarEstado($id, EstadoReservaEnum::CONFIRMADA->value);
    }

    /**
     * Cancela una reserva.
     */
    public function cancelar(int $id): DtReserva
    {
        $reserva = Reserva::findOrFail($id);

        $estadoActual = EstadoReservaEnum::from($reserva->estado);

        if (!$estadoActual->puedeCancelarse()) {
            throw new Exception("Esta reserva no puede ser cancelada");
        }

        return $this->cambiarEstado($id, EstadoReservaEnum::CANCELADA->value);
    }

    /**
     * Marca una reserva como en curso.
     */
    public function iniciar(int $id): DtReserva
    {
        return $this->cambiarEstado($id, EstadoReservaEnum::EN_CURSO->value);
    }

    /**
     * Finaliza una reserva.
     */
    public function finalizar(int $id): DtReserva
    {
        return $this->cambiarEstado($id, EstadoReservaEnum::FINALIZADA->value);
    }

    /**
     * Marca una reserva como no asistida.
     */
    public function marcarNoAsistida(int $id): DtReserva
    {
        return $this->cambiarEstado($id, EstadoReservaEnum::NO_ASISTIDA->value);
    }

    /**
     * Actualiza una reserva.
     */
    public function actualizar(int $id, array $datos): DtReserva
    {
        $reserva = Reserva::with(['cliente.usuario', 'servicio.profesional.usuario', 'pago'])->findOrFail($id);

        $estadoActual = EstadoReservaEnum::from($reserva->estado);

        if (!$estadoActual->puedeModificarse()) {
            throw new Exception("Esta reserva no puede ser modificada");
        }

        // Si se cambia la fecha, verificar disponibilidad
        if (isset($datos['fecha_hora_inicio'])) {
            $servicio = $reserva->servicio;
            $inicio = new DateTime($datos['fecha_hora_inicio']);
            $fin = clone $inicio;
            $fin->modify("+{$servicio->duracion} minutes");

            $datos['fecha_hora_fin'] = $fin->format('Y-m-d H:i:s');

            // Verificar disponibilidad excluyendo la reserva actual
            if (!$this->verificarDisponibilidadExceptoReserva(
                $reserva->id_servicio,
                $datos['fecha_hora_inicio'],
                $datos['fecha_hora_fin'],
                $id
            )) {
                throw new Exception("El nuevo horario no está disponible");
            }
        }

        $reserva->update($datos);

        return DtReserva::desdeModelo($reserva->fresh(['cliente.usuario', 'servicio.profesional.usuario', 'pago']));
    }

    /**
     * Verifica disponibilidad excluyendo una reserva específica.
     */
    private function verificarDisponibilidadExceptoReserva(
        int $idServicio,
        string $fechaHoraInicio,
        string $fechaHoraFin,
        int $idReservaExcluir
    ): bool {
        $reservasConflicto = Reserva::where('id_servicio', $idServicio)
            ->where('id', '!=', $idReservaExcluir)
            ->whereIn('estado', [
                EstadoReservaEnum::CONFIRMADA->value,
                EstadoReservaEnum::PAGADA->value,
                EstadoReservaEnum::EN_CURSO->value
            ])
            ->where(function ($query) use ($fechaHoraInicio, $fechaHoraFin) {
                $query->whereBetween('fecha_hora_inicio', [$fechaHoraInicio, $fechaHoraFin])
                    ->orWhereBetween('fecha_hora_fin', [$fechaHoraInicio, $fechaHoraFin])
                    ->orWhere(function ($q) use ($fechaHoraInicio, $fechaHoraFin) {
                        $q->where('fecha_hora_inicio', '<=', $fechaHoraInicio)
                          ->where('fecha_hora_fin', '>=', $fechaHoraFin);
                    });
            })
            ->exists();

        return !$reservasConflicto;
    }

    /**
     * Elimina una reserva (soft delete).
     */
    public function eliminar(int $id): bool
    {
        $reserva = Reserva::findOrFail($id);

        return $reserva->delete();
    }
}
