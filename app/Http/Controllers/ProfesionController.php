<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class ProfesionController extends Controller
{
    /**
     * Muestra la vista para gestionar las profesiones del sistema.
     */
    public function index(): View
    {
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
        if ($rolActivo) {
            $rolActivo->verificacionDelPermiso('configuraciones.subitem_profesiones');
        }

        return view('contenido.paginas.profesiones.index');
    }
}
