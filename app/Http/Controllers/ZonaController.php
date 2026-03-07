<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class ZonaController extends Controller
{
    public function gestionar(): View
    {
      $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
      $rolActivo->verificacionDelPermiso('configuraciones.subitem_zonas');

      return view('contenido.paginas.zona.gestionar-zonas');
    }
}
