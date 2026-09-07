<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class InformeEstadoMateriaNivelesController extends Controller
{
    /**
     * Muestra la vista del Informe de Estado por Materia o Niveles.
     */
    public function index(): View
    {
        return view('contenido.paginas.escuelas.informe-estado-materia-niveles');
    }
}
