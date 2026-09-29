<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RevisarSuspensionTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = tenant();

        if ($tenant) {
            if ($request->hasSession()) {
                $sessionTenant = $request->session()->get('_tenant_id');
                abort_if($sessionTenant !== null && $sessionTenant !== $tenant->getTenantKey(), 403);
                $request->session()->put('_tenant_id', $tenant->getTenantKey());
            }
            Log::withContext([
                'tenant_id' => $tenant->id,
            ]);
            // Verificar estado pendiente de revisión
            if ($tenant->status === 'pending_review') {
                return response(view('errors.tenant-pending'), 403);
            }

            // Verificar estado suspendido (ya sea por columna is_suspended o status)
            if (! $tenant->permiteAcceso()) {
                return response(view('errors.tenant-suspended'), 403);
            }
        }

        return $next($request);
    }
}
