<?php

namespace Tests\Unit;

use App\Models\LoginBrandingDefault;
use App\Models\LoginPersonalizacion;
use App\Models\ThemeSetting;
use App\Services\BrandingEntitlementService;
use App\Services\LoginBrandingResolver;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Mockery;
use Tests\TestCase;

class LoginBrandingResolverThemeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $centralConnection = config('tenancy.database.central_connection', 'central');

        config([
            'database.default' => 'sqlite_testing',
            "database.connections.{$centralConnection}" => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
            ],
            'database.connections.sqlite_testing' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
            ],
        ]);

        DB::purge('sqlite_testing');
        DB::purge($centralConnection);

        Schema::connection($centralConnection)->create('login_branding_defaults', function (Blueprint $table): void {
            $table->id();
            $table->boolean('singleton_key')->default(true);
            $table->string('left_image_path')->nullable();
            $table->integer('carousel_interval_ms')->default(6000);
            $table->timestamps();
        });

        Schema::connection($centralConnection)->create('login_branding_default_slides', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('login_branding_default_id');
            $table->string('image_path');
            $table->string('alt_text')->nullable();
            $table->string('link_url')->nullable();
            $table->integer('sort_order')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('login_personalizaciones', function (Blueprint $table): void {
            $table->id();
            $table->boolean('singleton_key')->default(true);
            $table->string('left_image_path')->nullable();
            $table->integer('carousel_interval_ms')->default(6000);
            $table->timestamps();
        });

        Schema::create('login_carrusel_imagenes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('login_personalizacion_id');
            $table->string('image_path');
            $table->string('alt_text')->nullable();
            $table->string('link_url')->nullable();
            $table->integer('sort_order')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('theme_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre')->nullable();
            $table->string('class')->nullable();
            $table->string('value')->nullable();
            $table->string('category')->nullable();
            $table->string('gradient')->nullable();
            $table->string('value2')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function test_uses_theme_login_color_instead_of_central_default_when_tenant_has_no_left_image(): void
    {
        LoginBrandingDefault::query()->create([
            'left_image_path' => 'central/login-branding/default/left/fondo.png',
            'carousel_interval_ms' => 6000,
        ]);

        LoginPersonalizacion::query()->create([
            'left_image_path' => null,
            'carousel_interval_ms' => 6000,
        ]);

        ThemeSetting::query()->create([
            'nombre' => 'Fondo login',
            'class' => 'bg-login-left',
            'value' => '#141621',
            'category' => 'login',
            'is_active' => true,
        ]);

        $entitlement = Mockery::mock(BrandingEntitlementService::class);
        $entitlement->shouldReceive('canCustomize')->once()->andReturn(true);

        $resolved = (new LoginBrandingResolver($entitlement))->resolve();

        $this->assertNull($resolved['left_image_url']);
    }

    public function test_custom_tenant_left_image_takes_precedence_over_theme_login_color(): void
    {
        LoginBrandingDefault::query()->create([
            'left_image_path' => 'central/login-branding/default/left/fondo.png',
            'carousel_interval_ms' => 6000,
        ]);

        LoginPersonalizacion::query()->create([
            'left_image_path' => 'img/login-branding/left/iglesia.png',
            'carousel_interval_ms' => 6000,
        ]);

        ThemeSetting::query()->create([
            'nombre' => 'Fondo login',
            'class' => 'bg-login-left',
            'value' => '#141621',
            'category' => 'login',
            'is_active' => true,
        ]);

        $entitlement = Mockery::mock(BrandingEntitlementService::class);
        $entitlement->shouldReceive('canCustomize')->once()->andReturn(true);

        $resolved = (new LoginBrandingResolver($entitlement))->resolve();

        $this->assertStringContainsString('img/login-branding/left/iglesia.png', (string) $resolved['left_image_url']);
        $this->assertTrue($resolved['custom']);
    }

    public function test_falls_back_to_central_default_left_image_when_theme_login_color_is_inactive(): void
    {
        LoginBrandingDefault::query()->create([
            'left_image_path' => 'central/login-branding/default/left/fondo.png',
            'carousel_interval_ms' => 6000,
        ]);

        ThemeSetting::query()->create([
            'nombre' => 'Fondo login',
            'class' => 'bg-login-left',
            'value' => '#141621',
            'category' => 'login',
            'is_active' => false,
        ]);

        $entitlement = Mockery::mock(BrandingEntitlementService::class);
        $entitlement->shouldReceive('canCustomize')->once()->andReturn(false);

        $resolved = (new LoginBrandingResolver($entitlement))->resolve();

        $this->assertStringContainsString('central/login-branding/default/left/fondo.png', (string) $resolved['left_image_url']);
    }

    public function test_personalizacion_view_renders_upload_confirmation_alert_for_carousel_image(): void
    {
        $compiledPath = sys_get_temp_dir().'/redil-local-blade-views';
        if (! is_dir($compiledPath)) {
            mkdir($compiledPath, 0777, true);
        }

        config(['view.compiled' => $compiledPath]);
        $this->app->forgetInstance('blade.compiler');
        $this->app->forgetInstance('view.engine.resolver');
        $this->app->forgetInstance('view');

        $fakeUpload = new class
        {
            public function isPreviewable(): bool
            {
                return true;
            }

            public function temporaryUrl(): string
            {
                return 'https://example.test/tmp/carrusel.png';
            }

            public function getClientOriginalName(): string
            {
                return 'banner-domingo.png';
            }
        };

        $personalizacion = new LoginPersonalizacion([
            'left_image_path' => null,
            'carousel_interval_ms' => 6000,
        ]);
        $personalizacion->setRelation('imagenes', collect());

        $html = view('livewire.login.personalizacion-login', [
            'personalizacion' => $personalizacion,
            'imagenIzquierda' => null,
            'nuevaImagen' => $fakeUpload,
            'errors' => (new ViewErrorBag)->put('default', new MessageBag),
        ])->render();

        $this->assertStringContainsString('Imagen cargada: banner-domingo.png', $html);
        $this->assertStringContainsString('Lista para agregar al carrusel', $html);
        $this->assertStringContainsString('Guardar cambios', $html);
        $this->assertStringContainsString('https://example.test/tmp/carrusel.png', $html);
    }
}
