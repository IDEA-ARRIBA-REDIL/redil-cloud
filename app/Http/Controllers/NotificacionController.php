<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use Illuminate\Http\Request;

class NotificacionController extends Controller
{
    /**
     * Muestra la lista de notificaciones del usuario autenticado.
     */
    public function lista(): \Illuminate\Contracts\View\View
    {
        $configuracion = Configuracion::find(1);
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();

        return view('contenido.paginas.notificaciones.lista', compact('configuracion', 'rolActivo'));
    }

    /**
     * Muestra el panel de administración de tipos de notificaciones.
     */
    public function configuracion(): \Illuminate\Contracts\View\View
    {
        $configuracion = Configuracion::find(1);
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();

        return view('contenido.paginas.notificaciones.configuracion', compact('configuracion', 'rolActivo'));
    }
}
