<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public $token;

    /**
     * Crea una nueva instancia de la notificación.
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Obtiene los canales de entrega de la notificación.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Obtiene la representación de correo de la notificación.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $frontendUrl = config('services.frontend_url', 'http://localhost:5173');
        $url = $frontendUrl . '/reset-password?token=' . $this->token . '&email=' . urlencode($notifiable->getEmailForPasswordReset());

        return (new MailMessage)
            ->subject(Lang::get('Recuperación de Contraseña'))
            ->greeting(Lang::get('¡Hola!'))
            ->line(Lang::get('Estás recibiendo este correo porque recibimos una solicitud de restablecimiento de contraseña para tu cuenta.'))
            ->action(Lang::get('Restablecer Contraseña'), $url)
            ->line(Lang::get('Este enlace expirará en :count minutos.', ['count' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire')]))
            ->line(Lang::get('Si no solicitaste este cambio, puedes ignorar este correo.'))
            ->salutation(Lang::get('Saludos, el equipo de soporte.'));
    }

    /**
     * Obtiene la representación en arreglo de la notificación.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
