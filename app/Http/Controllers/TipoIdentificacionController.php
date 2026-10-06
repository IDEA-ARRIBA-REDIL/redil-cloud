<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class TipoIdentificacionController extends Controller
{
    /**
     * Muestra la vista para gestionar los tipos de identificación.
     */
    public function index(): View
    {
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
        if ($rolActivo) {
            $rolActivo->verificacionDelPermiso('configuraciones.subitem_tipo_identificaciones');
        }

        return view('contenido.paginas.tipo-identificaciones.index');
    }
}
