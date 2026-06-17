<?php

namespace App\Listeners;

use App\Events\ReservaEstadoCambiado;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

/**
 * Este listener fue consolidado en NotificarCambioReserva para evitar
 * notificaciones duplicadas. Mantener la clase vacía para no romper
 * el registro en AppServiceProvider.
 */
class NotificarCambioEstadoReserva
{
    /**
     * Handle the event.
     * @deprecated Lógica movida a NotificarCambioReserva
     */
    public function handle(ReservaEstadoCambiado $event): void
    {
        // Vacío — toda la lógica está en NotificarCambioReserva
    }
}
