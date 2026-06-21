<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Models\Reserva;

class RecordatorioTurnoNotification extends Notification implements ShouldQueue
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
        return ['database', 'broadcast'];
    }

    /**
     * Obtiene la representación en arreglo de la notificación.
     */
    public function toArray(object $notifiable): array
    {
        $fecha = \Carbon\Carbon::parse($this->reserva->fecha_hora_inicio)->format('d/m/Y H:i');
        $modalidad = $this->reserva->servicio->modalidad ?? 'presencial';
        
        $titulo = 'Recordatorio de Turno';
        $mensaje = "Recuerda que tienes un turno para el servicio '{$this->reserva->servicio->nombre}' el {$fecha}.";
        $tipo = 'recordatorio';
        
        // Si el destinatario es un profesional y el turno es presencial
        if (isset($notifiable->role) && $notifiable->role === 'profesional' && $modalidad === 'presencial') {
            $titulo = 'Turno Presencial por Iniciar';
            $mensaje = "Tu turno presencial para '{$this->reserva->servicio->nombre}' está por comenzar. Recuerda dar inicio o registrar si no asistió.";
            $tipo = 'presencial_inminente';
        }
        
        return [
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'reserva_id' => $this->reserva->id,
            'tipo' => $tipo,
            'color' => $modalidad === 'presencial' ? 'orange' : 'primary'
        ];
    }
}
