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

$domains = config('tenancy.central_domains');

if (app()->environment('local')) {
    $domains[] = env('CENTRAL_DOMAIN', 'redilcloud').':8000';
    $domains[] = '127.0.0.1:8000';
    $domains[] = 'localhost:8000';
}

$domains = array_values(array_unique($domains));



foreach ($domains as $index => $domain) {
    // Solo el primer dominio conservará el nombre principal ('central.'), los demás tendrán un sufijo para evitar choque en la caché.
    $nombreGrupo = $index === 0 ? 'central.' : 'central' . $index . '.';
    
    Route::domain($domain)->name($nombreGrupo)->group(function () {

        Route::get('/', function () {
            return view('landing');
        })->name('landing');


        // Registro de clientes / onboarding
        Route::get('/registro', App\Livewire\Central\RegistroIglesia::class)->name('registro');

        // Autenticación de Super Admins
        Route::get('/admin/login', App\Livewire\Central\Auth\AdminLogin::class)->name('admin.login');

        // Panel Privado de Super Admins (Protegido por guard admin y suspensión)
          Route::middleware(['auth:admin', \App\Http\Middleware\RevisarSuspensionAdmin::class])->group(function () {
            Route::get('/admin/dashboard', App\Livewire\Central\AdminDashboard::class)->name('admin.dashboard');
            Route::get('/admin/super-admins', App\Livewire\Central\GestionarSuperAdmins::class)->name('admin.super-admins');


            Route::post('/admin/logout', function () {

                \Illuminate\Support\Facades\Auth::guard('admin')->logout();
                request()->session()->invalidate();
                request()->session()->regenerateToken();
                return redirect()->route('central.admin.login');
            })->name('admin.logout');
        });

    });
}
