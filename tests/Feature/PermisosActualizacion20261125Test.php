<?php

namespace Tests\Feature;

use Database\Seeders\AgregarPermisosActualizacion20261125Seeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PermisosActualizacion20261125Test extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'actualizacion_testing',
            'database.connections.actualizacion_testing' => [
                'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => true,
            ],
            'cache.default' => 'array',
            'permission.cache.store' => 'array',
        ]);
        DB::purge('actualizacion_testing');
        DB::setDefaultConnection('actualizacion_testing');
        (require database_path('migrations/tenant/2023_11_17_223636_create_permission_tables.php'))->up();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_agrega_permisos_sin_reemplazar_metadatos_ni_asignaciones_y_es_idempotente(): void
    {
        $administrador = Role::create(['name' => 'Super Administrador', 'guard_name' => 'web']);
        $editor = Role::create(['name' => 'Editor de sedes', 'guard_name' => 'web']);
        $otro = Role::create(['name' => 'Rol personalizado', 'guard_name' => 'web']);
        $existente = Permission::create(['name' => 'dashboard.ver_cumpleanos', 'guard_name' => 'web', 'titulo' => 'Título del cliente', 'descripcion' => 'Descripción del cliente']);
        $restriccion = Permission::create(['name' => 'personas.lista_asistentes_solo_ministerio', 'guard_name' => 'web']);
        $editarSedes = Permission::create(['name' => 'sedes.opcion_modificar_sede', 'guard_name' => 'web']);
        $administrador->givePermissionTo($restriccion);
        $otro->givePermissionTo($existente);
        $editor->givePermissionTo($editarSedes);
        DB::table('model_has_roles')->insert(['role_id' => $administrador->id, 'model_id' => 14, 'model_type' => 'App\\Models\\User']);
        DB::table('model_has_permissions')->insert(['permission_id' => $existente->id, 'model_id' => 14, 'model_type' => 'App\\Models\\User']);
        $permisosAnteriores = Permission::query()->orderBy('id')->get()->toArray();
        $asignacionesAnteriores = DB::table('role_has_permissions')->get()->map(fn ($row) => (array) $row)->all();
        $rolesAnteriores = Role::query()->orderBy('id')->get()->toArray();
        $usuariosRoles = DB::table('model_has_roles')->get()->toArray();
        $usuariosPermisos = DB::table('model_has_permissions')->get()->toArray();

        app(AgregarPermisosActualizacion20261125Seeder::class)->run();
        $asignacionesPrimeraEjecucion = DB::table('role_has_permissions')->orderBy('role_id')->orderBy('permission_id')->get()->toArray();
        app(AgregarPermisosActualizacion20261125Seeder::class)->run();

        foreach ($permisosAnteriores as $permiso) {
            $this->assertSame($permiso, Permission::findOrFail($permiso['id'])->toArray());
        }
        foreach ($asignacionesAnteriores as $asignacion) {
            $this->assertDatabaseHas('role_has_permissions', $asignacion);
        }
        $this->assertSame($rolesAnteriores, Role::query()->orderBy('id')->get()->toArray());
        $this->assertEquals($usuariosRoles, DB::table('model_has_roles')->get()->toArray());
        $this->assertEquals($usuariosPermisos, DB::table('model_has_permissions')->get()->toArray());
        $this->assertEquals($asignacionesPrimeraEjecucion, DB::table('role_has_permissions')->orderBy('role_id')->orderBy('permission_id')->get()->toArray());
        $this->assertTrue($administrador->fresh()->hasPermissionTo('configuraciones.subitem_acciones_dashboard'));
        $this->assertTrue($administrador->fresh()->hasPermissionTo($restriccion));
        $this->assertTrue($editor->fresh()->hasPermissionTo('sedes.opcion_cobertura_geografica_sede'));
        $this->assertFalse($otro->fresh()->hasPermissionTo('sedes.opcion_cobertura_geografica_sede'));
        $this->assertSame(5, Permission::query()->count());
    }

    public function test_no_crea_roles_ni_asigna_permisos_a_otro_guard(): void
    {
        $api = Role::create(['name' => 'Super Administrador', 'guard_name' => 'api']);

        app(AgregarPermisosActualizacion20261125Seeder::class)->run();

        $this->assertSame(1, Role::query()->count());
        $this->assertSame(0, $api->fresh()->permissions()->count());
        $this->assertSame(3, Permission::query()->where('guard_name', 'web')->count());
    }

    public function test_las_migraciones_conservan_los_permisos_y_roles_existentes(): void
    {
        $rol = Role::create(['name' => 'Super Administrador', 'guard_name' => 'web']);
        foreach (['grupos.lista_grupos_todos', 'personas.lista_asistentes_solo_ministerio', 'sedes.opcion_cobertura_geografica_sede'] as $nombre) {
            $rol->givePermissionTo(Permission::create(['name' => $nombre, 'guard_name' => 'web']));
        }
        $antes = $this->estadoPermisos();
        foreach (['tipo_usuarios', 'paises', 'municipios', 'sedes'] as $tabla) {
            Schema::create($tabla, function (Blueprint $table): void {
                $table->id();
            });
        }
        $acciones = require database_path('migrations/tenant/2026_11_17_111500_create_acciones_dashboard_tables.php');
        $cobertura = require database_path('migrations/tenant/2026_11_24_110000_add_cobertura_geografica_to_sedes_table.php');
        $grupos = require database_path('migrations/tenant/2026_11_24_222000_revoke_solo_ministerio_from_global_admin_roles.php');

        $acciones->up();
        $cobertura->up();
        $grupos->up();
        $this->assertSame($antes, $this->estadoPermisos());
        $this->assertTrue(Schema::hasColumn('sedes', 'modo_municipios'));
        $grupos->down();
        $cobertura->down();
        $acciones->down();
        $this->assertSame($antes, $this->estadoPermisos());
    }

    private function estadoPermisos(): array
    {
        return [
            'roles' => Role::query()->orderBy('id')->get()->toArray(),
            'permisos' => Permission::query()->orderBy('id')->get()->toArray(),
            'asignaciones' => DB::table('role_has_permissions')->orderBy('role_id')->orderBy('permission_id')->get()->map(fn ($row) => (array) $row)->all(),
        ];
    }
}
