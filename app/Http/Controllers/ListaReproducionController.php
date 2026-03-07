<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ListaReproducionController extends Controller
{

  public function listar()
  {
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $rolActivo->verificacionDelPermiso('configuraciones.subitem_lista_de_reproduccion');
    return view('contenido.paginas.lista-reproduccion.listar');
  }

}
