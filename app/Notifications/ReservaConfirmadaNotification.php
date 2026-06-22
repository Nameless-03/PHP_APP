<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Reserva;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class ReservaConfirmadaNotification extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    public $reserva;

    /**
     * Crea una nueva instancia de la notificación.
     */
    public function __construct(Reserva $reserva)
    {
        $this->reserva = $reserva;
    }

    /**
     * Obtiene los canales de entrega de la notificación.
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'broadcast']; // Guardamos en bd y emitimos por ws, enviamos por email
    }

    /**
     * Obtiene la representación de correo de la notificación.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $fecha = \Carbon\Carbon::parse($this->reserva->fecha_hora_inicio)->format('d/m/Y H:i');
        
        return (new MailMessage)
                    ->subject('Reserva Confirmada')
                    ->greeting('Hola ' . $notifiable->nombre . '!')
                    ->line("Tu reserva para el servicio '{$this->reserva->servicio->nombre}' el {$fecha} ha sido confirmada.")
                    ->action('Ver Reserva', url('/reservas/' . $this->reserva->id))
                    ->line('Gracias por usar nuestra plataforma.');
    }

    /**
     * Obtiene la representación en arreglo de la notificación.
     */
    public function toArray(object $notifiable): array
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
