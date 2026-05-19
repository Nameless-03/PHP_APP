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

    /**
     * Create a new job instance.
     */
    public function __construct(Pago $pago)
    {
        $this->pago = $pago;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("Iniciando procesamiento de pago asíncrono para el pago ID: {$this->pago->id}");

        // Simulamos comunicación con pasarela de pago (ej. Stripe o PayPal)
        sleep(2);

        // Simulamos que el pago fue exitoso
        $this->pago->update([
            'estado' => EstadoPagoEnum::COMPLETADO,
            'referencia_externa' => 'TXN_' . strtoupper(uniqid()),
        ]);

        Log::info("Pago ID: {$this->pago->id} completado con éxito.");

        // Podríamos disparar un evento PagoCompletado aquí
    }
}
