<?php

namespace App\Listeners;

use App\Events\ReservaCreada;
use App\Events\ReservaEstadoCambiado;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Notifications\ReservaEstadoNotificacion;
use App\Models\Notificacion;
use App\Enums\TipoNotificacionEnum;

class NotificarCambioReserva
{

    /**
     * Maneja el evento.
     */
    public function handle(mixed $event): void
    {
        if ($event instanceof ReservaCreada) {
            $mensaje = "Tu reserva para '{$event->reserva->servicio->nombre}' ha sido recibida y está pendiente de confirmación.";

            // Notificar al Cliente
            $event->reserva->cliente->usuario->notify(new ReservaEstadoNotificacion(
                $event->reserva,
                "Nueva Reserva Recibida",
                $mensaje
            ));
            Notificacion::create([
                'titulo'     => 'Nueva Reserva',
                'mensaje'    => $mensaje,
                'tipo'       => TipoNotificacionEnum::CONFIRMACION,
                'id_usuario' => $event->reserva->cliente->id_usuario,
            ]);

            // Notificar al Profesional
            $profesional = $event->reserva->servicio->profesional;
            if ($profesional && $profesional->usuario) {
                $mensajeProf = "Tienes una nueva solicitud de reserva para '{$event->reserva->servicio->nombre}' del cliente '{$event->reserva->cliente->usuario->nombre}'.";
                $profesional->usuario->notify(new ReservaEstadoNotificacion(
                    $event->reserva,
                    "Nueva Solicitud de Reserva",
                    $mensajeProf
                ));
                Notificacion::create([
                    'titulo'     => 'Nueva Solicitud de Reserva',
                    'mensaje'    => $mensajeProf,
                    'tipo'       => TipoNotificacionEnum::CONFIRMACION,
                    'id_usuario' => $profesional->id_usuario,
                ]);
            }
        }

        if ($event instanceof ReservaEstadoCambiado) {
            $reserva    = $event->reserva;
            $nuevoEstado = $reserva->estado->value;
            $estadoAnterior = $event->estadoAnterior;

            // Determinar quién realizó la acción
            $porQuien = $event->porQuien ?? "el sistema";

            // === CANCELACIÓN ===
            if ($nuevoEstado === 'cancelada') {
                // Construir mensaje para el cliente
                if ($reserva->id_compra_paquete) {
                    $mensajeCliente = "Se ha devuelto la sesión a tu paquete.";
                } elseif ($reserva->pago) {
                    $pagoEstado = is_string($reserva->pago->estado)
                        ? $reserva->pago->estado
                        : ($reserva->pago->estado->value ?? '');
                    if ($pagoEstado === 'reembolsado') {
                        $mensajeCliente = "Su pago será devuelto.";
                    } else {
                        $mensajeCliente = "Tu reserva para '{$reserva->servicio->nombre}' ha sido cancelada.";
                    }
                } else {
                    $mensajeCliente = "Tu reserva para '{$reserva->servicio->nombre}' ha sido cancelada.";
                }
                $mensajeCliente .= " Se te a devuelto el dinero";

                // Notificar al cliente
                $reserva->cliente->usuario->notify(new ReservaEstadoNotificacion(
                    $reserva, "Reserva Cancelada", $mensajeCliente
                ));
                Notificacion::create([
                    'titulo'     => 'Reserva Cancelada',
                    'mensaje'    => $mensajeCliente,
                    'tipo'       => TipoNotificacionEnum::CANCELACION,
                    'id_usuario' => $reserva->cliente->id_usuario,
                ]);

                // Notificar al profesional si quien canceló no fue él
                if ($porQuien !== 'el profesional') {
                    $profesional = $reserva->servicio->profesional;
                    if ($profesional && $profesional->usuario) {
                        if ($porQuien === 'el sistema') {
                            $mensajeProf = "La reserva de '{$reserva->cliente->usuario->nombre}' para '{$reserva->servicio->nombre}' ha sido cancelada por el sistema debido a que no fue confirmada antes del límite permitido.";
                            $tituloProf = "Reserva Cancelada por el Sistema";
                        } else {
                            $mensajeProf = "El cliente '{$reserva->cliente->usuario->nombre}' ha cancelado su reserva para '{$reserva->servicio->nombre}'.";
                            $tituloProf = "Reserva Cancelada por el Cliente";
                        }
                        $profesional->usuario->notify(new ReservaEstadoNotificacion(
                            $reserva, $tituloProf, $mensajeProf
                        ));
                        Notificacion::create([
                            'titulo'     => $tituloProf,
                            'mensaje'    => $mensajeProf,
                            'tipo'       => TipoNotificacionEnum::CANCELACION,
                            'id_usuario' => $profesional->id_usuario,
                        ]);
                    }
                }

                return;
            }

            // === CONFIRMACIÓN (profesional confirma reserva o nueva fecha) ===
            if ($nuevoEstado === 'confirmada') {
                // Si venía de pagada, es una confirmación de fecha reprogramada
                $mensajeCliente = in_array($estadoAnterior, ['pagada'])
                    ? "El profesional ha confirmado la nueva fecha para tu reserva de '{$reserva->servicio->nombre}'. ¡Tu cita está agendada!"
                    : "Tu reserva para '{$reserva->servicio->nombre}' ha sido confirmada por el profesional. ¡Te esperamos!";

                $reserva->cliente->usuario->notify(new \App\Notifications\ReservaConfirmadaNotification(
                    $reserva
                ));
                Notificacion::create([
                    'titulo'     => 'Reserva Confirmada',
                    'mensaje'    => $mensajeCliente,
                    'tipo'       => TipoNotificacionEnum::CONFIRMACION,
                    'id_usuario' => $reserva->cliente->id_usuario,
                ]);

                return;
            }

            // === REPROGRAMACIÓN ===
            if (in_array($estadoAnterior, ['pagada', 'confirmada']) && in_array($nuevoEstado, ['pagada', 'confirmada'])) {
                $mensajeCliente = "Tu reserva para '{$reserva->servicio->nombre}' ha sido reprogramada por {$porQuien}. Revisa la nueva fecha y hora.";
                $reserva->cliente->usuario->notify(new ReservaEstadoNotificacion(
                    $reserva, "Reserva Reprogramada", $mensajeCliente
                ));
                Notificacion::create([
                    'titulo'     => 'Reserva Reprogramada',
                    'mensaje'    => $mensajeCliente,
                    'tipo'       => TipoNotificacionEnum::MODIFICACION,
                    'id_usuario' => $reserva->cliente->id_usuario,
                ]);

                // Si reprogramó el cliente, notificar al profesional para que confirme la nueva fecha
                if ($porQuien === 'ti' && $nuevoEstado === 'pagada') {
                    $profesional = $reserva->servicio->profesional;
                    if ($profesional && $profesional->usuario) {
                        $mensajeProf = "El cliente '{$reserva->cliente->usuario->nombre}' ha reprogramado su reserva de '{$reserva->servicio->nombre}'. Por favor confirma la nueva fecha.";
                        $profesional->usuario->notify(new ReservaEstadoNotificacion(
                            $reserva, "Fecha de Reserva Reprogramada – Confirmación Requerida", $mensajeProf
                        ));
                        Notificacion::create([
                            'titulo'     => 'Fecha Reprogramada – Confirmar',
                            'mensaje'    => $mensajeProf,
                            'tipo'       => TipoNotificacionEnum::MODIFICACION,
                            'id_usuario' => $profesional->id_usuario,
                        ]);
                    }
                }

                return;
            }

            // === RESERVA PAGADA (notificar al profesional) ===
            if ($nuevoEstado === 'pagada') {
                $profesional = $reserva->servicio->profesional;
                if ($profesional && $profesional->usuario) {
                    $mensajeProf = "La reserva de '{$reserva->cliente->usuario->nombre}' para '{$reserva->servicio->nombre}' ha sido pagada. Ya puedes confirmarla.";
                    $profesional->usuario->notify(new ReservaEstadoNotificacion(
                        $reserva,
                        "Reserva Pagada – Confirmación Requerida",
                        $mensajeProf
                    ));
                    Notificacion::create([
                        'titulo'     => 'Reserva Pagada – Confirmar',
                        'mensaje'    => $mensajeProf,
                        'tipo'       => TipoNotificacionEnum::CONFIRMACION,
                        'id_usuario' => $profesional->id_usuario,
                    ]);
                }
            }

            // === OTROS CAMBIOS DE ESTADO ===
            $nuevoEstadoLabel = match ($nuevoEstado) {
                'pendiente' => 'Pendiente',
                'confirmada' => 'Confirmada',
                'pagada' => 'Pagada',
                'en_curso' => 'En curso',
                'finalizada' => 'Finalizada',
                'cancelada' => 'Cancelada',
                'no_asistida' => 'No asistió',
                default => $nuevoEstado,
            };
            $mensaje = "El estado de tu reserva para '{$reserva->servicio->nombre}' ha cambiado a: {$nuevoEstadoLabel}.";
            $reserva->cliente->usuario->notify(new ReservaEstadoNotificacion(
                $reserva, "Actualización de Reserva", $mensaje
            ));
            Notificacion::create([
                'titulo'     => 'Actualización de Reserva',
                'mensaje'    => $mensaje,
                'tipo'       => TipoNotificacionEnum::MODIFICACION,
                'id_usuario' => $reserva->cliente->id_usuario,
            ]);
        }
    }
}
