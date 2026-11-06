<?php

namespace Tests\Feature;

use App\Models\Grupo;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MinisterioConsultasTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['database.connections.ministerio_testing' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => false,
        ]]);
        DB::setDefaultConnection('ministerio_testing');
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            foreach (['primer_nombre', 'primer_apellido', 'segundo_nombre', 'segundo_apellido', 'email', 'password', 'remember_token', 'foto', 'telefono_movil'] as $column) {
                $table->string($column)->nullable();
            }
            $table->integer('tipo_usuario_id')->nullable();
            $table->integer('sede_id')->nullable();
            $table->integer('genero')->nullable();
            $table->boolean('activo')->default(true);
            $table->date('fecha_nacimiento')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('grupos', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre')->nullable();
            $table->integer('tipo_grupo_id')->nullable();
            $table->integer('sede_id')->nullable();
            $table->integer('usuario_creacion_id')->nullable();
            $table->boolean('dado_baja')->default(false);
            $table->timestamps();
        });
        foreach (['integrantes_grupo', 'encargados_grupo', 'grupos_excluidos'] as $name) {
            Schema::create($name, function (Blueprint $table): void {
                $table->id();
                $table->integer('grupo_id');
                $table->integer('user_id');
                $table->timestamps();
            });
        }
        Schema::create('tipo_usuarios', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre');
            $table->string('color')->nullable();
            $table->string('icono')->nullable();
        });
        Schema::create('sedes', function (Blueprint $table): void {
            $table->id();
        });
        Schema::create('roles', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('guard_name');
            $table->integer('lista_asistentes_sede_id')->nullable();
            $table->integer('lista_grupos_sede_id')->nullable();
        });
        Schema::create('model_has_roles', function (Blueprint $table): void {
            $table->integer('role_id');
            $table->integer('model_id');
            $table->string('model_type');
            $table->boolean('activo');
            $table->boolean('dependiente')->default(false);
        });
        DB::table('tipo_usuarios')->insert(['id' => 1, 'nombre' => 'Líder', 'color' => 'verde', 'icono' => 'ti-users']);
    }

    protected function tearDown(): void
    {
        DB::purge('ministerio_testing');
        parent::tearDown();
    }

    private function persona(int $id, bool $eliminada = false): User
    {
        return User::withoutEvents(fn (): User => User::factory()->create([
            'id' => $id, 'primer_nombre' => 'Persona', 'primer_apellido' => (string) $id,
            'deleted_at' => $eliminada ? now() : null,
        ]));
    }

    private function grupo(int $id, ?int $sedeId = null): Grupo
    {
        return Grupo::withoutEvents(fn (): Grupo => Grupo::query()->create([
            'id' => $id, 'nombre' => 'Grupo '.$id, 'sede_id' => $sedeId,
        ]));
    }

    private function vincular(string $table, int $personaId, int $grupoId): void
    {
        DB::table($table)->insert(['user_id' => $personaId, 'grupo_id' => $grupoId]);
    }

    public function test_encargados_directos_son_usuarios_notificables_y_respetan_eliminados(): void
    {
        $persona = $this->persona(1);
        $this->persona(2);
        $this->persona(3, true);
        $grupo = $this->grupo(10);
        $this->vincular('integrantes_grupo', 1, 10);
        foreach ([2, 3] as $id) {
            $this->vincular('encargados_grupo', $id, 10);
        }

        $directos = $persona->encargadosDirectos();
        $this->assertEqualsCanonicalizing([2, 3], $directos->pluck('id')->all());
        $this->assertInstanceOf(User::class, $directos->first());
        $this->assertTrue(method_exists($directos->first(), 'routeNotificationFor'));
        $this->assertSame('Persona 2', $directos->firstWhere('id', 2)->nombre);
        $this->assertSame('Líder', $directos->firstWhere('id', 2)->tipo_usuario);
        $this->assertSame([2], $grupo->encargadosDirectos()->pluck('id')->all());
    }

    public function test_tarjetas_precargadas_no_hacen_consultas_por_fila(): void
    {
        $this->persona(90);
        $this->persona(91, true);
        $this->grupo(10);
        foreach ([90, 91] as $id) {
            $this->vincular('encargados_grupo', $id, 10);
        }
        foreach (range(1, 12) as $id) {
            $this->persona($id);
            $this->vincular('integrantes_grupo', $id, 10);
        }
        $personas = User::query()->whereBetween('id', [1, 12])->with([
            'roles', 'tipoUsuario', 'gruposDondeAsiste.encargadosConEliminados.tipoUsuario',
        ])->get();
        $grupos = Grupo::query()->with('encargados.tipoUsuario')->withCount('asistentes')->get();
        DB::enableQueryLog();
        DB::flushQueryLog();
        foreach ($personas as $persona) {
            $this->assertEqualsCanonicalizing([90, 91], $persona->encargadosDirectos()->pluck('id')->all());
            $this->assertCount(0, $persona->roles);
        }
        foreach ($grupos as $grupo) {
            $this->assertSame(12, $grupo->asistentes_count);
            $this->assertSame([90], $grupo->encargadosDirectos()->pluck('id')->all());
        }
        $this->assertSame([], DB::getQueryLog());
        $original = $personas->first()->gruposDondeAsiste->first()->encargadosConEliminados->first();
        $this->assertArrayNotHasKey('nombre', $original->getAttributes());
        $this->assertFalse($original->isDirty());
    }

    public function test_ascenso_con_ciclos_ramas_compartidas_y_exclusiones_al_final(): void
    {
        foreach (range(1, 5) as $id) {
            $this->persona($id, $id === 5);
        }
        foreach ([10, 20, 30, 40] as $id) {
            $this->grupo($id);
        }
        foreach ([[1, 10], [2, 20], [3, 30], [4, 40]] as [$persona, $grupo]) {
            $this->vincular('integrantes_grupo', $persona, $grupo);
        }
        foreach ([[2, 10], [3, 10], [5, 10], [4, 20], [4, 30], [2, 40]] as [$persona, $grupo]) {
            $this->vincular('encargados_grupo', $persona, $grupo);
        }
        $this->vincular('grupos_excluidos', 3, 10);
        $this->vincular('integrantes_grupo', 1, 10);
        $this->vincular('integrantes_grupo', 2, 999);

        $persona = User::findOrFail(1);
        $this->assertEqualsCanonicalizing([2, 4], $persona->lideres('array'));
        $this->assertEqualsCanonicalizing([2, 4], $persona->lideres()->pluck('users.id')->all());
        $this->assertSame([], User::withTrashed()->findOrFail(5)->lideres('array'));
    }

    public function test_raices_comparten_consultas_sin_compartir_exclusiones(): void
    {
        foreach (range(1, 4) as $id) {
            $this->persona($id);
        }
        foreach ([10, 20, 30, 100] as $id) {
            $this->grupo($id);
        }
        foreach ([[1, 10], [2, 20], [3, 30]] as [$persona, $grupo]) {
            $this->vincular('integrantes_grupo', $persona, $grupo);
        }
        foreach ([[3, 10], [3, 20], [4, 30], [1, 100], [2, 100]] as [$persona, $grupo]) {
            $this->vincular('encargados_grupo', $persona, $grupo);
        }
        $this->vincular('grupos_excluidos', 4, 20);
        $resultados = User::idsLideresPorUsuarios([1, 2]);
        $this->assertEqualsCanonicalizing([3, 4], $resultados[1]);
        $this->assertSame([3], $resultados[2]);
        $this->assertEqualsCanonicalizing([10, 20, 30], Grupo::findOrFail(100)->gruposAscendentes('array'));
        $this->assertSame([], User::idsLideresPorUsuarios([]));
    }

    public function test_retorno_de_grupos_por_sede_es_un_builder_y_mantiene_la_regla_actual(): void
    {
        $persona = $this->persona(1);
        foreach ([14, 15] as $id) {
            DB::table('sedes')->insert(['id' => $id]);
            $this->grupo($id, $id);
        }
        DB::table('roles')->insert([
            'id' => 1, 'name' => 'Sede', 'guard_name' => 'web',
            'lista_asistentes_sede_id' => 14, 'lista_grupos_sede_id' => 15,
        ]);
        DB::table('model_has_roles')->insert([
            'role_id' => 1, 'model_id' => 1, 'model_type' => User::class, 'activo' => true,
        ]);
        $this->assertInstanceOf(Builder::class, $persona->gruposMinisterio());
        $this->assertSame([15], $persona->gruposMinisterio()->pluck('grupos.id')->all());
        $this->assertSame([15], $persona->gruposMinisterio('array'));
        DB::table('roles')->where('id', 1)->update(['lista_grupos_sede_id' => null]);
        $this->assertSame([], $persona->gruposMinisterio()->pluck('grupos.id')->all());
    }

    public function test_ministerio_ids_conserva_raiz_multiples_grupos_y_eliminados(): void
    {
        $this->persona(1);
        $this->persona(2, true);
        $this->persona(3);
        $grupo = $this->grupo(10);
        $this->grupo(20);
        foreach ([[1, 10], [2, 10], [3, 10], [3, 20]] as [$persona, $idGrupo]) {
            $this->vincular('integrantes_grupo', $persona, $idGrupo);
        }
        $this->vincular('encargados_grupo', 1, 20);
        $this->assertEqualsCanonicalizing([1, 3], $grupo->ministerioDelGrupo('array'));
        $this->assertEqualsCanonicalizing([1, 2, 3], $grupo->ministerioDelGrupo('array', 'todos'));
        $this->assertSame([2], $grupo->ministerioDelGrupo('array', 'solo-eliminados'));
        $this->assertSame([3], $grupo->ministerioDelGrupo('array', 'sin-eliminados', false));
        $this->assertEqualsCanonicalizing($grupo->ministerioDelGrupo()->pluck('id')->all(), $grupo->ministerioDelGrupo('array'));
    }

    public function test_indices_se_revierten_sin_modificar_registros(): void
    {
        $this->vincular('integrantes_grupo', 1, 10);
        $this->vincular('integrantes_grupo', 1, 10);
        $migration = require glob(dirname(__DIR__, 2).'/database/migrations/tenant/*add_ministry_traversal_indexes_to_group_tables.php')[0];
        $migration->up();
        $this->assertTrue(Schema::hasIndex('integrantes_grupo', ['grupo_id', 'user_id']));
        $this->assertTrue(Schema::hasIndex('integrantes_grupo', ['user_id', 'grupo_id']));
        $this->assertTrue(Schema::hasIndex('encargados_grupo', ['grupo_id', 'user_id']));
        $this->assertTrue(Schema::hasIndex('encargados_grupo', ['user_id', 'grupo_id']));
        $this->assertTrue(Schema::hasIndex('grupos_excluidos', ['user_id', 'grupo_id']));
        $migration->down();
        $this->assertFalse(Schema::hasIndex('integrantes_grupo', ['grupo_id', 'user_id']));
        $this->assertSame(2, DB::table('integrantes_grupo')->count());
    }
}
