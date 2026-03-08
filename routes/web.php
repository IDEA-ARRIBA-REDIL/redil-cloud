<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Central Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your central application.
| These routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group.
|
*/

Route::get('/', function () {
    return '<h1>Bienvenido a REDIL Cloud</h1><p>Esta es la página central. Accede a tu iglesia vía subdominio (ej: iglesia1.redilcloud:8000)</p>';
});

// Puedes añadir aquí rutas para que el Super Admin cree nuevos tenants
