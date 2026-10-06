<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class EstadoCivilController extends Controller
{
    /**
     * Muestra la vista para gestionar los estados civiles del sistema.
     */
    public function index(): View
    {
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
        if ($rolActivo) {
            $rolActivo->verificacionDelPermiso('configuraciones.subitem_estados_civiles');
        }

        return view('contenido.paginas.estados-civiles.index');
    }
}
