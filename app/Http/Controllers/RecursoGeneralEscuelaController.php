<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\RecursoGeneralEscuela;

class RecursoGeneralEscuelaController extends Controller
{
    public function index()
    {

        return view('contenido.paginas.escuelas.recursos-generales.index');
    }

    public function misRecursos()
    {
        // 1. Obtenemos al usuario autenticado.
        $user = Auth::user();
        $recursos = collect(); // Por defecto, una colección vacía.

        // 2. Verificamos que el usuario haya iniciado sesión.
        if ($user) {
            // 3. Obtenemos el ROL ACTIVO del usuario (según la lógica de tu app).
            $rolActivo = $user->roles()->wherePivot('activo', true)->first();

            // 4. Si tiene un rol activo, buscamos los recursos asociados a ese rol.
            if ($rolActivo) {
                $recursos = RecursoGeneralEscuela::where('visible', true)
                    ->whereHas('roles', function ($query) use ($rolActivo) {
                        $query->where('role_id', $rolActivo->id);
                    })
                    ->latest()
                    ->get();
            }
        }

        // 5. Pasamos la colección de recursos (llena o vacía) a la vista.
        return view('contenido.paginas.escuelas.recursos-generales.mis-recursos', [
            'recursos' => $recursos
        ]);
    }
}
