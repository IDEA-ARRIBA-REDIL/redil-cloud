<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class OcupacionController extends Controller
{
    /**
     * Muestra la vista para gestionar las ocupaciones del sistema.
     */
    public function index(): View
    {
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
        if ($rolActivo) {
            $rolActivo->verificacionDelPermiso('configuraciones.subitem_ocupaciones');
        }

        return view('contenido.paginas.ocupaciones.index');
    }
}
