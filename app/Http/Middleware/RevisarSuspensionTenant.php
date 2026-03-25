<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RevisarSuspensionTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = tenant();
        
        if ($tenant) {
            $data = is_string($tenant->data) ? json_decode($tenant->data, true) : $tenant->data;
            if (isset($data['is_suspended']) && $data['is_suspended']) {
                abort(403, 'El entorno de esta iglesia se encuentra suspendido. Por favor, contacta con REDIL.');
            }
        }

        return $next($request);
    }
}
