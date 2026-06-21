<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Models\Reserva;

class ReservaModificadaNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $reserva;
    public $accion; // 'cancelada' o 'reprogramada'

    /**
     * Crea una nueva instancia de la notificación.
     */
    public function __construct(Reserva $reserva, string $accion)
    {
        $this->reserva = $reserva;
        $this->accion = $accion;
    }

    /**
     * Obtiene los canales de entrega de la notificación.
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Obtiene la representación en arreglo de la notificación.
     */
    public function toArray(object $notifiable): array
    {
        $fecha = \Carbon\Carbon::parse($this->reserva->fecha_hora_inicio)->format('d/m/Y H:i');
        
        $titulo = $this->accion === 'cancelada' ? 'Reserva Cancelada' : 'Reserva Reprogramada';
        $color = $this->accion === 'cancelada' ? 'error' : 'info';
        
        return [
            'titulo' => $titulo,
            'mensaje' => "El turno para el servicio '{$this->reserva->servicio->nombre}' el {$fecha} ha sido {$this->accion}.",
            'reserva_id' => $this->reserva->id,
            'tipo' => $this->accion,
            'color' => $color
        ];
    }
}
