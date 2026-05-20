<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Reserva;

class RecordatorioTurnoNotification extends Notification
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
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        $fecha = \Carbon\Carbon::parse($this->reserva->fecha_hora_inicio)->format('d/m/Y H:i');
        
        return [
            'titulo' => 'Recordatorio de Turno',
            'mensaje' => "Recuerda que tienes un turno para el servicio '{$this->reserva->servicio->nombre}' el {$fecha}.",
            'reserva_id' => $this->reserva->id,
            'tipo' => 'recordatorio',
            'color' => 'primary'
        ];
    }
}
