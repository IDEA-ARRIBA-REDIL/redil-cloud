<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class HomologacionController extends Controller
{
    public function index(): View
    {
        return view('contenido.paginas.escuelas.homologaciones.gestionar-homologaciones');
    }
}
