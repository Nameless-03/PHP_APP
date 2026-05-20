<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Reserva;

class ReservaConfirmadaNotification extends Notification
{
    use Queueable;

    public $reserva;

    /**
     * Create a new notification instance.
     */
    public function __construct(Reserva $reserva)
    {
        $this->reserva = $reserva;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database']; // Guardamos en bd para la campana de notificaciones
    }

    /**
     * Get the array representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        $fecha = \Carbon\Carbon::parse($this->reserva->fecha_hora_inicio)->format('d/m/Y H:i');
        
        return [
            'titulo' => 'Reserva Confirmada',
            'mensaje' => "Tu reserva para el servicio '{$this->reserva->servicio->nombre}' el {$fecha} ha sido confirmada.",
            'reserva_id' => $this->reserva->id,
            'tipo' => 'confirmacion',
            'color' => 'success'
        ];
    }
}
