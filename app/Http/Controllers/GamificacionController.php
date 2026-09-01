<?php

namespace App\Http\Controllers;

use App\Models\Insignia;
use App\Models\InsigniaUser;
use App\Models\ReglaGamificacion;
use Illuminate\Support\Facades\Auth;

class GamificacionController extends Controller
{
    /**
     * Muestra la vista principal del sistema de gamificación del usuario.
     */
    public function index()
    {
        $usuario = Auth::user();

        // Cargar todas las insignias del catálogo ordenadas
        $insignias = Insignia::orderBy('orden', 'asc')->get();

        // Obtener los registros de progreso del usuario autenticado indexados por insignia_id
        $progresos = InsigniaUser::where('user_id', $usuario->id)
            ->get()
            ->keyBy('insignia_id');

        // Cargar reglas de gamificación tipo 'meta' para obtener la meta_cantidad de cada insignia
        $reglasMeta = ReglaGamificacion::where('frecuencia', 'meta')
            ->whereNotNull('insignia_id')
            ->get()
            ->keyBy('insignia_id');

        return view('contenido.paginas.gamificacion.index', compact('usuario', 'insignias', 'progresos', 'reglasMeta'));
    }
}
