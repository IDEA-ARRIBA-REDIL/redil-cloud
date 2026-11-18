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
        $idUsuario = $request->usuario->id ?? $request->usuario;

        return $request->user()->can('accederFicha', [\App\Models\User::class, (int) $idUsuario]) ? $next($request) : redirect()->route('pagina-no-encontrada');
    }
}
