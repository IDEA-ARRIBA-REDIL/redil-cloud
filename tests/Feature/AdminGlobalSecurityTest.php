<?php

namespace Tests\Feature;

use App\Http\Middleware\RevisarSuspensionTenant;
use App\Jobs\ConfigurarNuevoTenantJob;
use App\Livewire\Central\AdminDashboard;
use App\Mail\CodigoAdminMail;
use App\Models\InvitacionTenant;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\UserAdminRedil;
use App\Services\EstadoTenantService;
use App\Services\RegistroTenantService;
use App\Services\SeguridadAdminService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Livewire\Mechanisms\PersistentMiddleware\PersistentMiddleware;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class AdminGlobalSecurityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.default' => 'central',
            'database.connections.central' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => true],
            'tenancy.central_domains' => ['localhost'],
            'tenancy.bootstrappers' => [],
            'cache.default' => 'array',
            'logging.default' => 'null',
            'admin_global.registration_domain' => 'redil.test',
            'view.compiled' => storage_path('framework/views'),
        ]);
        DB::purge('central');
        DB::setDefaultConnection('central');
        Cache::flush();
        $this->app->instance(\Illuminate\Cache\RateLimiter::class, new \Illuminate\Cache\RateLimiter(Cache::store('array')));
        \Illuminate\Support\Facades\RateLimiter::clearResolvedInstance(\Illuminate\Cache\RateLimiter::class);
        Mail::fake();
        Queue::fake();
        session()->start();
        Schema::create('users_admins_redil', function (Blueprint $t): void {
            $t->id();
            $t->string('name');
            $t->string('email')->unique();
            $t->string('password');
            $t->boolean('is_suspended')->default(false);
            $t->rememberToken();
            $t->timestamps();
        });
        Schema::create('plans', function (Blueprint $t): void {
            $t->id();
            $t->string('nombre');
            $t->string('slug')->unique();
            $t->boolean('activo')->default(true);
            $t->unsignedInteger('max_miembros')->nullable();
            $t->boolean('incluye_logo')->default(false);
            $t->boolean('incluye_marca_blanca')->default(false);
            $t->timestamps();
        });
        Schema::create('tenants', function (Blueprint $t): void {
            $t->string('id')->primary();
            $t->json('data')->nullable();
            $t->timestamps();
            $t->foreignId('plan_id')->nullable();
            $t->string('status')->default('pending_review');
            $t->boolean('is_suspended')->default(false);
            $t->string('suspension_reason')->nullable();
            foreach (['license_starts_at', 'license_ends_at', 'grace_ends_at', 'approved_at'] as $column) {
                $t->timestamp($column)->nullable();
            }
            foreach (['notified_30_days', 'notified_7_days', 'notified_grace'] as $column) {
                $t->boolean($column)->default(false);
            }
            $t->integer('miembros_count_cache')->default(0);
            $t->unsignedBigInteger('approved_by')->nullable();
            $t->text('notes')->nullable();
        });
        Schema::create('domains', function (Blueprint $t): void {
            $t->id();
            $t->string('domain')->unique();
            $t->string('tenant_id');
            $t->timestamps();
        });
        Schema::create('admin_notifications', function (Blueprint $t): void {
            $t->id();
            $t->string('tenant_id')->nullable();
            $t->string('tipo');
            $t->text('mensaje');
            $t->timestamps();
        });
        (require database_path('migrations/2026_09_28_191657_harden_tenant_onboarding.php'))->up();
    }

    protected function tearDown(): void
    {
        if (tenancy()->initialized) {
            tenancy()->end();
        }
        parent::tearDown();
    }

    private function admin(): UserAdminRedil
    {
        return UserAdminRedil::create(['name' => 'Responsable', 'email' => 'admin@example.test', 'password' => 'Clave-larga-de-prueba-2026']);
    }

    private function autenticar(UserAdminRedil $admin): void
    {
        Auth::guard('admin')->login($admin);
        session()->put('admin_mfa', ['id' => $admin->id, 'password' => hash('sha256', $admin->password), 'expires' => now()->addHour()->timestamp]);
    }

    private function plan(): Plan
    {
        return Plan::create(['nombre' => 'Básico', 'slug' => 'basico-350', 'activo' => true]);
    }

    private function invitacion(): array
    {
        $codigo = str_repeat('a', 64);
        $invitacion = InvitacionTenant::create([
            'token_hash' => hash('sha256', $codigo), 'email' => 'cliente@example.test',
            'plan_id' => $this->plan()->id, 'created_by' => $this->admin()->id,
            'payment_reference' => 'PAGO-1', 'paid_at' => now(), 'expires_at' => now()->addDay(),
        ]);

        return [$codigo, $invitacion];
    }

    private function datos(string $codigo): array
    {
        return ['codigo' => $codigo, 'admin_email' => 'cliente@example.test', 'church_name' => 'Iglesia de prueba',
            'domain' => 'iglesia', 'pastor_name' => 'Pastor', 'pastor_phone' => '+570000000',
            'admin_contact_name' => 'Administrador', 'admin_contact_phone' => '+570000000',
            'city' => 'Ciudad', 'country' => 'Colombia', 'estimated_members' => 100, 'whatsapp' => '+570000000'];
    }

    public function test_invalid_code_never_creates_tenant_or_dispatches_work(): void
    {
        try {
            app(RegistroTenantService::class)->registrar($this->datos(str_repeat('b', 64)));
            $this->fail('Debió rechazar el código.');
        } catch (ValidationException) {
            $this->assertSame(0, Tenant::count());
            Queue::assertNothingPushed();
            Mail::assertNothingOutgoing();
        }
    }

    public function test_invitation_is_single_use_and_plan_is_server_owned(): void
    {
        [$codigo, $invitacion] = $this->invitacion();
        $datos = $this->datos($codigo) + ['plan' => 'premium', 'plan_id' => 999];
        $tenant = app(RegistroTenantService::class)->registrar($datos);
        $this->assertSame($invitacion->plan_id, $tenant->plan_id);
        $this->assertSame('pending_review', $tenant->status);
        $this->assertNull($tenant->provisioned_at);
        $this->assertNotNull($invitacion->fresh()->used_at);
        Queue::assertPushed(ConfigurarNuevoTenantJob::class, 1);
        try {
            app(RegistroTenantService::class)->registrar($datos);
            $this->fail('No debe reutilizarse.');
        } catch (ValidationException) {
            $this->assertSame(1, Tenant::count());
            Queue::assertPushed(ConfigurarNuevoTenantJob::class, 1);
        }
    }

    public function test_expired_revoked_wrong_email_and_disabled_plan_are_rejected(): void
    {
        [$codigo, $invitacion] = $this->invitacion();
        foreach (['expired', 'revoked', 'email', 'plan'] as $caso) {
            $invitacion->update(['expires_at' => now()->addDay(), 'revoked_at' => null]);
            Plan::query()->update(['activo' => true]);
            $datos = $this->datos($codigo);
            if ($caso === 'expired') {
                $invitacion->update(['expires_at' => now()->subMinute()]);
            }
            if ($caso === 'revoked') {
                $invitacion->update(['revoked_at' => now()]);
            }
            if ($caso === 'email') {
                $datos['admin_email'] = 'otro@example.test';
            }
            if ($caso === 'plan') {
                Plan::query()->update(['activo' => false]);
            }
            try {
                app(RegistroTenantService::class)->registrar($datos);
                $this->fail($caso);
            } catch (ValidationException) {
                $this->assertSame(0, Tenant::count());
            }
        }
    }

    public function test_domains_are_validated_and_reserved_names_rejected(): void
    {
        foreach (['admin', 'www', 'otro.com', '-iglesia', 'iglesia-', str_repeat('a', 64), '<script>'] as $domain) {
            try {
                app(RegistroTenantService::class)->dominio($domain);
                $this->fail($domain);
            } catch (ValidationException) {
                $this->assertTrue(true);
            }
        }
        $this->assertSame('mi-iglesia.redil.test', app(RegistroTenantService::class)->dominio('Mi-Iglesia'));
    }

    public function test_license_fields_are_physical_and_queries_find_them(): void
    {
        $tenant = Tenant::create(['id' => 'licencia', 'onboarding_version' => 1, 'status' => 'active', 'is_suspended' => false,
            'license_starts_at' => now()->subDay(), 'license_ends_at' => now()->addDay(), 'grace_ends_at' => now()->addDays(8)]);
        $this->assertSame(1, Tenant::where('status', 'active')->whereNotNull('license_ends_at')->count());
        $data = json_decode(DB::table('tenants')->value('data'), true);
        $this->assertArrayNotHasKey('status', $data);
        $this->assertTrue($tenant->fresh()->permiteAcceso());
    }

    public function test_only_active_unexpired_unsuspended_tenant_is_allowed(): void
    {
        $tenant = new Tenant(['id' => 't', 'status' => 'active', 'is_suspended' => false,
            'license_starts_at' => now()->subDay(), 'license_ends_at' => now()->addDay(), 'grace_ends_at' => now()->addDays(8)]);
        $this->assertTrue($tenant->permiteAcceso());
        foreach (['pending_review', 'setup_failed', 'cancelled', 'expired', 'suspended', null] as $estado) {
            $tenant->status = $estado;
            $this->assertFalse($tenant->permiteAcceso());
        }
        $tenant->status = 'active';
        $tenant->is_suspended = true;
        $this->assertFalse($tenant->permiteAcceso());
        $tenant->is_suspended = false;
        $tenant->grace_ends_at = now()->subDay();
        $this->assertFalse($tenant->permiteAcceso());
    }

    public function test_password_does_not_authenticate_until_email_code_is_validated(): void
    {
        $admin = $this->admin();
        $service = app(SeguridadAdminService::class);
        $service->iniciar($admin->email, 'Clave-larga-de-prueba-2026');
        $this->assertFalse(Auth::guard('admin')->check());
        $mail = Mail::queued(CodigoAdminMail::class)->first();
        $this->assertNotNull($mail);
        $this->assertSame('admin_security', $mail->connection);
        $this->assertSame('admin-security', $mail->queue);
        $service->verificar($mail->codigo);
        $this->assertSame($admin->id, Auth::guard('admin')->id());
        $this->assertSame($admin->id, $service->exigir()->id);
        $this->expectException(ValidationException::class);
        $service->verificar($mail->codigo);
    }

    public function test_admin_password_attempts_are_rate_limited(): void
    {
        $admin = $this->admin();
        for ($i = 0; $i < 5; $i++) {
            try {
                app(SeguridadAdminService::class)->iniciar($admin->email, 'incorrecta');
            } catch (ValidationException) {
            }
        }
        $this->expectException(ValidationException::class);
        app(SeguridadAdminService::class)->iniciar($admin->email, 'Clave-larga-de-prueba-2026');
    }

    public function test_suspended_admin_with_existing_session_is_rejected_by_component_boot(): void
    {
        $admin = $this->admin();
        $this->autenticar($admin);
        $admin->update(['is_suspended' => true]);
        $this->expectException(HttpException::class);
        (new AdminDashboard)->boot();
    }

    public function test_suspension_middlewares_are_persistent(): void
    {
        $middleware = app(PersistentMiddleware::class)->getPersistentMiddleware();
        $this->assertContains(\App\Http\Middleware\RevisarSuspensionAdmin::class, $middleware);
        $this->assertContains(RevisarSuspensionTenant::class, $middleware);
    }

    public function test_cannot_activate_unprovisioned_tenant_and_expired_sets_suspension(): void
    {
        $admin = $this->admin();
        $this->autenticar($admin);
        $plan = $this->plan();
        $tenant = Tenant::create(['id' => 'nuevo', 'onboarding_version' => 1, 'status' => 'pending_review', 'plan_id' => $plan->id]);
        try {
            app(EstadoTenantService::class)->actualizar($tenant, 'active', $plan->id, now()->addMonth()->toDateString());
            $this->fail('No debe activarse sin preparar.');
        } catch (ValidationException) {
            $this->assertSame('pending_review', $tenant->fresh()->status);
        }
        app(EstadoTenantService::class)->actualizar($tenant, 'expired', $plan->id, now()->toDateString());
        $this->assertTrue($tenant->fresh()->is_suspended);
        $this->assertFalse($tenant->fresh()->permiteAcceso());
    }

    public function test_provisioning_job_contains_no_password_and_uses_dedicated_queue(): void
    {
        $job = new ConfigurarNuevoTenantJob('tenant-id');
        $this->assertFalse(property_exists($job, 'admin_password'));
        $this->assertSame('provisioning', $job->connection);
        $this->assertSame('provisioning', $job->queue);
        $this->assertGreaterThan($job->timeout, config('queue.connections.provisioning.retry_after'));
    }

    public function test_existing_tenant_creation_keeps_the_original_seeding_pipeline(): void
    {
        \Illuminate\Support\Facades\Bus::fake();
        Tenant::create(['id' => 'legacy']);
        \Illuminate\Support\Facades\Bus::assertDispatchedSync(\Stancl\JobPipeline\JobPipeline::class, function ($pipeline): bool {
            return $pipeline->jobs === [
                \Stancl\Tenancy\Jobs\CreateDatabase::class,
                \Stancl\Tenancy\Jobs\MigrateDatabase::class,
                \Stancl\Tenancy\Jobs\SeedDatabase::class,
            ];
        });
        $this->assertSame('TenantDatabaseSeeder', config('tenancy.seeder_parameters.--class'));
    }

    public function test_form_tenant_skips_legacy_pipeline(): void
    {
        \Illuminate\Support\Facades\Bus::fake();
        Tenant::create(['id' => 'form', 'onboarding_version' => 1]);
        \Illuminate\Support\Facades\Bus::assertNotDispatched(\Stancl\JobPipeline\JobPipeline::class);
    }

    public function test_new_job_and_seeder_reject_tenants_outside_form_workflow(): void
    {
        $tenant = Tenant::withoutEvents(fn () => Tenant::create(['id' => 'legacy', 'status' => 'pending_review']));
        \Illuminate\Support\Facades\Artisan::shouldReceive('call')->never();
        (new ConfigurarNuevoTenantJob($tenant->id))->handle();
        $this->assertNull($tenant->fresh()->provisioned_at);
        tenancy()->initialize($tenant);
        $this->expectException(\LogicException::class);
        app(\Database\Seeders\NuevoTenantSeeder::class)->run();
    }

    public function test_admin_can_create_and_edit_a_plan_with_unlimited_capacity(): void
    {
        $this->autenticar($this->admin());
        $component = \Livewire\Livewire::test(\App\Livewire\Central\GestionarPlanes::class)
            ->call('create')->set('nombre', 'Comunidad')->set('slug', 'comunidad')
            ->set('max_miembros', 350)->set('incluye_logo', true)->call('store')->assertHasNoErrors();
        $plan = Plan::firstOrFail();
        $this->assertSame(350, $plan->max_miembros);
        $this->assertTrue($plan->incluye_logo);
        $component->call('edit', $plan->id)->set('nombre', 'Comunidad Plus')
            ->set('max_miembros', '')->set('incluye_marca_blanca', true)->call('store')->assertHasNoErrors();
        $this->assertSame(1, Plan::count());
        $this->assertNull($plan->fresh()->max_miembros);
        $this->assertSame('Comunidad Plus', $plan->fresh()->nombre);
        $this->assertTrue($plan->fresh()->incluye_marca_blanca);
    }

    public function test_duplicate_slug_invalid_capacity_and_blank_name_are_rejected(): void
    {
        $this->autenticar($this->admin());
        $plan = $this->plan();
        \Livewire\Livewire::test(\App\Livewire\Central\GestionarPlanes::class)
            ->call('create')->set('nombre', ' ')->set('slug', $plan->slug)->set('max_miembros', 0)
            ->call('store')->assertHasErrors(['nombre', 'slug', 'max_miembros']);
        $this->assertSame(1, Plan::count());
    }

    public function test_plan_can_be_disabled_and_enabled_without_changing_assigned_tenants(): void
    {
        $this->autenticar($this->admin());
        $plan = $this->plan();
        $tenant = Tenant::withoutEvents(fn () => Tenant::create(['id' => 'con-plan', 'plan_id' => $plan->id,
            'status' => 'active', 'license_starts_at' => now()->subDay(), 'license_ends_at' => now()->addMonth()]));
        $component = \Livewire\Livewire::test(\App\Livewire\Central\GestionarPlanes::class)->call('toggleActivo', $plan->id);
        $this->assertFalse($plan->fresh()->activo);
        $this->assertTrue($tenant->fresh()->permiteAcceso());
        $this->assertSame($plan->id, $tenant->fresh()->plan_id);
        $component->call('toggleActivo', $plan->id);
        $this->assertTrue($plan->fresh()->activo);
    }

    public function test_plan_actions_require_verified_administrator(): void
    {
        $this->expectException(HttpException::class);
        (new \App\Livewire\Central\GestionarPlanes)->toggleActivo(1);
    }

    public function test_plan_identifier_cannot_be_changed_from_client(): void
    {
        $this->autenticar($this->admin());
        $this->expectException(\Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException::class);
        \Livewire\Livewire::test(\App\Livewire\Central\GestionarPlanes::class)->set('plan_id', 123);
    }

    public function test_shared_central_layout_renders_navigation_and_scoped_theme(): void
    {
        $this->withoutVite();
        $this->autenticar($this->admin());
        $html = view('layouts.centralApp', ['slot' => new \Illuminate\Support\HtmlString('<h1>Contenido de prueba</h1>')])->render();
        $this->assertStringContainsString('class="redil-central"', $html);
        $this->assertStringContainsString('/admin/planes', $html);
        $this->assertStringContainsString('Administración central', $html);
        $this->assertStringContainsString('Contenido de prueba', $html);
        $this->assertStringContainsString('--redil-turquoise: #00d4b4', $html);
    }

    public function test_admin_mail_has_isolated_central_queue_encryption_and_expiration(): void
    {
        $mail = new CodigoAdminMail('123456');
        $job = new \Illuminate\Mail\SendQueuedMailable($mail);
        $this->assertTrue($job->shouldBeEncrypted);
        $this->assertSame('admin-security', $job->queue);
        $this->assertSame('admin_security', $job->connection);
        $this->assertSame('central', config('queue.connections.admin_security.connection'));
        $this->assertGreaterThan($job->timeout, config('queue.connections.admin_security.retry_after'));
        $this->assertTrue($mail->retryUntil()->isFuture());
        $this->travel(11)->minutes();
        $this->assertTrue($mail->retryUntil()->isPast());
        $this->travelBack();
    }
}
