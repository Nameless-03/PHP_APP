<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Reserva;

class ReservaConfirmadaNotification extends Notification implements ShouldQueue
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
        return ['mail', 'database', 'broadcast']; // Guardamos en bd y emitimos por ws, enviamos por email
    }

    /**
     * Get the mail representation of the notification.
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
     * Get the array representation of the notification.
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
