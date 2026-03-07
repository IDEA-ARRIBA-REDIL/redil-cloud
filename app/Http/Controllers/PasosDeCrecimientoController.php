<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PasosDeCrecimientoController extends Controller
{
  public function pasosDeCrecimiento()
  {
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $rolActivo->verificacionDelPermiso('configuraciones.subitem_pasos_de_crecimiento');

    return view('contenido.paginas.gestionar-pasos-de-crecimiento.gestionar-pasos-de-crecimiento');
  }

  public function crear()
  {
    return '';
  }
}
