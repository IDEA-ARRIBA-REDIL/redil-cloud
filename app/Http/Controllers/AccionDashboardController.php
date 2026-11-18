<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class AccionDashboardController extends Controller
{
    /**
     * Muestra la vista administrativa para gestionar los enlaces de "¿Qué deseas hacer hoy?" del Dashboard.
     */
    public function index(): View
    {
        $rolActivo = auth()->user()?->roles()->wherePivot('activo', true)->first();
        abort_if($rolActivo === null, 403, 'No tienes los permisos necesarios.');
        $rolActivo->verificacionDelPermiso('configuraciones.subitem_acciones_dashboard');

        return view('contenido.paginas.acciones-dashboard.index');
    }
}
