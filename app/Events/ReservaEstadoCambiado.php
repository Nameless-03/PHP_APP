<?php

namespace App\Events;

use App\Models\Reserva;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReservaEstadoCambiado
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $reserva;
    public $estadoAnterior;
    public $porQuien;

    /**
     * Crea una nueva instancia del evento.
     */
    public function __construct(Reserva $reserva, string $estadoAnterior)
    {
        $this->reserva = $reserva;
        $this->estadoAnterior = $estadoAnterior;

        $porQuien = "el sistema";
        if (auth()->check()) {
            $authUser = auth()->user();
            if ($authUser->id === $reserva->cliente->id_usuario) {
                $porQuien = "ti";
            } elseif ($reserva->servicio->profesional && $authUser->id === $reserva->servicio->profesional->id_usuario) {
                $porQuien = "el profesional";
            } elseif ($authUser->esAdmin()) {
                $porQuien = "el administrador";
            }
        }
        $this->porQuien = $porQuien;
    }
}
