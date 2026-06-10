<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Events\ReservaCreada;
use App\Events\ReservaEstadoCambiado;
use App\Listeners\NotificarCambioReserva;
use App\Listeners\NotificarCambioEstadoReserva;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
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
        Event::listen(
            ReservaEstadoCambiado::class,
            NotificarCambioEstadoReserva::class
        );
    }
}

