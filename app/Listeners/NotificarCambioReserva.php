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
            
            // 1. Enviar Notificación por Email (Laravel Notification)
            $event->reserva->cliente->usuario->notify(new ReservaEstadoNotificacion(
                $event->reserva,
                "Nueva Reserva Recibida",
                $mensaje
            ));

            // 2. Guardar en Base de Datos (Custom Table)
            Notificacion::create([
                'titulo' => 'Nueva Reserva',
                'mensaje' => $mensaje,
                'tipo' => TipoNotificacionEnum::CONFIRMACION,
                'id_usuario' => $event->reserva->cliente->id_usuario,
            ]);
        }

        if ($event instanceof ReservaEstadoCambiado) {
            $nuevoEstado = $event->reserva->estado->value;
            $mensaje = "El estado de tu reserva ha cambiado a: {$nuevoEstado}.";

            $event->reserva->cliente->usuario->notify(new ReservaEstadoNotificacion(
                $event->reserva,
                "Actualización de Reserva",
                $mensaje
            ));

            Notificacion::create([
                'titulo' => 'Actualización de Reserva',
                'mensaje' => $mensaje,
                'tipo' => TipoNotificacionEnum::MODIFICACION,
                'id_usuario' => $event->reserva->cliente->id_usuario,
            ]);
        }
    }
}
