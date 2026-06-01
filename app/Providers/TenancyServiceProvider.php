<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Configuracion;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Stancl\JobPipeline\JobPipeline;
use Stancl\Tenancy\Events;
use Stancl\Tenancy\Jobs;
use Stancl\Tenancy\Listeners;
use Stancl\Tenancy\Middleware;

class TenancyServiceProvider extends ServiceProvider
{
    // By default, no namespace is used to support the callable array syntax.
    public static string $controllerNamespace = '';

    public function events()
    {
        return [
            // Tenant events
            Events\CreatingTenant::class => [],
            Events\TenantCreated::class => [
                JobPipeline::make([
                    Jobs\CreateDatabase::class,
                    Jobs\MigrateDatabase::class,
                    Jobs\SeedDatabase::class,

                    // Your own jobs to prepare the tenant.
                    // Provision API keys, create S3 buckets, anything you want!

                ])->send(function (Events\TenantCreated $event) {
                    return $event->tenant;
                })->shouldBeQueued(false), // `false` by default, but you probably want to make this `true` for production.
            ],
            Events\SavingTenant::class => [],
            Events\TenantSaved::class => [],
            Events\UpdatingTenant::class => [],
            Events\TenantUpdated::class => [],
            Events\DeletingTenant::class => [],
            Events\TenantDeleted::class => [
                JobPipeline::make([
                    Jobs\DeleteDatabase::class,
                ])->send(function (Events\TenantDeleted $event) {
                    return $event->tenant;
                })->shouldBeQueued(false), // `false` by default, but you probably want to make this `true` for production.
            ],

            // Domain events
            Events\CreatingDomain::class => [],
            Events\DomainCreated::class => [],
            Events\SavingDomain::class => [],
            Events\DomainSaved::class => [],
            Events\UpdatingDomain::class => [],
            Events\DomainUpdated::class => [],
            Events\DeletingDomain::class => [],
            Events\DomainDeleted::class => [],

            // Database events
            Events\DatabaseCreated::class => [],
            Events\DatabaseMigrated::class => [],
            Events\DatabaseSeeded::class => [],
            Events\DatabaseRolledBack::class => [],
            Events\DatabaseDeleted::class => [],

            // Tenancy events
            Events\InitializingTenancy::class => [],
            Events\TenancyInitialized::class => [
                Listeners\BootstrapTenancy::class,
            ],

            Events\EndingTenancy::class => [],
            Events\TenancyEnded::class => [
                Listeners\RevertToCentralContext::class,
            ],

            Events\BootstrappingTenancy::class => [],
            Events\TenancyBootstrapped::class => [],
            Events\RevertingToCentralContext::class => [],
            Events\RevertedToCentralContext::class => [],

            // Resource syncing
            Events\SyncedResourceSaved::class => [
                Listeners\UpdateSyncedResource::class,
            ],

            // Fired only when a synced resource is changed in a different DB than the origin DB (to avoid infinite loops)
            Events\SyncedResourceChangedInForeignDatabase::class => [],
        ];
    }

    public function register()
    {
        //
    }

