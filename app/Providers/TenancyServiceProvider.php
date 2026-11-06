<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Configuracion;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Livewire\Features\SupportFileUploads\FilePreviewController;
use Livewire\Livewire;
use Stancl\JobPipeline\JobPipeline;
use Stancl\Tenancy\Events;
use Stancl\Tenancy\Jobs;
use Stancl\Tenancy\Listeners;
use Stancl\Tenancy\Middleware;

class TenancyServiceProvider extends ServiceProvider
{
    // By default, no namespace is used to support the callable array syntax.
    public static string $controllerNamespace = '';

    public static function esPeticionAdminCentral(\Illuminate\Http\Request $request): bool
    {
        if ($request->is('admin', 'admin/*')) {
            return true;
        }
        if (! $request->is('livewire/update') || ! $request->isMethod('POST')) {
            return false;
        }
        $components = $request->input('components');
        if (! is_array($components) || $components === []) {
            return false;
        }
        foreach ($components as $component) {
            if (! is_array($component)) {
                return false;
            }
            $snapshot = $component['snapshot'] ?? null;
            if (! is_string($snapshot)) {
                return false;
            }
            $snapshot = json_decode($snapshot, true);
            if (! is_array($snapshot) || ! is_string($snapshot['checksum'] ?? null)
                || ! is_string($snapshot['memo']['name'] ?? null)
                || ! is_string($snapshot['memo']['path'] ?? null)
                || ! str_starts_with($snapshot['memo']['name'], 'central.')
                || ! str_starts_with(ltrim($snapshot['memo']['path'] ?? '', '/'), 'admin/')) {
                return false;
            }
            $checksum = $snapshot['checksum'];
            unset($snapshot['checksum']);
            if (! hash_equals($checksum, \Livewire\Mechanisms\HandleComponents\Checksum::generate($snapshot))) {
                return false;
            }
        }

        return true;
    }

    public function events()
    {
        return [
            // Tenant events
            Events\CreatingTenant::class => [],
            Events\TenantCreated::class => [
                function (Events\TenantCreated $event): void {
                    if ((int) $event->tenant->onboarding_version === 1) {
                        return;
                    }

                    JobPipeline::make([
                        Jobs\CreateDatabase::class,
                        Jobs\MigrateDatabase::class,
                        Jobs\SeedDatabase::class,
                    ])->send(fn (Events\TenantCreated $created) => $created->tenant)
                        ->shouldBeQueued(false)->toListener()($event);
                },
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
        $this->app->bind(Middleware\InitializeTenancyByDomain::class, function ($app) {
            return new class($app->make(\Stancl\Tenancy\Tenancy::class), $app->make(\Stancl\Tenancy\Resolvers\DomainTenantResolver::class)) extends Middleware\InitializeTenancyByDomain
            {
                public function handle($request, \Closure $next)
                {
                    if (TenancyServiceProvider::esPeticionAdminCentral($request)) {
                        if ($this->tenancy->initialized) {
                            $this->tenancy->end();
                        }

                        return $next($request);
                    }

                    return parent::handle($request, $next);
                }
            };
        });
    }

    public function boot()
    {
        $this->bootEvents();
        $this->mapRoutes();

        $this->makeTenancyMiddlewareHighestPriority();

        Event::listen(Events\TenancyBootstrapped::class, function (): void {
            $registrar = app(\Spatie\Permission\PermissionRegistrar::class);
            $registrar->clearPermissionsCollection();
            $registrar->cacheKey = 'spatie.permission.cache.tenant.'.tenant('id');
        });
        Event::listen(Events\TenancyEnded::class, function (): void {
            $registrar = app(\Spatie\Permission\PermissionRegistrar::class);
            $registrar->clearPermissionsCollection();
            $registrar->cacheKey = 'spatie.permission.cache.central';
        });

        Livewire::addPersistentMiddleware([
            \App\Http\Middleware\RevisarSuspensionAdmin::class,
            \App\Http\Middleware\RevisarSuspensionTenant::class,
        ]);

        Livewire::setUpdateRoute(function ($handle) {
            return Route::post('/livewire/update', $handle)
                ->middleware([
                    \App\Http\Middleware\InicializarTenantLivewire::class,
                    'web',
                    'universal',
                    \App\Http\Middleware\RevisarSuspensionTenant::class,
                ]);
        });

        FilePreviewController::$middleware = [
            'web',
            'universal',
            Middleware\InitializeTenancyByDomain::class,
        ];

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

            \App\Http\Middleware\InicializarTenantLivewire::class,
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
