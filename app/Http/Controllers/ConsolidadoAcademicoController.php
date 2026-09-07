<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class ConsolidadoAcademicoController extends Controller
{
    /**
     * Muestra la vista del Dashboard de Consolidado Académico.
     */
    public function index(): View
    {
        return view('contenido.paginas.escuelas.consolidado-academico');
    }
}
