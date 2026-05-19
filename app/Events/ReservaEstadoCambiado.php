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

    /**
     * Create a new event instance.
     */
    public function __construct(Reserva $reserva, string $estadoAnterior)
    {
        $this->reserva = $reserva;
        $this->estadoAnterior = $estadoAnterior;
    }
}
