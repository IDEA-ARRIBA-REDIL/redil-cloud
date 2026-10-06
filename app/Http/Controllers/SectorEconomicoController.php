<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class SectorEconomicoController extends Controller
{
    /**
     * Muestra la vista para gestionar los sectores económicos del sistema.
     */
    public function index(): View
    {
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
        if ($rolActivo) {
            $rolActivo->verificacionDelPermiso('configuraciones.subitem_sectores_economicos');
        }

        return view('contenido.paginas.sectores-economicos.index');
    }
}
