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
     * Crea una nueva instancia de la notificación.
     */
    public function __construct(string $titulo, string $mensaje, string $tipo = 'otro')
    {
        $this->titulo = $titulo;
        $this->mensaje = $mensaje;
        $this->tipo = $tipo;
    }

    /**
     * Obtiene los canales de entrega de la notificación.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    /**
     * Obtiene la representación de correo de la notificación.
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
     * Obtiene la representación en arreglo de la notificación.
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
