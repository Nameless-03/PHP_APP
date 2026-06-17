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
        $reservas = Reserva::with(['servicio', 'cliente.usuario'])
            ->where('estado', EstadoReservaEnum::PAGADA->value)
            ->get();

        $count = 0;

        foreach ($reservas as $reserva) {
            if (Cache::has("reprogramada_por_cliente_{$reserva->id}")) {
                $limiteCancelacionHoras = $reserva->servicio->limite_cancelacion_horas ?? 10;
                $limiteConfirmacion = Carbon::parse($reserva->fecha_hora_inicio)->subHours($limiteCancelacionHoras);

                if (Carbon::now()->greaterThanOrEqualTo($limiteConfirmacion)) {
                    try {
                        $this->info("Cancelando automáticamente reserva #{$reserva->id} reprogramada y no confirmada a tiempo.");
                        
                        // Cancelar la reserva cambiando su estado a CANCELADA
                        $reservaService->cambiarEstado($reserva, EstadoReservaEnum::CANCELADA);
                        
                        $count++;
                    } catch (\Exception $e) {
                        $this->error("Error cancelando reserva #{$reserva->id}: " . $e->getMessage());
                        Log::error("Error cancelando reserva reprogramada #{$reserva->id} automáticamente: " . $e->getMessage());
                    }
                }
            }
        }

        $this->info("Se cancelaron automáticamente {$count} reservas reprogramadas no confirmadas a tiempo.");
    }
}
