<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class HomologacionController extends Controller
{
    public function index(): View
    {
        return view('contenido.paginas.escuelas.homologaciones.gestionar-homologaciones');
    }

    public function masivas(): View
    {
        return view('contenido.paginas.escuelas.homologaciones.homologaciones-masivas');
    }
}
