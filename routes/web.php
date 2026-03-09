<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Central Web Routes
|--------------------------------------------------------------------------
|
| Rutas centrales para el dominio principal (redilcloud.com).
| Se registran bajo cada dominio central definido en config/tenancy.php
| para que el middleware de tenancy las reconozca y no las bloquee.
|
*/

foreach (config('tenancy.central_domains') as $domain) {
    Route::domain($domain)->group(function () {

        Route::get('/', function () {
            return view('landing');
        });

        // Puedes añadir aquí más rutas centrales:
        // - Landing page pública
        // - Panel del Super Admin para crear tenants
        // - etc.

    });
}
