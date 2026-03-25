<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrap();

        \Livewire\Livewire::setUpdateRoute(function ($handle) {
            $middleware = ['web'];
            $host = request()->getHost();
            $centralDomains = config('tenancy.central_domains', []);

            // Determinamos si el host actual es central (ignoramos el puerto para la comparación)
            $isCentral = collect($centralDomains)->contains(function ($domain) use ($host) {
                // Quitamos el puerto del dominio de la config si lo tiene para comparar
                $domainOnly = explode(':', $domain)[0];

                return $host === $domainOnly;
            });

            if (! $isCentral) {
                $middleware[] = \Stancl\Tenancy\Middleware\InitializeTenancyByDomain::class;
            }

            return \Illuminate\Support\Facades\Route::post('/livewire/update', $handle)
                ->middleware($middleware);
        });
    }
}
