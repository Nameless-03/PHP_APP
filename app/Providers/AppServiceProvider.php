<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Events\ReservaCreada;
use App\Events\ReservaEstadoCambiado;
use App\Listeners\NotificarCambioReserva;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Registra cualquier servicio de la aplicación.
     */
    public function register(): void
    {
        //
    }

    /**
     * Inicializa cualquier servicio de la aplicación.
     */
    public function boot(): void
    {
        // Registrar listeners manualmente para asegurar su ejecución
        Event::listen(
            ReservaCreada::class,
            NotificarCambioReserva::class
        );
        Event::listen(
            ReservaEstadoCambiado::class,
            NotificarCambioReserva::class
        );
    }
}

