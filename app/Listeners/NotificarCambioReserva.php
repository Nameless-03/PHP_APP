<?php

namespace App\Listeners;

use App\Events\ReservaCreada;
use App\Events\ReservaEstadoCambiado;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Notifications\ReservaEstadoNotificacion;
use App\Models\Notificacion;
use App\Enums\TipoNotificacionEnum;

class NotificarCambioReserva implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(mixed $event): void
    {
        if ($event instanceof ReservaCreada) {
            $mensaje = "Tu reserva para '{$event->reserva->servicio->nombre}' ha sido recibida y está pendiente de confirmación.";
            
            // 1. Enviar Notificación por Email (Laravel Notification) al cliente
            $event->reserva->cliente->usuario->notify(new ReservaEstadoNotificacion(
                $event->reserva,
                "Nueva Reserva Recibida",
                $mensaje
            ));

            // 2. Guardar en Base de Datos (Custom Table) para el cliente
            Notificacion::create([
                'titulo' => 'Nueva Reserva',
                'mensaje' => $mensaje,
                'tipo' => TipoNotificacionEnum::CONFIRMACION,
                'id_usuario' => $event->reserva->cliente->id_usuario,
            ]);

            // 3. Notificar al Profesional
            $profesional = $event->reserva->servicio->profesional;
            if ($profesional && $profesional->usuario) {
                $mensajeProfesional = "Tienes una nueva solicitud de reserva para '{$event->reserva->servicio->nombre}' del cliente '{$event->reserva->cliente->usuario->nombre}'.";
                
                $profesional->usuario->notify(new ReservaEstadoNotificacion(
                    $event->reserva,
                    "Nueva Solicitud de Reserva",
                    $mensajeProfesional
                ));

                Notificacion::create([
                    'titulo' => 'Nueva Solicitud de Reserva',
                    'mensaje' => $mensajeProfesional,
                    'tipo' => TipoNotificacionEnum::CONFIRMACION,
                    'id_usuario' => $profesional->id_usuario,
                ]);
            }
        }

        if ($event instanceof ReservaEstadoCambiado) {
            $reserva = $event->reserva;
            $nuevoEstado = $reserva->estado->value;

            if ($nuevoEstado === 'cancelada') {
                if ($reserva->id_compra_paquete) {
                    $mensaje = "Tu reserva para '{$reserva->servicio->nombre}' ha sido cancelada por el profesional. Se ha devuelto la sesión a tu paquete.";
                } else {
                    $pagoMetodo = $reserva->pago ? ($reserva->pago->metodo->value ?? $reserva->pago->metodo) : '';
                    if ($pagoMetodo === 'paypal') {
                        $mensaje = "Tu reserva para '{$reserva->servicio->nombre}' ha sido cancelada por el profesional. Su pago será devuelto.";
                    } else {
                        $mensaje = "Tu reserva para '{$reserva->servicio->nombre}' ha sido cancelada por el profesional.";
                    }
                }
            } else {
                $mensaje = "El estado de tu reserva ha cambiado a: {$nuevoEstado}.";
            }

            if ($nuevoEstado !== 'confirmada') {
                $reserva->cliente->usuario->notify(new ReservaEstadoNotificacion(
                    $reserva,
                    "Actualización de Reserva",
                    $mensaje
                ));
            }

            Notificacion::create([
                'titulo' => 'Actualización de Reserva',
                'mensaje' => $mensaje,
                'tipo' => TipoNotificacionEnum::MODIFICACION,
                'id_usuario' => $reserva->cliente->id_usuario,
            ]);
        }
    }
}
