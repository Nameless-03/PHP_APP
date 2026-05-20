<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Reserva;

class ReservaModificadaNotification extends Notification
{
    use Queueable;

    public $reserva;
    public $accion; // 'cancelada' o 'reprogramada'

    /**
     * Create a new notification instance.
     */
    public function __construct(Reserva $reserva, string $accion)
    {
        $this->reserva = $reserva;
        $this->accion = $accion;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toDatabase(object $notifiable): array
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
