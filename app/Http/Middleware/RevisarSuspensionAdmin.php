<?php

namespace App\Http\Middleware;

use App\Services\SeguridadAdminService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RevisarSuspensionAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            app(SeguridadAdminService::class)->exigir();
        } catch (HttpException $exception) {
            Auth::guard('admin')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            if ($request->hasHeader('X-Livewire') || $request->expectsJson()) {
                abort(403, 'Acceso administrativo no autorizado. Inicia sesión nuevamente.');
            }

            return redirect('/admin/login');
        }

        return $next($request);
    }
}
