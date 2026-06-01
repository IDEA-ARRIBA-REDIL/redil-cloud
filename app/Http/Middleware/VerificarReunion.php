<?php

namespace App\Http\Middleware;

use App\Models\Reunion;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificarReunion
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        if (! $user) {
            return redirect()->route('pagina-no-encontrada');
        }

        $rolActivo = $user->roles()->wherePivot('activo', true)->first();
        if (! $rolActivo) {
            return redirect()->route('pagina-no-encontrada');
        }

        // Obtenemos el parámetro de la ruta
        $parametroReunion = $request->route('reunion');
        if (! $parametroReunion) {
            return redirect()->route('pagina-no-encontrada');
        }

        // Verificamos: Si es un objeto (Modelo) lo usamos, si no, lo buscamos en la base de datos (con SoftDeletes por si acaso)
        $reunion = ($parametroReunion instanceof Reunion) ? $parametroReunion : Reunion::withTrashed()->find($parametroReunion);

        if (! $reunion) {
            return redirect()->route('pagina-no-encontrada');
        }

        $validado = false;

        // Si tiene permiso para ver todas las reuniones
        if ($rolActivo->hasPermissionTo('reuniones.lista_reuniones_todas')) {
            $validado = true;
        } else {
            // Si no, verificamos si la sede de la reunión está entre las sedes encargadas del usuario
            $sedesEncargadasArray = $user->sedesEncargadas('array'); // En esta funcion hay una parte donde pregunta si solo ministerio
            if (! empty($sedesEncargadasArray) && in_array($reunion->sede_id, $sedesEncargadasArray)) {
                $validado = true;
            }
        }

        
    }
}
 