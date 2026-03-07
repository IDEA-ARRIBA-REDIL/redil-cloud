<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();


// 2. Aquí se programa la ejecución de tu comando de la sonda.
//    Laravel ejecutará este comando cada minuto.
// Se añade el método sendOutputTo() para capturar cualquier error fatal.
/*
Schedule::command('pagos:verificar-zonapagos')
    ->everyMinute();*/

Schedule::command('reportes:notificar-pendientes')->everyThirtyMinutes();
