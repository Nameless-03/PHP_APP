<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reserva;
use App\Enums\EstadoReservaEnum;
use App\Notifications\RecordatorioTurnoNotification;
use Carbon\Carbon;

class EnviarRecordatoriosTurnos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'turnos:recordatorios';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envía recordatorios a clientes y profesionales 24hs antes del turno';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Rango de tiempo: de ahora + 24hs hasta ahora + 24hs + 1 hora
        $inicio = Carbon::now()->addHours(24);
        $fin = Carbon::now()->addHours(25);

        $reservas = Reserva::with(['cliente.usuario', 'servicio.profesional.usuario'])
            ->where('estado', EstadoReservaEnum::CONFIRMADA->value)
            ->whereBetween('fecha_hora_inicio', [$inicio, $fin])
            ->get();

        $count = 0;
        foreach ($reservas as $reserva) {
            // Notificar al cliente
            $reserva->cliente->usuario->notify(new RecordatorioTurnoNotification($reserva));
            
            // Notificar al profesional
            $reserva->servicio->profesional->usuario->notify(new RecordatorioTurnoNotification($reserva));
            
            $count++;
        }

        $this->info("Se enviaron $count recordatorios de turnos.");
    }
}
