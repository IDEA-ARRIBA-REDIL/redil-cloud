<?php

namespace Tests\Feature;

use App\Livewire\Central\MarcaBlancaTenant;
use App\Models\Configuracion;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\UserAdminRedil;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class BrandingPorUbicacionTest extends TestCase
{
    private Tenant $tenant;

    private UserAdminRedil $admin;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.default' => 'central',
            'database.connections.central' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''],
            'tenancy.bootstrappers' => [], 'tenancy.central_domains' => ['localhost'],
            'cache.default' => 'array', 'logging.default' => 'null', 'filesystems.default' => 'local',
            'view.compiled' => storage_path('framework/views'),
        ]);
        app('db')->purge('central');
        app('db')->setDefaultConnection('central');
        Storage::fake('local');
        Storage::fake('public');
        session()->start();
        Schema::create('users_admins_redil', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('password');
            $table->boolean('is_suspended')->default(false);
            $table->timestamps();
        });
        Schema::create('plans', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre');
            $table->boolean('incluye_logo')->default(true);
            $table->boolean('incluye_marca_blanca')->default(true);
            $table->timestamps();
        });
        Schema::create('tenants', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->foreignId('plan_id')->nullable();
            $table->json('data')->nullable();
            $table->timestamps();
        });
        Schema::create('domains', function (Blueprint $table): void {
            $table->id();
            $table->string('tenant_id');
            $table->string('domain');
            $table->timestamps();
        });
        Schema::create('configuraciones', function (Blueprint $table): void {
            $table->id();
            $table->boolean('logo_personalizado')->default(true);
            $table->boolean('marca_blanca')->default(true);
            foreach (['nombre_creador', 'url_creador', 'color_nombre_app', 'descripcion_login', 'sufijo_app',
                'version_app', 'logo_app', 'logo_app_negro', 'favicon_app'] as $column) {
                $table->string($column)->nullable();
            }
            $table->timestamps();
        });
        (require database_path('migrations/tenant/2026_11_02_165821_add_context_logos_to_configuraciones_table.php'))->up();
        $plan = Plan::query()->create(['nombre' => 'Ensayo']);
        $this->tenant = Tenant::withoutEvents(fn () => Tenant::query()->create(['id' => 'logos-test', 'plan_id' => $plan->id]));
        $this->tenant->domains()->create(['domain' => 'logos.localhost']);
        Configuracion::query()->create(['logo_app' => 'respaldo.png', 'logo_app_negro' => 'oscuro.png']);
        foreach (['respaldo.png', 'oscuro.png', 'login.png', 'menu.png', 'escuelas.png'] as $file) {
            Storage::disk('public')->put('img/branding/'.$file, 'imagen de prueba');
        }
        $this->admin = UserAdminRedil::query()->create(['name' => 'Equipo REDIL', 'email' => 'admin@example.test', 'password' => 'Clave-de-prueba-123!']);
        Auth::guard('admin')->login($this->admin);
        session()->put('admin_mfa', ['id' => $this->admin->id, 'password' => hash('sha256', $this->admin->password), 'expires' => now()->addHour()->timestamp]);
    }

    protected function tearDown(): void
    {
        if (tenancy()->initialized) {
            tenancy()->end();
        }
        parent::tearDown();
    }

    public function test_each_location_renders_its_own_logo_and_keeps_legacy_fallbacks(): void
    {
        $configuration = Configuracion::query()->firstOrFail();
        $configuration->update(['logo_login' => 'login.png', 'logo_menu_general' => 'menu.png', 'logo_menu_escuelas' => 'escuelas.png']);
        foreach (['login' => 'login.png', 'menu_general' => 'menu.png', 'menu_escuelas' => 'escuelas.png'] as $context => $file) {
            $html = $this->tenant->run(fn (): string => view('_partials.macros', ['configuracion' => $configuration, 'contexto_logo' => $context])->render());
            $this->assertStringContainsString('img/branding/'.$file, $html);
            $this->assertStringNotContainsString('img/branding/respaldo.png', $html);
        }
        $configuration->update(['logo_login' => 'archivo-inexistente.png']);
        $html = $this->tenant->run(fn (): string => view('_partials.macros', ['configuracion' => $configuration, 'contexto_logo' => 'login'])->render());
        $this->assertStringContainsString('img/branding/respaldo.png', $html);
        $html = $this->tenant->run(fn (): string => view('_partials.macros', ['configuracion' => $configuration, 'logo_negro' => true])->render());
        $this->assertStringContainsString('img/branding/oscuro.png', $html);
    }

    public function test_admin_saves_three_independent_logos_and_only_replaces_the_selected_one(): void
    {
        $component = Livewire::test(MarcaBlancaTenant::class, ['tenant' => $this->tenant])
            ->assertSee('Logo del login')->assertSee('Logo del menú general')->assertSee('Logo del menú de escuelas')
            ->set('logoLoginFile', UploadedFile::fake()->image('login.png', 800, 300))
            ->set('logoMenuGeneralFile', UploadedFile::fake()->image('general.png', 800, 300))
            ->set('logoMenuEscuelasFile', UploadedFile::fake()->image('escuelas.png', 800, 300))
            ->call('guardar')->assertHasNoErrors();
        $before = Configuracion::query()->firstOrFail();
        foreach (['logo_login', 'logo_menu_general', 'logo_menu_escuelas'] as $field) {
            Storage::disk('public')->assertExists('img/branding/'.$before->{$field});
            $this->assertSame([300, 150], array_slice(getimagesize(Storage::disk('public')->path('img/branding/'.$before->{$field})), 0, 2));
        }
        $this->assertCount(3, array_unique($before->only(['logo_login', 'logo_menu_general', 'logo_menu_escuelas'])));
        $component->set('logoMenuGeneralFile', UploadedFile::fake()->image('otro.png'))->call('guardar')->assertHasNoErrors();
        $after = $before->fresh();
        $this->assertSame($before->logo_login, $after->logo_login);
        $this->assertSame($before->logo_menu_escuelas, $after->logo_menu_escuelas);
        $this->assertNotSame($before->logo_menu_general, $after->logo_menu_general);
        Storage::disk('public')->assertMissing('img/branding/'.$before->logo_menu_general);
        Storage::disk('public')->assertExists('img/branding/'.$before->logo_login);
    }

    public function test_replacing_a_shared_logo_does_not_delete_other_locations_file(): void
    {
        Configuracion::query()->firstOrFail()->update(['logo_login' => 'respaldo.png', 'logo_menu_general' => 'respaldo.png']);
        Livewire::test(MarcaBlancaTenant::class, ['tenant' => $this->tenant])
            ->set('logoMenuGeneralFile', UploadedFile::fake()->image('otro.png'))->call('guardar')->assertHasNoErrors();
        Storage::disk('public')->assertExists('img/branding/respaldo.png');
    }

    public function test_uploads_are_revalidated_against_the_current_plan(): void
    {
        $component = Livewire::test(MarcaBlancaTenant::class, ['tenant' => $this->tenant]);
        $this->tenant->plan()->update(['incluye_logo' => false]);
        $component->set('logoLoginFile', UploadedFile::fake()->image('login.png'))->call('guardar')->assertHasErrors(['logoLoginFile']);
        $this->assertNull(Configuracion::query()->firstOrFail()->logo_login);
    }

    public function test_invalid_logo_dimensions_are_rejected(): void
    {
        Livewire::test(MarcaBlancaTenant::class, ['tenant' => $this->tenant])
            ->set('logoMenuEscuelasFile', UploadedFile::fake()->image('grande.png', 4100, 10))
            ->call('guardar')->assertHasErrors(['logoMenuEscuelasFile']);
        $this->assertNull(Configuracion::query()->firstOrFail()->logo_menu_escuelas);
    }

    public function test_central_mfa_and_active_admin_are_required(): void
    {
        session()->forget('admin_mfa');
        Livewire::test(MarcaBlancaTenant::class, ['tenant' => $this->tenant])->assertForbidden();
    }
}