    public function boot()
    {
        $this->bootEvents();
        $this->mapRoutes();

        $this->makeTenancyMiddlewareHighestPriority();

        // Sobreescribir las variables globales con la config del tenant
        Event::listen(Events\TenancyBootstrapped::class, function () {
            // Crear directorios de storage necesarios para el tenant si no existen.
            // Esto previene el error "tempnam(): file created in the system's temporary directory"
            // que ocurre cuando Livewire WithFileUploads intenta subir archivos temporales
            // al disco 'local' aislado del tenant y el directorio no existe todavía.
            $directorios = [
                storage_path('app/livewire-tmp'),
                storage_path('app/public'),
                storage_path('framework/cache'),
                storage_path('framework/views'),
                storage_path('framework/sessions'),
                storage_path('logs'),
            ];

            foreach ($directorios as $dir) {
                if (! is_dir($dir)) {
                    mkdir($dir, 0775, true);
                }
            }

            if (Schema::hasTable('configuraciones')) {
                $configuracion = Configuracion::first();
                if ($configuracion) {
                    // Crear directorios de archivos de actividades si hay configuracion
                    $rutaBase = $configuracion->ruta_almacenamiento;
                    if ($rutaBase) {
                        $directoriosActividad = [
                            storage_path('app/public/'.$rutaBase.'/archivos/actividades'),
                            storage_path('app/public/'.$rutaBase.'/img/respuestas-formulario'),
                        ];
                        foreach ($directoriosActividad as $dir) {
                            if (! is_dir($dir)) {
                                mkdir($dir, 0775, true);
                            }
                        }
                    }

                    // Fallbacks globales por defecto
                    $logoUrl = asset('storage/global/img/logo_crecer.png');
                    $faviconUrl = asset('assets/img/favicon/logo_crecer.ico');

                    $branding = [
                        'templateName' => $configuracion->nombre_app_personalizado ?: config('variables.templateName'),
                        'templateNameColor' => $configuracion->color_nombre_app ?: config('variables.templateNameColor'),
                        'creatorName' => $configuracion->nombre_creador ?: config('variables.creatorName'),
                        'creatorUrl' => $configuracion->url_creador ?: config('variables.creatorUrl'),
                        'templateDescriptionLogin' => $configuracion->descripcion_login ?: config('variables.templateDescriptionLogin'),
                        'templateSuffix' => $configuracion->sufijo_app ?: config('variables.templateSuffix'),
                        'templateVersion' => $configuracion->version_app ?: config('variables.templateVersion'),
                    ];

                    // Si tiene Marca Blanca activa, intentamos cargar sus archivos personalizados
                    if ($configuracion->marca_blanca) {
                        $rutaBase = $configuracion->ruta_almacenamiento ? $configuracion->ruta_almacenamiento.'/' : '';

                        if ($configuracion->logo_app) {
                            $logoUrl = tenant_asset($rutaBase.'img/branding/'.$configuracion->logo_app);
                        }
                        if ($configuracion->favicon_app) {
                            $faviconUrl = tenant_asset($rutaBase.'img/branding/'.$configuracion->favicon_app);
                        }
                    }

                    // Inyectamos todo al config global de la ejecución
                    config([
                        'variables.templateName' => $branding['templateName'],
                        'variables.templateNameColor' => $branding['templateNameColor'],
                        'variables.creatorName' => $branding['creatorName'],
                        'variables.creatorUrl' => $branding['creatorUrl'],
                        'variables.templateDescriptionLogin' => $branding['templateDescriptionLogin'],
                        'variables.templateSuffix' => $branding['templateSuffix'],
                        'variables.templateVersion' => $branding['templateVersion'],
                        'variables.logoApp' => $logoUrl,
                        'variables.faviconApp' => $faviconUrl,
                        'app.name' => $branding['templateName'],
                    ]);
                }
            }
        });
    }

    protected function bootEvents()
    {
        foreach ($this->events() as $event => $listeners) {
            foreach ($listeners as $listener) {
                if ($listener instanceof JobPipeline) {
                    $listener = $listener->toListener();
                }

                Event::listen($event, $listener);
            }
        }
    }

    protected function mapRoutes()
    {
        $this->app->booted(function () {
            if (file_exists(base_path('routes/tenant.php'))) {
                Route::namespace(static::$controllerNamespace)
                    ->group(base_path('routes/tenant.php'));
            }
        });
    }

    protected function makeTenancyMiddlewareHighestPriority()
    {
        $tenancyMiddleware = [
            // Even higher priority than the initialization middleware
            Middleware\PreventAccessFromCentralDomains::class,

            Middleware\InitializeTenancyByDomain::class,
            Middleware\InitializeTenancyBySubdomain::class,
            Middleware\InitializeTenancyByDomainOrSubdomain::class,
            Middleware\InitializeTenancyByPath::class,
            Middleware\InitializeTenancyByRequestData::class,
        ];

        foreach (array_reverse($tenancyMiddleware) as $middleware) {
            $this->app[\Illuminate\Contracts\Http\Kernel::class]->prependToMiddlewarePriority($middleware);
        }
    }
}
