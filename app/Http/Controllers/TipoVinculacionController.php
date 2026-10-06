<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class TipoVinculacionController extends Controller
{
    /**
     * Muestra la vista para gestionar los tipos de vinculación del sistema.
     */
    public function index(): View
    {
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
        if ($rolActivo) {
            $rolActivo->verificacionDelPermiso('configuraciones.subitem_tipo_vinculaciones');
        }

        return view('contenido.paginas.tipo-vinculaciones.index');
    }
}
