<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reserva;
use App\Enums\EstadoReservaEnum;
use App\Services\ReservaService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class FinalizarReservasExpiradas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'turnos:finalizar-expirados';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Finaliza automáticamente las reservas cuya hora de fin más 5 minutos de margen ha pasado';

    /**
     * Execute the console command.
     */
    public function handle(ReservaService $reservaService): void
    {
        // Se da un margen de 5 minutos antes de finalizar automáticamente
        $marginTime = Carbon::now()->subMinutes(5);

        // Obtener reservas activas cuya hora de finalización (con el margen de 5 min) ya pasó
        $reservas = Reserva::whereIn('estado', [
                EstadoReservaEnum::EN_CURSO->value,
                EstadoReservaEnum::CONFIRMADA->value,
                EstadoReservaEnum::PAGADA->value,
            ])
            ->where('fecha_hora_fin', '<=', $marginTime)
            ->get();

        $count = 0;

        foreach ($reservas as $reserva) {
            try {
                $this->info("Finalizando automáticamente reserva #{$reserva->id} por expiración de tiempo (con margen de 5 min).");
                
                // Finalizar la reserva cambiando su estado a FINALIZADA
                $reservaService->cambiarEstado($reserva, EstadoReservaEnum::FINALIZADA);
                
                $count++;
            } catch (\Exception $e) {
                $this->error("Error al finalizar reserva #{$reserva->id}: " . $e->getMessage());
                Log::error("Error al finalizar reserva expirada #{$reserva->id} automáticamente: " . $e->getMessage());
            }
        }

        $this->info("Se finalizaron automáticamente {$count} reservas expiradas.");
    }
}
