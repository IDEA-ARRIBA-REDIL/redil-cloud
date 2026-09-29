<?php

namespace App\Http\Controllers;

class GamificacionController extends Controller
{
    /**
     * Muestra la vista principal del sistema de gamificación del usuario.
     */
    public function index()
    {
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
        $rolActivo->verificacionDelPermiso('gamificacion.habilitar_gamificacion');

        return view('contenido.paginas.gamificacion.index');
    }

    /**
     * Muestra la vista de configuración administrativa de gamificación (Reglas e Insignias).
     */
    public function configuracion()
    {
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
        $rolActivo->verificacionDelPermiso('configuraciones.configuracion_gamificacion');

        return view('contenido.paginas.gamificacion.configuracion');
    }

    /**
     * Muestra la vista de administración de la Tienda de Canjes (Premios y Solicitudes).
     */
    public function tienda()
    {
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
        $rolActivo->verificacionDelPermiso('configuraciones.tienda_gamificacion');

        return view('contenido.paginas.gamificacion.tienda');
    }
}
