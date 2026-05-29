<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PagoNotificacion extends Notification implements ShouldQueue
{
    use Queueable;

    public string $titulo;
    public string $mensaje;
    public string $tipo;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $titulo, string $mensaje, string $tipo = 'otro')
    {
        $this->titulo = $titulo;
        $this->mensaje = $mensaje;
        $this->tipo = $tipo;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
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
                    ->line('Gracias por confiar en nuestra plataforma.');
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
            'tipo' => $this->tipo,
        ];
    }
}
