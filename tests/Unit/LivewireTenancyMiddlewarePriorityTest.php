<?php

namespace Tests\Unit;

use App\Http\Middleware\InicializarTenantLivewire;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Http\Request;
use Illuminate\Session\Middleware\StartSession;
use Tests\TestCase;

class LivewireTenancyMiddlewarePriorityTest extends TestCase
{
    public function test_livewire_update_route_has_tenant_initialization_before_session_and_csrf(): void
    {
        $router = app('router');
        $route = $router->getRoutes()->match(Request::create('/livewire/update', 'POST'));
        $middleware = $router->gatherRouteMiddleware($route);

        $initIndex = array_search(InicializarTenantLivewire::class, $middleware, true);
        $sessionIndex = array_search(StartSession::class, $middleware, true);
        $csrfIndex = array_search(ValidateCsrfToken::class, $middleware, true);

        $this->assertNotFalse($initIndex, 'InicializarTenantLivewire must be in the route middleware');
        $this->assertNotFalse($sessionIndex, 'StartSession must be in the route middleware');
        $this->assertNotFalse($csrfIndex, 'ValidateCsrfToken must be in the route middleware');

        $this->assertLessThan(
            $sessionIndex,
            $initIndex,
            'InicializarTenantLivewire must execute before StartSession'
        );

        $this->assertLessThan(
            $csrfIndex,
            $initIndex,
            'InicializarTenantLivewire must execute before ValidateCsrfToken'
        );
    }
}
