<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('turnos:recordatorios')->hourly();
Schedule::command('videollamada:recordatorios')->everyMinute();
Schedule::command('turnos:cancelar-no-confirmados')->everyMinute();
Schedule::command('turnos:finalizar-expirados')->everyMinute();
