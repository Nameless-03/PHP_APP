<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ServicioActualizado implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $tipo; // 'cancelacion'
    public array $data;

    /**
     * Crea una nueva instancia del evento.
     */
    public function __construct(string $tipo, array $data)
    {
        $this->tipo = $tipo;
        $this->data = $data;
    }

    /**
     * Obtiene los canales en los que debe emitirse el evento.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('servicios'),
        ];
    }

    /**
     * Nombre de transmisión personalizado.
     */
    public function broadcastAs(): string
    {
        return 'servicio.actualizado';
    }
}
