<?php

namespace Tests\Feature;

use Database\Seeders\AgregarPermisosConfiguracionSeeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AgregarPermisosConfiguracionSeederTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => true],
            'cache.default' => 'array',
            'permission.cache.store' => 'array',
        ]);
        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');
        Cache::flush();
        (require database_path('migrations/tenant/2023_11_17_223636_create_permission_tables.php'))->up();
    }

    public function test_it_adds_only_six_permissions_to_both_existing_admin_roles_without_removing_other_assignments(): void
    {
        $real = Role::create(['name' => 'Super Administrador', 'guard_name' => 'web']);
        $prueba = Role::create(['name' => 'Super Administrador Prueba', 'guard_name' => 'web']);
        $existente = Permission::create(['name' => 'configuraciones.existente', 'guard_name' => 'web']);
        $existente->assignRole($real, $prueba);

        app(AgregarPermisosConfiguracionSeeder::class)->run();
        app(AgregarPermisosConfiguracionSeeder::class)->run();

        $this->assertSame(7, Permission::query()->count());
        $this->assertSame(7, $real->fresh()->permissions()->count());
        $this->assertSame(7, $prueba->fresh()->permissions()->count());
        $this->assertTrue($prueba->fresh()->hasPermissionTo('configuraciones.subitem_profesiones'));
        $this->assertTrue($existente->fresh()->hasRole('Super Administrador'));
        $this->assertTrue($existente->fresh()->hasRole('Super Administrador Prueba'));
    }

    public function test_it_uses_the_existing_trial_admin_when_the_real_admin_does_not_exist(): void
    {
        $prueba = Role::create(['name' => 'Super Administrador Prueba', 'guard_name' => 'web']);

        app(AgregarPermisosConfiguracionSeeder::class)->run();

        $this->assertSame(1, Role::query()->count());
        $this->assertSame(6, Permission::query()->count());
        $this->assertSame(6, $prueba->fresh()->permissions()->count());
    }

    public function test_it_does_not_create_a_role_or_permission_when_no_admin_role_exists(): void
    {
        try {
            app(AgregarPermisosConfiguracionSeeder::class)->run();
            $this->fail('El seeder debió exigir un rol administrativo existente.');
        } catch (\LogicException $exception) {
            $this->assertSame(0, Role::query()->count());
            $this->assertSame(0, Permission::query()->count());
        }
    }
}
