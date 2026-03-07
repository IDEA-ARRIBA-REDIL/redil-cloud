<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Curso;
use Illuminate\Http\Request;

class CursoFrontController extends Controller
{
    /**
     * Muestra la vista de detalle público de un curso.
     *
     * @param string $slug
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show($slug)
    {
        // Buscar el curso por su slug, asegurarse de que esté activo (estado = 1), y cargar relaciones básicas
        $curso = Curso::with(['equipo.user', 'equipo.tipoCargo', 'aprendizajes', 'pasosRequisito', 'tareasRequisito', 'rangosEdad', 'estadosCiviles', 'tipoServicios', 'categorias'])
            ->where('slug', $slug)
            ->where('estado', 1)
            ->firstOrFail();

        // Obtener la configuración general para los logos y URLs base de imágenes
        $configuracion = \App\Models\Configuracion::first();

        return view('front.cursos.detalle', compact('curso', 'configuracion'));
    }
}
