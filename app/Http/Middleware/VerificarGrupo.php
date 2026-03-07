<?php

namespace App\Http\Middleware;

use App\Models\Grupo;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class VerificarGrupo
{


  /**
   * Handle an incoming request.
   *
   * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
   */
  public function handle(Request $request, Closure $next): ?Response
  {

    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    
    // Obtenemos el parámetro de la ruta
    $parametroGrupo = $request->route('grupo');

    // Verificamos: Si es un objeto (Modelo) sacamos el ID, si no, usamos el valor directo
    $grupoId = ($parametroGrupo instanceof Grupo) ? $parametroGrupo->id : $parametroGrupo;

    

    $validado = false;

    if ($rolActivo->hasPermissionTo('grupos.lista_grupos_todos') || isset(auth()->user()->iglesiaEncargada()->first()->id)) {
      $validado = true;
    }

    if ($rolActivo->hasPermissionTo('grupos.lista_grupos_solo_ministerio')) {
      $grupos = auth()->user()->gruposMinisterio()->leftJoin('encargados_grupo', 'grupos.id', '=', 'encargados_grupo.grupo_id')
        ->leftJoin('users', 'users.id', '=', 'encargados_grupo.user_id')
        ->select('grupos.id')
        ->get()
        ->unique('id');
      $grupos = $grupos->where('id', $grupoId);
      $validado = $grupos->count() >= 1 ? true : false;
    }

    return $validado ? $next($request) : redirect()->route('pagina-no-encontrada');
  }
}
