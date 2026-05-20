<?php

namespace App\Listeners;

use App\Events\ReservaEstadoCambiado;
use App\Notifications\ReservaConfirmadaNotification;
use App\Notifications\ReservaModificadaNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotificarCambioEstadoReserva
{
    /**
     * Handle the event.
     */
    public function handle(ReservaEstadoCambiado $event): void
    {
        $reserva = $event->reserva;
        $estadoActual = $reserva->estado->value;

        // Si se confirma, notificar al cliente
        if ($estadoActual === 'confirmada' && $event->estadoAnterior === 'pendiente') {
            $clienteUser = $reserva->cliente->usuario;
            $clienteUser->notify(new ReservaConfirmadaNotification($reserva));
        }

        // Si se cancela, notificar al cliente o al profesional
        if ($estadoActual === 'cancelada') {
            $clienteUser = $reserva->cliente->usuario;
            $profesionalUser = $reserva->servicio->profesional->usuario;

            // Ambos reciben la notificación de que fue cancelada
            $clienteUser->notify(new ReservaModificadaNotification($reserva, 'cancelada'));
            $profesionalUser->notify(new ReservaModificadaNotification($reserva, 'cancelada'));
        }
    }
}
