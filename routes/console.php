<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;

use App\Console\Commands\VerificarPagosPendientes;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Registrar la Sonda de ZonaPagos para que esté disponible en la consola Artisan
Artisan::starting(function ($artisan) {
    $artisan->resolve(VerificarPagosPendientes::class);
});

Schedule::command('reportes:notificar-pendientes')->everyThirtyMinutes();
