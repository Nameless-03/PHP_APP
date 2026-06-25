<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaqueteActualizado implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $userId;
    public string $tipo; // 'compra', 'uso', 'cancelacion', 'eliminacion'
    public array $data;

    /**
     * Crea una nueva instancia del evento.
     */
    public function __construct(int $userId, string $tipo, array $data)
    {
        $this->userId = $userId;
        $this->tipo = $tipo;
        $this->data = $data;
    }

    /**
     * Obtiene los canales en los que debe emitirse el evento.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("App.Models.Usuario.{$this->userId}"),
        ];
    }

    /**
     * Nombre de transmisión personalizado.
     */
    public function broadcastAs(): string
    {
        return 'paquete.actualizado';
    }
}
