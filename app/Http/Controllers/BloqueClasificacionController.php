<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BloqueClasificacionController extends Controller
{
    public function index()
    {
        return view('contenido.paginas.bloques-clasificacion.index');
    }
}
