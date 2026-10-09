<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PlantillaInformeController extends Controller
{
    /**
     * Muestra la vista de gestión de plantillas de informes personalizados.
     */
    public function index(): View
    {
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
        if ($rolActivo) {
            $rolActivo->verificacionDelPermiso('configuraciones.subitem_plantillas_informes');
        }

        return view('contenido.paginas.configuracion.plantillas-informes.index');
    }
}
