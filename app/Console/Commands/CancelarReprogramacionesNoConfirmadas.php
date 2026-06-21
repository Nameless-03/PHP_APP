<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reserva;
use App\Enums\EstadoReservaEnum;
use App\Services\ReservaService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CancelarReprogramacionesNoConfirmadas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'turnos:cancelar-no-confirmados';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cancela automáticamente las reservas reprogramadas por el cliente si el profesional no las confirma antes del límite de cancelación';

    /**
     * Execute the console command.
     */
    public function handle(ReservaService $reservaService): void
    {
        // 1. Cancelar reservas reprogramadas por el cliente no confirmadas a tiempo
        $reservasPagadas = Reserva::with(['servicio', 'cliente.usuario'])
            ->where('estado', EstadoReservaEnum::PAGADA->value)
            ->get();

        $countReprog = 0;
        foreach ($reservasPagadas as $reserva) {
            if (Cache::has("reprogramada_por_cliente_{$reserva->id}")) {
                $limiteCancelacionHoras = $reserva->servicio->limite_cancelacion_horas ?? 10;
                $limiteConfirmacion = Carbon::parse($reserva->fecha_hora_inicio)->subHours($limiteCancelacionHoras);

                if (Carbon::now()->greaterThanOrEqualTo($limiteConfirmacion)) {
                    try {
                        $this->info("Cancelando automáticamente reserva #{$reserva->id} reprogramada y no confirmada a tiempo.");
                        $reservaService->cambiarEstado($reserva, EstadoReservaEnum::CANCELADA);
                        $countReprog++;
                    } catch (\Exception $e) {
                        $this->error("Error cancelando reserva #{$reserva->id}: " . $e->getMessage());
                        Log::error("Error cancelando reserva reprogramada #{$reserva->id} automáticamente: " . $e->getMessage());
                    }
                }
            }
        }

        // 2. Cancelar reservas pendientes de pago que alcanzaron el tiempo límite de cancelación sin confirmarse
        $reservasPendientes = Reserva::with(['servicio', 'cliente.usuario'])
            ->where('estado', EstadoReservaEnum::PENDIENTE->value)
            ->get();

        $countPendientes = 0;
        foreach ($reservasPendientes as $reserva) {
            $limiteCancelacionHoras = $reserva->servicio->limite_cancelacion_horas ?? 10;
            $limiteConfirmacion = Carbon::parse($reserva->fecha_hora_inicio)->subHours($limiteCancelacionHoras);

            if (Carbon::now()->greaterThanOrEqualTo($limiteConfirmacion)) {
                try {
                    $this->info("Cancelando automáticamente reserva pendiente #{$reserva->id} no pagada/confirmada a tiempo.");
                    $reservaService->cambiarEstado($reserva, EstadoReservaEnum::CANCELADA);
                    $countPendientes++;
                } catch (\Exception $e) {
                    $this->error("Error cancelando reserva pendiente #{$reserva->id}: " . $e->getMessage());
                    Log::error("Error cancelando reserva pendiente #{$reserva->id} automáticamente: " . $e->getMessage());
                }
            }
        }

        $this->info("Se cancelaron automáticamente {$countReprog} reservas reprogramadas y {$countPendientes} reservas pendientes no confirmadas a tiempo.");
    }
}
