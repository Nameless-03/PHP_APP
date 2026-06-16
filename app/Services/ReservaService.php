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

use App\Services\NoSqlLoggerService;

class ReservaService
{
    public function __construct(
        private NoSqlLoggerService $logger
    ) {}
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

            $estadoReserva = EstadoReservaEnum::PENDIENTE;
            $crearPagoEfectivo = false;

            // Si se pasa id_compra_paquete, validar y procesar consumo de sesión
            if (!empty($data['id_compra_paquete'])) {
                $compra = \App\Models\CompraPaquete::with(['paquete.servicios', 'pagos'])->findOrFail($data['id_compra_paquete']);
                
                // Validar que pertenezca al cliente
                if ($compra->id_cliente !== $data['id_cliente']) {
                    throw new Exception("El paquete seleccionado no te pertenece.");
                }

                // Validar que esté activo y tenga sesiones
                if ($compra->estado !== 'activo' || $compra->sesiones_disponibles <= 0) {
                    throw new Exception("El paquete no tiene sesiones disponibles.");
                }

                // Validar que el servicio esté incluido en el paquete
                $servicioValido = $compra->paquete->servicios->contains($servicio->id);
                if (!$servicioValido) {
                    throw new Exception("El servicio seleccionado no está incluido en este paquete.");
                }

                // Descontar sesión
                $compra->decrement('sesiones_disponibles');
                
                // Si ya no quedan sesiones, agotar el paquete
                if ($compra->fresh()->sesiones_disponibles === 0) {
                    $compra->update(['estado' => 'agotado']);
                }

                // Check if the package was paid in cash
                $pagoPaqueteEfectivo = $compra->pagos()->where('metodo', 'efectivo')->exists();

                if ($pagoPaqueteEfectivo) {
                    $estadoReserva = EstadoReservaEnum::PENDIENTE;
                    $crearPagoEfectivo = true;
                } else {
                    $estadoReserva = EstadoReservaEnum::PAGADA;
                }
            }

            $reserva = Reserva::create([
                'fecha_hora_inicio' => $inicio,
                'fecha_hora_fin' => $fin,
                'estado' => $estadoReserva,
                'observaciones' => $data['observaciones'] ?? null,
                'id_cliente' => $data['id_cliente'],
                'id_servicio' => $servicio->id,
                'id_compra_paquete' => $data['id_compra_paquete'] ?? null,
            ]);

            if ($crearPagoEfectivo) {
                \App\Models\Pago::create([
                    'monto' => 0.00,
                    'metodo' => 'efectivo',
                    'estado' => \App\Enums\EstadoPagoEnum::PENDIENTE,
                    'id_reserva' => $reserva->id,
                ]);
            }

            // Generar videollamada automáticamente si es remota o híbrida
            if ($servicio->modalidad === 'remota' || $servicio->modalidad === 'hibrida') {
                try {
                    $reserva->videollamada()->create([
                        'enlace' => '/videollamada/' . $reserva->id,
                        'token' => 'room_key_' . \Illuminate\Support\Str::random(16),
                        'fecha_creacion' => now(),
                    ]);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("Error al generar la sesión de videollamada para la reserva {$reserva->id}: " . $e->getMessage());
                    // Registrar incidente y notificar internamente al usuario
                    \App\Models\Notificacion::create([
                        'titulo' => 'Error de Videollamada',
                        'mensaje' => "No se pudo generar la sesión de videollamada para tu reserva de '{$servicio->nombre}'. Se registrará el incidente.",
                        'tipo' => \App\Enums\TipoNotificacionEnum::OTRO,
                        'id_usuario' => $data['id_cliente'],
                    ]);
                }
            }

            // Disparar Evento de Dominio
            ReservaCreada::dispatch($reserva);

            $this->logger->log("Creación de reserva", 'info', [
                'reserva_id' => $reserva->id,
                'servicio' => $servicio->nombre,
                'fecha_inicio' => $reserva->fecha_hora_inicio->toIso8601String(),
                'monto' => $servicio->precio
            ], $reserva->id_cliente);

