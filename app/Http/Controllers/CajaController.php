<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CajaController extends Controller
{
    //
    public function gestionar($tipo = 'todos')
    {
        // El único trabajo de este método es retornar la "vista anfitriona".
        // Le pasamos la variable 'tipo' para que esta vista, a su vez,
        // se la pase al componente Livewire.
        return view('contenido.paginas.cajas.gestionar', [
            'tipo' => $tipo
        ]);
    }
}
