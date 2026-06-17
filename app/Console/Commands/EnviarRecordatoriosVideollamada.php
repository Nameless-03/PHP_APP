<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reserva;
use App\Notifications\RecordatorioTurnoNotification;
use Carbon\Carbon;

class EnviarRecordatoriosVideollamada extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'videollamada:recordatorios';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envía recordatorios de videollamada 10 minutos antes del turno';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        // Ventana: reservas que comienzan entre 9 y 11 minutos desde ahora
        $desde = Carbon::now()->addMinutes(9);
        $hasta = Carbon::now()->addMinutes(11);

        $reservas = Reserva::with(['servicio.profesional.usuario', 'cliente.usuario'])
            ->whereIn('estado', ['confirmada', 'pagada'])
            ->whereHas('servicio', function ($q) {
                $q->whereIn('modalidad', ['remota', 'hibrida']);
            })
            ->whereBetween('fecha_hora_inicio', [$desde, $hasta])
            ->get()
            ->filter(function ($reserva) {
                // Excluir reservas cuyo recordatorio ya fue enviado (via caché)
                return !\Illuminate\Support\Facades\Cache::has("recordatorio_reserva_{$reserva->id}");
            });

        foreach ($reservas as $reserva) {
            try {
                // Notificar al cliente
                if ($reserva->cliente?->usuario) {
                    $reserva->cliente->usuario->notify(new RecordatorioTurnoNotification($reserva));
                }

                // Notificar al profesional
                if ($reserva->servicio?->profesional?->usuario) {
                    $reserva->servicio->profesional->usuario->notify(new RecordatorioTurnoNotification($reserva));
                }

                // Marcar como enviado en caché para evitar reenvíos
                \Illuminate\Support\Facades\Cache::put(
                    "recordatorio_reserva_{$reserva->id}",
                    true,
                    now()->addHours(2)
                );

                $this->info("Recordatorio enviado para reserva #{$reserva->id}");
            } catch (\Exception $e) {
                $this->error("Error en reserva #{$reserva->id}: " . $e->getMessage());
                \Illuminate\Support\Facades\Log::error("Error enviando recordatorio reserva #{$reserva->id}: " . $e->getMessage());
            }
        }

        $this->info("Total recordatorios enviados: {$reservas->count()}");
    }
}
