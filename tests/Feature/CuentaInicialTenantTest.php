<?php

namespace Tests\Feature;

use App\Http\Controllers\NotificacionController;
use App\Livewire\Configuracion\GestionarPlantillasInformes;
use App\Livewire\Notificaciones\AdminTiposNotificaciones;
use App\Livewire\RolesPrivilegios\EditarPermisos;
use App\Livewire\RolesPrivilegios\GestionarRolesPrivilegios;
use App\Livewire\RuedaDeLaVida\GestionarRuedaDeLaVida;
use App\Models\Configuracion;
use App\Models\Grupo;
use App\Models\LoginPersonalizacion;
use App\Models\Role;
use App\Models\Sede;
use App\Models\Tenant;
use App\Models\ThemeSetting;
use App\Models\User;
use App\Services\PermisosCuentaInicial;
use Database\Seeders\CuentaInicialTenantSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class CuentaInicialTenantTest extends TestCase
{
    private static ?string $tenantId = null;

    private Tenant $testTenant;

    protected function setUp(): void
    {
        parent::setUp();
        $database = getenv('CUENTA_INICIAL_TEST_DATABASE');
        if (! is_string($database) || ! preg_match('/^redil_integracion_test_[0-9]{8}$/', $database)) {
            $this->markTestSkipped('Requiere CUENTA_INICIAL_TEST_DATABASE=redil_integracion_test_YYYYMMDD; nunca utiliza la base de trabajo.');
        }
        foreach (['central', 'pgsql'] as $name) {
            config(['database.connections.'.$name.'.host' => '127.0.0.1',
                'database.connections.'.$name.'.port' => 5432,
                'database.connections.'.$name.'.database' => $database,
                'database.connections.'.$name.'.url' => null]);
            app('db')->purge($name);
        }
        config(['cache.default' => 'array', 'logging.default' => 'null', 'view.compiled' => storage_path('framework/views')]);
        config(['cuenta_inicial.soporte_password' => 'Solo-para-pruebas-123!']);
        Mail::fake();
        Queue::fake();
        if (! self::$tenantId) {
            self::$tenantId = 'cuenta_inicial_test_'.bin2hex(random_bytes(4));
            $result = Artisan::call('tenant:preparar-cuenta-local', ['--tenant' => self::$tenantId, '--database' => $database, '--no-interaction' => true]);
            $this->assertSame(0, $result, Artisan::output());
        }
        $this->testTenant = Tenant::findOrFail(self::$tenantId);
        (new Tenant)->getConnection()->beginTransaction();
        tenancy()->initialize($this->testTenant);
        (new User)->getConnection()->beginTransaction();
    }

    protected function tearDown(): void
    {
        if (isset($this->testTenant)) {
            auth()->forgetGuards();
            (new User)->getConnection()->rollBack();
            tenancy()->end();
            (new Tenant)->getConnection()->rollBack();
        }
        parent::tearDown();
    }

    public function test_creates_exactly_four_users_and_only_the_required_structure(): void
    {
        $this->assertSame(4, User::count());
        $this->assertSame(1, Sede::count());
        $this->assertSame(1, Grupo::count());
        $this->assertSame(1, \App\Models\Iglesia::count());
        $sede = Sede::firstOrFail();
        $grupo = Grupo::firstOrFail();
        $this->assertEquals($grupo->id, $sede->grupo_id);
        $this->assertEquals($sede->id, $grupo->sede_id);
        $this->assertSame(2, $grupo->encargados()->count());
        $this->assertSame(2, $grupo->asistentes()->count());
        $this->assertSame(2, \App\Models\Iglesia::firstOrFail()->pastoresEncargados()->count());
        $this->assertSame(4, (new User)->getConnection()->table('model_has_roles')->where('activo', true)->count());
        $this->assertSame(3, User::whereNull('email_verified_at')->count());
        foreach (['pagos', 'compras', (new \App\Models\ReporteGrupo)->getTable(), (new \App\Models\ReporteReunion)->getTable(), 'informes_grupo', 'informes_en_cola', 'crecimiento_usuario', 'matriculas', 'bitacora_tipos_usuarios'] as $table) {
            $this->assertSame(0, (new User)->getConnection()->table($table)->count(), $table);
        }
        Mail::assertNothingSent();
        Mail::assertNothingQueued();
        Queue::assertNothingPushed();
    }

    public function test_admin_exclusions_and_developer_permissions_are_separate(): void
    {
        $admin = $this->account(PermisosCuentaInicial::ADMINISTRADOR);
        $developer = $this->account(PermisosCuentaInicial::SUPERADMINISTRADOR);
        $this->assertTrue($admin->hasPermissionTo('personas.item_asistentes'));
        foreach (Permission::all() as $permission) {
            $this->assertTrue($developer->hasPermissionTo($permission->name), $permission->name);
            if (PermisosCuentaInicial::reservado($permission->name)) {
                $this->assertFalse($admin->hasPermissionTo($permission->name), $permission->name);
            }
        }
        $this->assertFalse($admin->hasPermissionTo('configuraciones.subitem_roles'));
        $this->assertFalse($admin->hasPermissionTo('configuraciones.subitem_tipo_de_usuarios'));
        $this->assertSame(1, Role::where('name', 'Conéctate')->count());
        $this->assertSame(1, Role::where('name', 'Punto de pago')->count());
        $this->assertSame(0, Role::where('name', 'like', '%Prueba%')->count());
        $this->assertSame(0, Role::where('name', 'like', 'Ingresos %')->count());
    }

    public function test_catalogues_forms_and_relationships_are_complete(): void
    {
        $connection = (new User)->getConnection();
        foreach (['paises', 'municipios', 'barrios', 'calificaciones', 'campos_extra', 'campos_extra_grupo',
            'campos_extras_formularios', 'campos_formulario_usuario', 'campos_informe_excel', 'campos_perfil_usuario',
            'carreras', 'continentes', 'departamentos', 'formularios_usuario', 'formulario_usuario_rol', 'informes', 'informe_rol',
            'localidades', 'monedas', 'ocupaciones', 'pasos_crecimiento', 'rangos_edad', 'zonas', 'secciones_informes', 'items_subsecciones_informes'] as $table) {
            $this->assertGreaterThan(0, $connection->table($table)->count(), $table);
        }
        $this->assertSame(4, \App\Models\SalonInfantil::count());
        $this->assertSame(2, \App\Models\EstacionSalonInfantil::count());
        foreach ([['tipo_usuarios', 'id_rol_dependiente', 'roles'], ['formularios_usuario', 'tipo_usuario_default_id', 'tipo_usuarios'],
            ['formularios_usuario', 'sede_default_id', 'sedes'], ['secciones_formulario_usuario', 'formulario_usuario_id', 'formularios_usuario'],
            ['campo_seccion_formulario_usuario', 'seccion_id', 'secciones_formulario_usuario'],
            ['campo_seccion_formulario_usuario', 'campo_id', 'campos_formulario_usuario'],
            ['calificaciones', 'sistema_calificacion_id', 'sistema_calificaciones'],
            ['pasos_crecimiento', 'seccion_paso_crecimiento_id', 'secciones_pasos_crecimiento'],
            ['paises', 'continente_id', 'continentes'], ['regiones', 'pais_id', 'paises'],
            ['departamentos', 'region_id', 'regiones'], ['municipios', 'departamento_id', 'departamentos'],
            ['localidades', 'municipio_id', 'municipios'], ['barrios', 'municipio_id', 'municipios'],
            ['barrios', 'localidad_id', 'localidades']] as [$table, $column, $parent]) {
            $this->assertSame(0, $connection->table($table.' as child')->whereNotNull('child.'.$column)
                ->whereRaw('NOT EXISTS (SELECT 1 FROM '.$connection->getQueryGrammar()->wrapTable($parent).' AS parent WHERE parent.id::text = child.'.$connection->getQueryGrammar()->wrap($column).'::text)')->count(), $table.'.'.$column);
        }
        $this->assertSame(0, $connection->table('campos_formulario_usuario')->whereIn('name_id', ['multiple', 'textarea'])->count());
        $this->assertSame(0, $connection->table('formularios_usuario')->whereNotNull('deleted_at')->count());
    }

    public function test_second_execution_preserves_customizations_and_does_not_duplicate_rows(): void
    {
        Configuracion::firstOrFail()->update(['nombre_app_personalizado' => 'Mi personalización']);
        $theme = ThemeSetting::query()->where('category', 'button')->where('class', 'btn-primary')->firstOrFail();
        $theme->update(['value' => '#123456']);
        $login = LoginPersonalizacion::query()->firstOrFail();
        $login->update(['carousel_interval_ms' => 9000, 'left_image_path' => 'mi-fondo.png']);
        $support = User::query()->where('email', 'soporte@redil.co')->firstOrFail();
        $support->update(['password' => Hash::make('Una-clave-posterior-123!')]);
        $cssPath = public_path('storage/'.$this->testTenant->id.'/theme/_custom-variables.css');
        $cssBefore = hash_file('sha256', $cssPath);
        $role = Role::where('name', PermisosCuentaInicial::ADMINISTRADOR)->firstOrFail();
        $role->revokePermissionTo('personas.item_asistentes');
        $connection = (new User)->getConnection();
        $before = [];
        foreach ($connection->getSchemaBuilder()->getTableListing(schema: $this->testTenant->database()->getName()) as $table) {
            $before[$table] = $connection->table($table)->count();
        }
        app(CuentaInicialTenantSeeder::class)->__invoke();
        foreach ($before as $table => $count) {
            $this->assertSame($count, $connection->table($table)->count(), $table);
        }
        $this->assertSame('Mi personalización', Configuracion::firstOrFail()->nombre_app_personalizado);
        $this->assertFalse($role->fresh()->hasPermissionTo('personas.item_asistentes'));
        $this->assertSame('#123456', $theme->fresh()->value);
        $this->assertSame(9000, $login->fresh()->carousel_interval_ms);
        $this->assertSame('mi-fondo.png', $login->fresh()->left_image_path);
        $this->assertTrue(Hash::check('Una-clave-posterior-123!', $support->fresh()->password));
        $this->assertSame($cssBefore, hash_file('sha256', $cssPath));
    }

    public function test_installs_the_complete_visual_package_in_the_tenant_storage(): void
    {
        $settings = json_decode(file_get_contents(database_path('seeders/data/cuenta-inicial/tema.json')), true, flags: JSON_THROW_ON_ERROR);
        $this->assertCount(count($settings) + count(config('dashboard_theme')), ThemeSetting::all());
        foreach ($settings as $setting) {
            $row = ThemeSetting::query()->where('class', $setting['class'])->where('category', $setting['category'])->firstOrFail();
            $this->assertEquals($setting, $row->only(array_keys($setting)));
        }
        $configuration = Configuracion::query()->firstOrFail();
        $this->assertEquals(config('cuenta_inicial.marca'), $configuration->only(array_keys(config('cuenta_inicial.marca'))));
        $login = LoginPersonalizacion::query()->with('imagenes')->firstOrFail();
        $this->assertSame(6000, $login->carousel_interval_ms);
        $this->assertCount(3, $login->imagenes);
        $this->assertSame([1, 2, 3], $login->imagenes->pluck('sort_order')->all());
        $paths = [$login->left_image_path, ...$login->imagenes->pluck('image_path')->all(),
            'img/branding/logo-redil.png', 'img/branding/logo-redil-blanco.png', 'img/branding/isotipo-redil.png',
            'iglesia/img/branding/logo-redil.png', 'iglesia/img/branding/isotipo-redil.png'];
        foreach ($paths as $path) {
            $this->assertTrue(Storage::disk('public')->exists($path), $path);
            $this->assertSame(hash_file('sha256', database_path('seeders/data/cuenta-inicial/imagenes/'.basename($path))), hash('sha256', Storage::disk('public')->get($path)));
        }
        $this->assertTrue(Storage::exists('img/branding/logo-redil.png'));
        $css = file_get_contents(public_path('storage/'.$this->testTenant->id.'/theme/_custom-variables.css'));
        $this->assertStringContainsString('#63e3bb', $css);
        $this->assertStringContainsString('#4db997', $css);
        $this->assertStringContainsString('.bg-menu-theme{background:linear-gradient(180deg,#000000 0% ,#000000 100%)', $css);
        $this->assertStringContainsString('linear-gradient(180deg,#ffffff 0%,#ffffff 100%)', $css);
        $this->testTenant->setRelation('plan', new \App\Models\Plan(['incluye_logo' => true, 'incluye_marca_blanca' => true]));
        $resolved = app(\App\Services\LoginBrandingResolver::class)->resolve();
        $this->assertTrue($resolved['custom']);
        $this->assertCount(3, $resolved['slides']);
        $this->assertStringContainsString('fondo-login.png', $resolved['left_image_url']);
    }

    public function test_support_can_authenticate_with_the_installation_password(): void
    {
        $support = User::query()->where('email', 'soporte@redil.co')->firstOrFail();
        $this->assertNotNull($support->email_verified_at);
        $this->assertNotSame(config('cuenta_inicial.soporte_password'), $support->password);
        $this->assertTrue(auth('web')->validate(['email' => $support->email, 'password' => config('cuenta_inicial.soporte_password')]));
        $this->assertFalse(auth('web')->validate(['email' => $support->email, 'password' => 'incorrecta']));
    }

    public function test_rejects_a_populated_tenant_without_installation_marker(): void
    {
        $this->testTenant->update(['cuenta_inicial_version' => null]);
        $this->expectException(\LogicException::class);
        app(CuentaInicialTenantSeeder::class)->__invoke();
    }

    public function test_rejects_the_central_context(): void
    {
        $connection = (new User)->getConnection();
        $connection->rollBack();
        tenancy()->end();
        try {
            app(CuentaInicialTenantSeeder::class)->__invoke();
            $this->fail('No debe sembrar en central.');
        } catch (\LogicException $exception) {
            $this->assertStringContainsString('tenant PostgreSQL', $exception->getMessage());
        } finally {
            tenancy()->initialize($this->testTenant);
            (new User)->getConnection()->beginTransaction();
        }
    }

    public function test_admin_cannot_enter_restricted_livewire_components_or_notification_controller(): void
    {
        $this->actingAs($this->account(PermisosCuentaInicial::ADMINISTRADOR));
        foreach ([AdminTiposNotificaciones::class, GestionarPlantillasInformes::class, GestionarRuedaDeLaVida::class,
            GestionarRolesPrivilegios::class, EditarPermisos::class,
            \App\Livewire\Login\PersonalizacionLogin::class] as $component) {
            Livewire::test($component)->assertForbidden();
        }
        $this->expectException(HttpException::class);
        app(NotificacionController::class)->configuracion();
    }

    public function test_rechecks_permission_on_livewire_updates(): void
    {
        $developer = $this->account(PermisosCuentaInicial::SUPERADMINISTRADOR);
        $this->actingAs($developer);
        $component = Livewire::test(AdminTiposNotificaciones::class)->assertOk();
        $role = $developer->roles()->wherePivot('activo', true)->firstOrFail();
        $role->revokePermissionTo(PermisosCuentaInicial::NOTIFICACIONES);
        $before = \App\Models\TipoNotificacion::count();
        $component->call('eliminar', \App\Models\TipoNotificacion::value('id'))->assertForbidden();
        $this->assertSame($before, \App\Models\TipoNotificacion::count());
    }

    public function test_admin_cannot_modify_hito_types_or_user_types(): void
    {
        $this->actingAs($this->account(PermisosCuentaInicial::ADMINISTRADOR));
        $controller = app(\App\Http\Controllers\TipoHitosController::class);
        $request = \Illuminate\Http\Request::create('/', 'POST');
        foreach ([['listarTipoHitos', []], ['creacionTipoHitos', []], ['crearTipoHitos', [$request]],
            ['actualizacionTipoHitos', [1]], ['actualizarTipoHitos', [$request, 1]],
            ['eliminarTipoHitos', [1]], ['toggleEstado', [1]]] as [$method, $arguments]) {
            try {
                $controller->{$method}(...$arguments);
                $this->fail('Debe rechazar '.$method);
            } catch (HttpException $exception) {
                $this->assertSame(403, $exception->getStatusCode());
            }
        }
        $middleware = app(\App\Http\Controllers\UsuarioConfiguracionController::class)->getMiddleware()[0]['middleware'];
        $this->expectException(HttpException::class);
        $middleware($request, fn () => response('No debe ejecutar la acción'));
    }

    private function account(string $role): User
    {
        return User::whereHas('roles', fn ($query) => $query->where('name', $role))->firstOrFail();
    }
}