            return $reserva->load(['servicio', 'cliente.usuario', 'videollamada']);
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
        return Reserva::with(['servicio', 'compraPaquete.paquete', 'pago'])->where('id_cliente', $idCliente)->get();
    }

    /**
     * Listar reservas de un profesional.
     */
    public function listarPorProfesional(int $idProfesional): Collection
    {
        return Reserva::with(['cliente.usuario', 'servicio', 'compraPaquete.paquete', 'pago'])
            ->whereHas('servicio', function ($q) use ($idProfesional) {
                $q->where('id_profesional', $idProfesional);
            })->get();
    }

    /**
     * Verificar si una transición de estado es válida.
     */
    public function esTransicionValida(EstadoReservaEnum $estadoActual, EstadoReservaEnum $nuevoEstado): bool
    {
        if ($estadoActual === $nuevoEstado) {
            return true;
        }

        $transiciones = [
            EstadoReservaEnum::PENDIENTE->value => [
                EstadoReservaEnum::CONFIRMADA->value,
                EstadoReservaEnum::PAGADA->value,
                EstadoReservaEnum::CANCELADA->value,
            ],
            EstadoReservaEnum::CONFIRMADA->value => [
                EstadoReservaEnum::PAGADA->value,
                EstadoReservaEnum::EN_CURSO->value,
                EstadoReservaEnum::CANCELADA->value,
                EstadoReservaEnum::NO_ASISTIDA->value,
            ],
            EstadoReservaEnum::PAGADA->value => [
                EstadoReservaEnum::CONFIRMADA->value,
                EstadoReservaEnum::EN_CURSO->value,
                EstadoReservaEnum::CANCELADA->value,
                EstadoReservaEnum::NO_ASISTIDA->value,
            ],
            EstadoReservaEnum::EN_CURSO->value => [
                EstadoReservaEnum::FINALIZADA->value,
            ],
            // Estados terminales
            EstadoReservaEnum::FINALIZADA->value => [],
            EstadoReservaEnum::CANCELADA->value => [],
            EstadoReservaEnum::NO_ASISTIDA->value => [],
        ];

        return in_array($nuevoEstado->value, $transiciones[$estadoActual->value] ?? []);
    }

    /**
     * Cambiar el estado de una reserva.
     */
    public function cambiarEstado(Reserva $reserva, EstadoReservaEnum $nuevoEstado): Reserva
    {
        $estadoAnterior = $reserva->estado->value;

        if (!$this->esTransicionValida($reserva->estado, $nuevoEstado)) {
            throw new Exception("Transición de estado no válida de '{$reserva->estado->value}' a '{$nuevoEstado->value}'.");
        }
        
        return DB::transaction(function () use ($reserva, $nuevoEstado, $estadoAnterior) {
            // Reembolsar sesión si pasa a cancelada y no estaba ya cancelada
            if ($nuevoEstado === EstadoReservaEnum::CANCELADA && $reserva->id_compra_paquete && $reserva->estado !== EstadoReservaEnum::CANCELADA) {
                $compra = $reserva->compraPaquete;
                if ($compra) {
                    $compra->increment('sesiones_disponibles');
                    if ($compra->estado === 'agotado') {
                        $compra->update(['estado' => 'activo']);
                    }
                }
            }

            // Si la reserva se confirma y tiene un pago pendiente, completamos el pago
            if ($nuevoEstado === EstadoReservaEnum::CONFIRMADA && $reserva->pago && $reserva->pago->estado === 'pendiente') {
                $reserva->pago->update([
                    'estado' => 'completado',
                    'referencia_externa' => 'CASH_' . strtoupper(uniqid()),
                ]);
            }

            // Si la reserva se cancela y tiene un pago pendiente, lo marcamos como fallido
            if ($nuevoEstado === EstadoReservaEnum::CANCELADA && $reserva->pago && $reserva->pago->estado === 'pendiente') {
                $reserva->pago->update([
                    'estado' => 'fallido',
                ]);
            }

            $reserva->update([
                'estado' => $nuevoEstado
            ]);

            ReservaEstadoCambiado::dispatch($reserva->fresh(), $estadoAnterior);

            if ($nuevoEstado === EstadoReservaEnum::CANCELADA) {
                $reserva->delete();
            }

            // Log NoSQL activity
            $this->logger->log("Cambio de estado de reserva", 'info', [
                'reserva_id' => $reserva->id,
                'estado_anterior' => $estadoAnterior,
                'nuevo_estado' => $nuevoEstado->value
            ], $reserva->id_cliente);

            return $reserva;
        });
    }

    /**
     * Cancelar una reserva validando políticas.
     */
    public function cancelar(Reserva $reserva, \App\Models\Usuario $usuario): Reserva
    {
        // Si el usuario es cliente, validar política de cancelación configurable
        if ($usuario->esCliente()) {
            $limiteCancelacionHoras = $reserva->servicio->limite_cancelacion_horas ?? 10;
            $horasFaltantes = now()->diffInHours(Carbon::parse($reserva->fecha_hora_inicio), false);
            
            if ($horasFaltantes < $limiteCancelacionHoras) {
                throw new Exception("Política de cancelación: No puedes cancelar con menos de {$limiteCancelacionHoras} horas de anticipación.");
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
            $estadoAnterior = $reserva->estado->value;
            $reserva->update([
                'fecha_hora_inicio' => $nuevoInicio,
                'fecha_hora_fin' => $nuevoFin,
                'estado' => EstadoReservaEnum::PENDIENTE
            ]);

            $reservaRenovada = $reserva->fresh();
            
            // Notificar a ambas partes sobre la reprogramación
            $clienteUser = $reservaRenovada->cliente->usuario;
            $profesionalUser = $reservaRenovada->servicio->profesional->usuario;
            
            $clienteUser->notify(new \App\Notifications\ReservaModificadaNotification($reservaRenovada, 'reprogramada'));
            $profesionalUser->notify(new \App\Notifications\ReservaModificadaNotification($reservaRenovada, 'reprogramada'));

            // Log NoSQL activity
            $this->logger->log("Reprogramación de reserva", 'info', [
                'reserva_id' => $reserva->id,
                'nueva_fecha' => $nuevoInicio->toIso8601String()
            ], $usuario->id);

            return $reservaRenovada;
        });
    }
}
