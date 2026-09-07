<?php

namespace Tests\Feature;

use App\Models\Grupo;
use App\Models\Role;
use App\Models\TipoUsuario;
use App\Models\User;
use App\Services\HitoTriggerService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery\MockInterface;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class UserGroupPilotTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'pilot_testing');
        config()->set('database.connections.pilot_testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);
        config()->set('permission.models.role', Role::class);

        DB::purge('pilot_testing');
        DB::setDefaultConnection('pilot_testing');

        $this->createPilotSchema();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_only_the_active_role_grants_permissions(): void
    {
        $user = $this->createUser();
        $activeRole = Role::create(['name' => 'Líder activo', 'guard_name' => 'web']);
        $inactiveRole = Role::create(['name' => 'Administrador inactivo', 'guard_name' => 'web']);
        $activePermission = Permission::create(['name' => 'grupos.lista_grupos_solo_ministerio', 'guard_name' => 'web']);
        $inactivePermission = Permission::create(['name' => 'grupos.lista_grupos_todos', 'guard_name' => 'web']);
        $directPermission = Permission::create(['name' => 'grupos.opcion_eliminar_grupo', 'guard_name' => 'web']);

        $activeRole->givePermissionTo($activePermission);
        $inactiveRole->givePermissionTo($inactivePermission);
        $user->roles()->attach($activeRole->id, ['activo' => true, 'dependiente' => true]);
        $user->roles()->attach($inactiveRole->id, ['activo' => false, 'dependiente' => false]);
        $user->givePermissionTo($directPermission);

        $this->assertTrue($user->hasPermissionTo($activePermission));
        $this->assertTrue($user->can($activePermission->name));
        $this->assertFalse($user->hasPermissionTo($inactivePermission));
        $this->assertFalse($user->can($inactivePermission->name));
        $this->assertFalse($user->hasPermissionTo($directPermission));
        $this->assertFalse($user->can($directPermission->name));
    }

    public function test_switching_roles_changes_permissions_and_preserves_other_morph_rows(): void
    {
        $user = $this->createUser();
        $firstRole = Role::create(['name' => 'Primer rol', 'guard_name' => 'web']);
        $secondRole = Role::create(['name' => 'Segundo rol', 'guard_name' => 'web']);
        $firstPermission = Permission::create(['name' => 'grupos.opcion_ver_perfil_grupo', 'guard_name' => 'web']);
        $secondPermission = Permission::create(['name' => 'grupos.opcion_modificar_grupo', 'guard_name' => 'web']);

        $firstRole->givePermissionTo($firstPermission);
        $secondRole->givePermissionTo($secondPermission);
        $user->roles()->attach($firstRole->id, ['activo' => true, 'dependiente' => true]);
        $user->roles()->attach($secondRole->id, ['activo' => false, 'dependiente' => false]);

        DB::table('model_has_roles')->insert([
            'role_id' => $firstRole->id,
            'model_type' => 'Tests\\Fixtures\\OtherAuthorizableModel',
            'model_id' => $user->id,
            'activo' => true,
            'dependiente' => false,
        ]);

        $user->switchActiveRole($secondRole);

        $this->assertFalse($user->hasPermissionTo($firstPermission));
        $this->assertTrue($user->hasPermissionTo($secondPermission));
        $this->assertDatabaseHas('model_has_roles', [
            'role_id' => $firstRole->id,
            'model_type' => 'Tests\\Fixtures\\OtherAuthorizableModel',
            'model_id' => $user->id,
            'activo' => true,
        ]);
        $this->assertSame(1, DB::table('model_has_roles')
            ->where('model_type', User::class)
            ->where('model_id', $user->id)
            ->where('activo', true)
            ->count());
        $this->assertCount(2, $user->fresh()->roles);
    }

    public function test_user_can_be_linked_once_to_a_group_and_unlinked_with_an_audit_trail(): void
    {
        $user = $this->createUser(['sede_id' => 7]);
        $grupoId = DB::table('grupos')->insertGetId([
            'nombre' => 'Grupo piloto',
            'sede_id' => 7,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $grupo = Grupo::findOrFail($grupoId);

        $this->assertTrue($user->cambiarGrupo($grupo->id));
        $this->assertNull($user->cambiarGrupo($grupo->id));
        $this->assertCount(1, $user->gruposDondeAsiste()->get());
        $this->assertDatabaseHas('bitacora_integrantes_grupo', [
            'grupo_id' => $grupo->id,
            'user_id' => $user->id,
            'estado_vinculacion' => true,
        ]);

        $this->assertTrue($user->desvincularDeGrupo($grupo->id));
        $this->assertCount(0, $user->gruposDondeAsiste()->get());
        $this->assertDatabaseHas('bitacora_integrantes_grupo', [
            'grupo_id' => $grupo->id,
            'user_id' => $user->id,
            'estado_vinculacion' => false,
        ]);
        $this->assertSame(2, DB::table('bitacora_integrantes_grupo')->count());
    }

    public function test_ministry_hierarchy_follows_member_leaders_and_stops_at_exclusions(): void
    {
        $rootLeader = $this->createUser();
        $childLeader = $this->createUser();
        $grandchildLeader = $this->createUser();
        $rootGroupId = $this->createGroup('Grupo raíz');
        $childGroupId = $this->createGroup('Grupo hijo');
        $grandchildGroupId = $this->createGroup('Grupo nieto');

        DB::table('encargados_grupo')->insert([
            ['grupo_id' => $rootGroupId, 'user_id' => $rootLeader->id],
            ['grupo_id' => $childGroupId, 'user_id' => $childLeader->id],
            ['grupo_id' => $grandchildGroupId, 'user_id' => $grandchildLeader->id],
        ]);
        DB::table('integrantes_grupo')->insert([
            ['grupo_id' => $rootGroupId, 'user_id' => $childLeader->id],
            ['grupo_id' => $childGroupId, 'user_id' => $grandchildLeader->id],
        ]);

        $this->assertEqualsCanonicalizing(
            [$rootGroupId, $childGroupId, $grandchildGroupId],
            $rootLeader->gruposMinisterio('array')
        );

        DB::table('grupos_excluidos')->insert([
            'grupo_id' => $childGroupId,
            'user_id' => $rootLeader->id,
        ]);

        $this->assertSame([$rootGroupId], $rootLeader->gruposMinisterio('array'));
    }

    public function test_report_attendance_is_preserved_independently_from_current_membership(): void
    {
        $user = $this->createUser();
        $grupoId = $this->createGroup('Grupo con reporte');
        $reporteId = DB::table('reporte_grupos')->insertGetId([
            'grupo_id' => $grupoId,
            'fecha' => '2026-09-04',
        ]);

        DB::table('integrantes_grupo')->insert([
            'grupo_id' => $grupoId,
            'user_id' => $user->id,
        ]);
        DB::table('asistencia_grupos')->insert([
            'user_id' => $user->id,
            'reporte_grupo_id' => $reporteId,
            'asistio' => false,
            'observaciones' => 'Ausencia justificada',
        ]);

        $reporte = $user->reportesGrupo()->firstOrFail();
        $this->assertFalse((bool) $reporte->pivot->asistio);
        $this->assertSame('Ausencia justificada', $reporte->pivot->observaciones);

        DB::table('integrantes_grupo')->where('user_id', $user->id)->delete();

        $this->assertFalse($user->gruposDondeAsiste()->exists());
        $this->assertTrue($user->reportesGrupo()->whereKey($reporteId)->exists());
    }

    public function test_group_membership_and_leadership_dispatch_hitos_only_once(): void
    {
        $member = $this->createUser(['sede_id' => 7]);
        $leader = $this->createUser(['sede_id' => 7]);
        $grupoId = DB::table('grupos')->insertGetId([
            'nombre' => 'Grupo con hitos',
            'sede_id' => 7,
            'tipo_grupo_id' => 12,
        ]);
        $grupo = Grupo::findOrFail($grupoId);

        $this->mock(HitoTriggerService::class, function (MockInterface $mock) use ($grupo, $leader, $member): void {
            $mock->shouldReceive('onAsignacionGrupoIntegrante')
                ->once()
                ->with($member->id, 12, $grupo->id)
                ->andReturn([]);
            $mock->shouldReceive('onDesignacionLiderGrupo')
                ->once()
                ->with($leader->id, 12, $grupo->id)
                ->andReturn([]);
        });

        $this->assertTrue($member->cambiarGrupo($grupo->id));
        $this->assertNull($member->cambiarGrupo($grupo->id));
        $this->assertSame('true', $grupo->asignarEncargado($leader->id));
        $this->assertSame('false', $grupo->asignarEncargado($leader->id));
        $this->assertSame(1, DB::table('integrantes_grupo')->where('grupo_id', $grupo->id)->count());
        $this->assertSame(1, DB::table('encargados_grupo')->where('grupo_id', $grupo->id)->count());
    }

    public function test_group_automation_promotes_type_and_dependent_role_without_touching_other_models(): void
    {
        $sourceRole = Role::create(['name' => 'Rol inicial', 'guard_name' => 'web', 'dependiente' => true]);
        $targetRole = Role::create(['name' => 'Rol promovido', 'guard_name' => 'web', 'dependiente' => true]);
        $independentRole = Role::create(['name' => 'Rol adicional', 'guard_name' => 'web', 'dependiente' => false]);
        $sourceType = TipoUsuario::create([
            'nombre' => 'Tipo inicial',
            'puntaje' => 1,
            'id_rol_dependiente' => $sourceRole->id,
        ]);
        $targetType = TipoUsuario::create([
            'nombre' => 'Tipo promovido',
            'puntaje' => 10,
            'id_rol_dependiente' => $targetRole->id,
        ]);
        $user = $this->createUser(['tipo_usuario_id' => $sourceType->id]);
        $user->roles()->attach($sourceRole->id, ['activo' => true, 'dependiente' => true]);
        $user->roles()->attach($independentRole->id, ['activo' => false, 'dependiente' => false]);
        DB::table('model_has_roles')->insert([
            'role_id' => $targetRole->id,
            'model_type' => 'Tests\\Fixtures\\OtherAuthorizableModel',
            'model_id' => $user->id,
            'activo' => true,
            'dependiente' => true,
        ]);

        $this->assertTrue($user->promoverTipoUsuario($targetType));
        $this->assertSame($targetType->id, $user->fresh()->tipo_usuario_id);
        $this->assertDatabaseMissing('model_has_roles', [
            'role_id' => $sourceRole->id,
            'model_type' => User::class,
            'model_id' => $user->id,
        ]);
        $this->assertDatabaseHas('model_has_roles', [
            'role_id' => $targetRole->id,
            'model_type' => User::class,
            'model_id' => $user->id,
            'activo' => true,
            'dependiente' => true,
        ]);
        $this->assertDatabaseHas('model_has_roles', [
            'role_id' => $independentRole->id,
            'model_type' => User::class,
            'model_id' => $user->id,
            'activo' => false,
            'dependiente' => false,
        ]);
        $this->assertDatabaseHas('model_has_roles', [
            'role_id' => $targetRole->id,
            'model_type' => 'Tests\\Fixtures\\OtherAuthorizableModel',
            'model_id' => $user->id,
            'activo' => true,
        ]);
        $this->assertFalse($user->fresh()->promoverTipoUsuario($sourceType));
        $this->assertSame($targetType->id, $user->fresh()->tipo_usuario_id);
    }

    private function createUser(array $attributes = []): User
    {
        return User::create(array_merge([
            'email' => fake()->unique()->safeEmail(),
            'password' => 'password-seguro',
            'primer_nombre' => 'Usuario',
            'primer_apellido' => 'Piloto',
        ], $attributes));
    }

    private function createGroup(string $name): int
    {
        return DB::table('grupos')->insertGetId([
            'nombre' => $name,
        ]);
    }

    private function createPilotSchema(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('primer_nombre')->nullable();
            $table->string('primer_apellido')->nullable();
            $table->unsignedBigInteger('sede_id')->nullable();
            $table->unsignedBigInteger('tipo_usuario_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('roles', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('guard_name');
            $table->boolean('dependiente')->default(false);
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        Schema::create('tipo_usuarios', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre');
            $table->integer('puntaje')->default(0);
            $table->unsignedBigInteger('id_rol_dependiente')->nullable();
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        Schema::create('model_has_permissions', function (Blueprint $table): void {
            $table->unsignedBigInteger('permission_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->primary(['permission_id', 'model_id', 'model_type']);
        });

        Schema::create('model_has_roles', function (Blueprint $table): void {
            $table->unsignedBigInteger('role_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->boolean('activo')->nullable();
            $table->boolean('dependiente')->nullable();
            $table->primary(['role_id', 'model_id', 'model_type']);
        });

        Schema::create('role_has_permissions', function (Blueprint $table): void {
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('role_id');
            $table->primary(['permission_id', 'role_id']);
        });

        Schema::create('grupos', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre');
            $table->unsignedBigInteger('sede_id')->nullable();
            $table->unsignedBigInteger('tipo_grupo_id')->nullable();
            $table->unsignedBigInteger('usuario_creacion_id')->nullable();
            $table->timestamps();
        });

        Schema::create('integrantes_grupo', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('grupo_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
            $table->unique(['grupo_id', 'user_id']);
        });

        Schema::create('encargados_grupo', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('grupo_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
        });

        Schema::create('grupos_excluidos', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('grupo_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
        });

        Schema::create('reporte_grupos', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('grupo_id');
            $table->date('fecha');
            $table->timestamps();
        });

        Schema::create('asistencia_grupos', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('reporte_grupo_id');
            $table->boolean('asistio');
            $table->text('observaciones')->nullable();
            $table->unsignedBigInteger('tipo_inasistencia_id')->nullable();
            $table->timestamps();
        });

        Schema::create('bitacora_integrantes_grupo', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('grupo_id');
            $table->unsignedBigInteger('user_id');
            $table->boolean('estado_vinculacion');
            $table->unsignedBigInteger('autor_id')->nullable();
            $table->timestamps();
        });

        Schema::create('bitacora_sedes', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('sede_id_anterior')->nullable();
            $table->unsignedBigInteger('sede_id_nuevo')->nullable();
            $table->unsignedBigInteger('autor_id')->nullable();
            $table->timestamps();
        });

        Schema::create('bitacora_tipos_usuarios', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('tipo_usuario_id_anterior')->nullable();
            $table->unsignedBigInteger('tipo_usuario_id_nuevo')->nullable();
            $table->unsignedBigInteger('autor_id')->nullable();
            $table->timestamps();
        });
    }
}
