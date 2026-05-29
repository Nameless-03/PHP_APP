<?php

namespace App\Services;

use App\Models\Pago;
use App\Models\Reserva;
use App\Enums\EstadoPagoEnum;
use App\Enums\EstadoReservaEnum;
use App\Jobs\ProcesarPagoJob;
use Illuminate\Support\Facades\DB;
use Exception;

use App\Services\NoSqlLoggerService;

class PagoService
{
    public function __construct(
        private NoSqlLoggerService $logger
    ) {}
    /**
     * Iniciar proceso de pago.
     */
    public function iniciarPago(array $data): Pago
    {
        return DB::transaction(function () use ($data) {
            
            // Validaciones lógicas
            if (isset($data['id_reserva'])) {
                $reserva = Reserva::findOrFail($data['id_reserva']);
                if ($reserva->estado === EstadoReservaEnum::PAGADA) {
                    throw new Exception("Esta reserva ya está pagada.");
                }
            }

            // Crear el registro del pago en estado Pendiente
            $pago = Pago::create([
                'monto' => $data['monto'],
                'metodo' => $data['metodo'],
                'estado' => EstadoPagoEnum::PENDIENTE,
                'id_reserva' => $data['id_reserva'] ?? null,
                'id_compra' => $data['id_compra'] ?? null,
            ]);

            // Enviar el trabajo a la cola
            ProcesarPagoJob::dispatch($pago);

            // Log NoSQL activity
            $this->logger->log("Inicio de pago", 'info', [
                'pago_id' => $pago->id,
                'monto' => $pago->monto,
                'metodo' => $pago->metodo->value ?? $pago->metodo
            ], auth()->check() ? auth()->id() : null);

            return $pago;
        });
    }
}
