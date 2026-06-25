<?php

namespace App\Events;

use App\Models\Reserva;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReservaCreada
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $reserva;

    /**
     * Crea una nueva instancia del evento.
     */
    public function __construct(Reserva $reserva)
    {
        $this->reserva = $reserva;
    }
}
