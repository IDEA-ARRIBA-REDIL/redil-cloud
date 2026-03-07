<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificarUsuario
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
      $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();

      $idUsuario = isset($request->usuario->id) ? $request->usuario->id : $request->usuario;
      $validado = false;

      if ($rolActivo->hasPermissionTo('personas.lista_asistentes_solo_ministerio')) {
        $personas = auth()
          ->user()
          ->discipulos('todos');

        $personas = $personas->where('id',$idUsuario);

        // Si el ID solicitado es el mismo que el del usuario logueado, se valida.
        if ($idUsuario == auth()->id()) {
            $validado = true;
        }else{
          $validado = $personas->count()>=1 ? true : false;
        }

      }

      if ($rolActivo->hasPermissionTo('personas.lista_asistentes_todos')) {
        $validado = true;
      }

      // Nueva validación para autogestión de perfil
      if ($rolActivo->hasPermissionTo('personas.perfil.principal_autogestion') && $idUsuario == auth()->id()) {
          $validado = true;
      }

      return $validado ? $next($request) : redirect()->route('pagina-no-encontrada');
    }
}
