<?php

namespace App\Http\Middleware;

use App\Models\ReporteReunion;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificarReporteReunion
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

        // Obtenemos el parámetro de la ruta (puede venir con diferentes nombres en los parámetros de ruta)
        $parametro = $request->route('reporteReunion')
            ?? $request->route('reporte')
            ?? $request->route('reporteReunionId');

        if (! $parametro) {
            return redirect()->route('pagina-no-encontrada');
        }

        // Si es una instancia del modelo, la usamos; de lo contrario, buscamos en la base de datos
        $reporteReunion = ($parametro instanceof ReporteReunion)
            ? $parametro
            : ReporteReunion::find($parametro);

        if (! $reporteReunion) {
            return redirect()->route('pagina-no-encontrada');
        }

        $reunion = $reporteReunion->reunion()->withTrashed()->first();
        if (! $reunion) {
            return redirect()->route('pagina-no-encontrada');
        }


        $validado = false;

        // Si tiene permiso para ver todos los reportes de reunión
        if ($rolActivo->hasPermissionTo('reporte_reuniones.lista_reportes_reunion_todos')) {
            $validado = true;
        } else {
            // Si el rol tiene definido lista_reuniones_sede_id, solo puede ver reportes de esa sede
            if ($rolActivo->lista_reuniones_sede_id) {
                if ($reunion->sede_id == $rolActivo->lista_reuniones_sede_id) {
                    $validado = true;
                }
            } else {
                // De lo contrario, se valida por las sedes encargadas del usuario y sedes de sus grupos encargados
                $sedesEncargadasArray = $user->sedesEncargadas('array');
                $sedeDeLosGruposArray = $user->gruposEncargados()->select('grupos.sede_id')->pluck('grupos.sede_id')->toArray();
                $sedesTotalesArray = array_merge($sedesEncargadasArray, $sedeDeLosGruposArray);
                $sedesTotalesArray = array_filter($sedesTotalesArray);
                $sedesTotalesArray = array_unique($sedesTotalesArray);

                $otrasSedes = $reunion->sedes()->pluck('sedes_id')->toArray();
                $interseccionArray = [];
                if (count($otrasSedes) > 0) {
                    $interseccionArray = array_intersect($sedesTotalesArray, $otrasSedes);
                }

                if (in_array($reunion->sede_id, $sedesTotalesArray) || count($interseccionArray) > 0) {
                    $validado = true;
                }
            }
        }

        return $validado ? $next($request) : redirect()->route('pagina-no-encontrada')->with('error', 'No tienes permiso para ver este reporte.');
    }
}
