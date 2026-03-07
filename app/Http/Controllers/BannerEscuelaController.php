<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

use Illuminate\Http\Request;

class BannerEscuelaController extends Controller
{
    /**
     * Muestra la vista principal para gestionar los banners.
     */
    public function gestionar(): View
    {
        // Esto le dice a Laravel que busque y muestre el archivo de vista en:
        // resources/views/admin/banners/index.blade.php
        return view('contenido.paginas.escuelas.banners.gestionar');
    }
}
