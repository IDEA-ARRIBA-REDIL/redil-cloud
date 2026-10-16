<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\TareaConsolidacion;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CpanelReleaseSchemaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'release_testing');
        config()->set('database.connections.release_testing', [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
            'foreign_key_constraints' => true,
        ]);
        DB::purge('release_testing');
        DB::setDefaultConnection('release_testing');

        Schema::create('tipo_identificaciones', function (Blueprint $table): void {
            $table->id();
            $table->string('abreviatura');
        });
        Schema::create('tareas_consolidacion', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre');
            $table->boolean('default')->default(false);
            $table->timestamps();
        });
        Schema::create('roles', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
        });
    }

    public function test_migrations_preserve_existing_records_with_compatible_defaults(): void
    {
        DB::table('tipo_identificaciones')->insert(['abreviatura' => 'CC']);
        DB::table('tareas_consolidacion')->insert(['nombre' => 'Tarea existente']);

        $this->applyReleaseMigrations();

        $this->assertDatabaseCount('tipo_identificaciones', 1);
        $this->assertDatabaseCount('tareas_consolidacion', 1);
        $this->assertDatabaseHas('tipo_identificaciones', [
            'abreviatura' => 'CC', 'tipo_caracteres' => 'solo_numeros', 'forzar_mayusculas' => true,
        ]);
        $this->assertDatabaseHas('tareas_consolidacion', ['nombre' => 'Tarea existente', 'todos_los_roles' => true]);
        $this->assertTrue(Schema::hasTable('rol_tarea_consolidacion'));
    }

    public function test_task_scope_and_authorization_agree_for_allowed_denied_and_missing_roles(): void
    {
        $this->applyReleaseMigrations();
        $role = Role::query()->create(['name' => 'Permitido', 'guard_name' => 'web']);
        $otherRole = Role::query()->create(['name' => 'Ajeno', 'guard_name' => 'web']);
        $publicTask = TareaConsolidacion::query()->create(['nombre' => 'General', 'todos_los_roles' => true]);
        $restrictedTask = TareaConsolidacion::query()->create(['nombre' => 'Restringida', 'todos_los_roles' => false]);
        $restrictedTask->roles()->attach($role->id);

        $this->assertEqualsCanonicalizing([$publicTask->id, $restrictedTask->id], TareaConsolidacion::permitidasParaRol($role)->pluck('id')->all());
        $this->assertSame([$publicTask->id], TareaConsolidacion::permitidasParaRol($otherRole)->pluck('id')->all());
        $this->assertSame(0, TareaConsolidacion::permitidasParaRol(null)->count());
        $this->assertTrue($restrictedTask->puedeSerGestionadaPorRol($role));
        $this->assertFalse($restrictedTask->puedeSerGestionadaPorRol($otherRole));
        $this->assertFalse($publicTask->puedeSerGestionadaPorRol(null));
        $restrictedTask->load('roles');
        $this->assertTrue($restrictedTask->puedeSerGestionadaPorRol($role));
        $this->assertFalse($restrictedTask->puedeSerGestionadaPorRol($otherRole));
    }

    private function applyReleaseMigrations(): void
    {
        foreach ([
            '2026_10_13_000001_add_formato_to_tipo_identificaciones_table.php',
            '2026_10_14_201500_add_roles_configuration_to_tareas_consolidacion_table.php',
        ] as $filename) {
            $migration = require database_path('migrations/tenant/'.$filename);
            $migration->up();
        }
    }
}
