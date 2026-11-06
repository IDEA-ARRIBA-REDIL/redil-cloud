<?php

namespace App\Http\Middleware;

use App\Providers\TenancyServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Symfony\Component\HttpFoundation\Response;

class InicializarTenantLivewire
{
    public function handle(Request $request, Closure $next): Response
    {
        if (TenancyServiceProvider::esPeticionAdminCentral($request)) {
            return $next($request);
        }

        return app(InitializeTenancyByDomain::class)->handle($request, $next);
    }
}
