<?php

namespace App\Jobs;

use App\Models\Pago;
use App\Enums\EstadoPagoEnum;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcesarPagoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $pago;
    public bool $simularError;

    /**
     * Create a new job instance.
     */
    public function __construct(Pago $pago, bool $simularError = false)
    {
        $this->pago = $pago;
        $this->simularError = $simularError;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        \Illuminate\Support\Facades\Log::info("Iniciando procesamiento de pago asíncrono para el pago ID: {$this->pago->id}");

        // Simulamos comunicación con pasarela de pago (ej. Stripe o PayPal)
        sleep(2);

        if ($this->simularError) {
            // Caso de fallo/rechazo del pago
            $this->pago->update([
                'estado' => EstadoPagoEnum::FALLIDO,
                'referencia_externa' => null,
            ]);

            \Illuminate\Support\Facades\Log::warning("Pago ID: {$this->pago->id} fallido/rechazado.");

            // Si está asociado a una reserva, notificar el error
            if ($this->pago->id_reserva) {
                $reserva = \App\Models\Reserva::with('servicio')->find($this->pago->id_reserva);
                if ($reserva) {
                    $cliente = $reserva->cliente->usuario;
                    $mensaje = "El pago de tu reserva para '{$reserva->servicio->nombre}' ha sido rechazado. Por favor, reintenta el pago o cancela la operación.";
                    
                    // Laravel Database & Mail Notification
                    $cliente->notify(new \App\Notifications\PagoNotificacion(
                        "Pago de Reserva Rechazado",
                        $mensaje,
                        'cancelada'
                    ));

                    // Custom notificaciones table log
                    \App\Models\Notificacion::create([
                        'titulo' => 'Pago de Reserva Rechazado',
                        'mensaje' => $mensaje,
                        'tipo' => \App\Enums\TipoNotificacionEnum::OTRO,
                        'id_usuario' => $cliente->id,
                    ]);
                }
            }

            // Si está asociado a un paquete, notificar el error
            if ($this->pago->id_compra) {
                $compra = \App\Models\CompraPaquete::with('paquete')->find($this->pago->id_compra);
                if ($compra) {
                    $cliente = $compra->cliente->usuario;
                    $mensaje = "El pago para la compra del paquete '{$compra->paquete->nombre}' ha sido rechazado. Por favor, reintenta el pago o cancela la compra.";
                    
                    // Laravel Database & Mail Notification
                    $cliente->notify(new \App\Notifications\PagoNotificacion(
                        "Pago de Paquete Rechazado",
                        $mensaje,
                        'cancelada'
                    ));

                    // Custom notificaciones table log
                    \App\Models\Notificacion::create([
                        'titulo' => 'Pago de Paquete Rechazado',
                        'mensaje' => $mensaje,
                        'tipo' => \App\Enums\TipoNotificacionEnum::OTRO,
                        'id_usuario' => $cliente->id,
                    ]);
                }
            }
        } else {
            // Caso de éxito
            $this->pago->update([
                'estado' => EstadoPagoEnum::COMPLETADO,
                'referencia_externa' => 'TXN_' . strtoupper(uniqid()),
            ]);

            \Illuminate\Support\Facades\Log::info("Pago ID: {$this->pago->id} completado con éxito.");

            // Si es una reserva, actualizar a PAGADA y notificar
            if ($this->pago->id_reserva) {
                $reserva = \App\Models\Reserva::with('servicio')->find($this->pago->id_reserva);
                if ($reserva) {
                    $reserva->update([
                        'estado' => \App\Enums\EstadoReservaEnum::PAGADA,
                    ]);

                    $cliente = $reserva->cliente->usuario;
                    $mensaje = "El pago de tu reserva para '{$reserva->servicio->nombre}' fue aprobado con éxito. ¡Tu turno está pagado!";
                    
                    // Laravel Database & Mail Notification
                    $cliente->notify(new \App\Notifications\PagoNotificacion(
                        "Pago de Reserva Aprobado",
                        $mensaje,
                        'confirmacion'
                    ));

                    // Custom notificaciones table log
                    \App\Models\Notificacion::create([
                        'titulo' => 'Pago de Reserva Aprobado',
                        'mensaje' => $mensaje,
                        'tipo' => \App\Enums\TipoNotificacionEnum::CONFIRMACION,
                        'id_usuario' => $cliente->id,
                    ]);
                }
            }

            // Si es un paquete, habilitar para utilización y registrar sesiones disponibles
            if ($this->pago->id_compra) {
                $compra = \App\Models\CompraPaquete::with('paquete')->find($this->pago->id_compra);
                if ($compra) {
                    $compra->update([
                        'estado' => 'activo',
                        'sesiones_disponibles' => $compra->paquete->cantidad_sesiones,
                    ]);

                    $cliente = $compra->cliente->usuario;
                    $mensaje = "Tu pago para el paquete '{$compra->paquete->nombre}' fue aprobado. El paquete está habilitado con {$compra->paquete->cantidad_sesiones} sesiones.";
                    
                    // Laravel Database & Mail Notification
                    $cliente->notify(new \App\Notifications\PagoNotificacion(
                        "Compra de Paquete Exitosa",
                        $mensaje,
                        'confirmacion'
                    ));

                    // Custom notificaciones table log
                    \App\Models\Notificacion::create([
                        'titulo' => 'Compra de Paquete Exitosa',
                        'mensaje' => $mensaje,
                        'tipo' => \App\Enums\TipoNotificacionEnum::CONFIRMACION,
                        'id_usuario' => $cliente->id,
                    ]);
                }
            }
        }
    }
}
