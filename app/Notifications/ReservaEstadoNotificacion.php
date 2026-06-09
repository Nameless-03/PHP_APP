<?php

namespace App\Notifications;

use App\Models\Reserva;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Notificacion as ModeloNotificacion;

class ReservaEstadoNotificacion extends Notification implements ShouldQueue
{
    use Queueable;

    public $reserva;
    public $titulo;
    public $mensaje;

    /**
     * Create a new notification instance.
     */
    public function __construct(Reserva $reserva, string $titulo, string $mensaje)
    {
        $this->reserva = $reserva;
        $this->titulo = $titulo;
        $this->mensaje = $mensaje;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'broadcast']; // Guardamos en mail, bd y emitimos por ws
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject($this->titulo)
                    ->greeting('Hola ' . $notifiable->nombre . '!')
                    ->line($this->mensaje)
                    ->line('Detalles de la reserva:')
                    ->line('Servicio: ' . $this->reserva->servicio->nombre)
                    ->line('Fecha: ' . $this->reserva->fecha_hora_inicio->format('d/m/Y H:i'))
                    ->action('Ver Reserva', url('/reservas/' . $this->reserva->id))
                    ->line('Gracias por usar nuestra plataforma.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'titulo' => $this->titulo,
            'mensaje' => $this->mensaje,
            'reserva_id' => $this->reserva->id,
            'tipo' => 'otro',
            'color' => 'primary'
        ];
    }
}
