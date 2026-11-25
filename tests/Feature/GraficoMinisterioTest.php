<?php

namespace Tests\Feature;

use App\Http\Controllers\GrupoController;
use App\Http\Requests\GraficoMinisterioRequest;
use App\Models\Grupo;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class GraficoMinisterioTest extends TestCase
{
    private User $operador;

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
        Schema::table('users', function (Blueprint $table): void {
            $table->integer('indice_grafico_ministerial')->default(0);
            $table->boolean('esta_aprobado')->default(true);
        });
        Schema::table('grupos', function (Blueprint $table): void {
            $table->string('codigo')->nullable();
            $table->integer('indice_grafico_ministerial')->default(0);
        });
        Schema::table('sedes', function (Blueprint $table): void {
            $table->integer('grupo_id')->nullable();
        });
        Schema::table('roles', function (Blueprint $table): void {
            $table->timestamps();
        });
        Schema::create('permissions', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
        });
        Schema::create('role_has_permissions', function (Blueprint $table): void {
            $table->integer('role_id');
            $table->integer('permission_id');
        });
        Schema::create('configuraciones', function (Blueprint $table): void {
            $table->id();
            $table->integer('maximos_niveles_grafico_ministerio')->default(2);
        });
        Schema::create('tipo_grupos', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre');
            $table->string('color');
        });
        Schema::create('reporte_grupos', function (Blueprint $table): void {
            $table->id();
            $table->integer('grupo_id');
            $table->date('fecha');
            $table->boolean('finalizado');
        });
        Schema::create('formularios_usuario', function (Blueprint $table): void {
            $table->id();
            $table->integer('tipo_formulario_id');
            $table->integer('edad_minima');
            $table->integer('edad_maxima');
            $table->string('label');
            $table->softDeletes();
        });
        Schema::create('formulario_usuario_rol', function (Blueprint $table): void {
            $table->integer('rol_id');
            $table->integer('formulario_usuario_id');
            $table->timestamps();
        });
        DB::table('configuraciones')->insert(['id' => 1]);
        DB::table('tipo_grupos')->insert(['id' => 1, 'nombre' => 'Grupo', 'color' => '#00ff00']);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $permisos = [
            'grupos.subitem_grafico_ministerio', 'grupos.grafico_ministerio_todos',
            'grupos.grafico_ministerio_solo_ministerio', 'consejeria.opcion_agendar_cita',
            'personas.opcion_dar_de_alta_asistente', 'personas.privilegio_modificar_asistentes_desaprobados',
            'personas.opcion_cambiar_contrasena_asistente', 'personas.opcion_descargar_qr',
            'personas.opcion_dar_de_baja_asistente', 'personas.opcion_eliminar_asistente',
            'personas.eliminar_asistentes_forzadamente', 'grupos.opcion_ver_perfil_grupo',
            'grupos.opcion_modificar_grupo', 'grupos.opcion_anadir_lideres_grupo',
            'grupos.opcion_anadir_integrantes_grupo', 'grupos.opcion_georreferencia_grupo',
            'grupos.opcion_excluir_grupo', 'grupos.opcion_ver_informes_evidencia',
            'grupos.opcion_dar_de_baja_alta_grupo', 'grupos.opcion_eliminar_grupo',
        ];
        foreach ($permisos as $permiso) {
            Permission::create(['name' => $permiso, 'guard_name' => 'web']);
        }
        $rol = Role::create(['name' => 'Líder', 'guard_name' => 'web']);
        $rol->givePermissionTo('grupos.subitem_grafico_ministerio');
        $this->operador = $this->persona(1);
        $this->operador->roles()->attach($rol, ['activo' => true]);
        $this->actingAs($this->operador);

        Route::get('/prueba-grafico/{idNodo}/{niveles}', function (GraficoMinisterioRequest $request, string $idNodo, int $niveles): JsonResponse {
            $resultado = app(GrupoController::class)->graficoDelMinisterio($request, $idNodo, $niveles);
            if ($resultado instanceof JsonResponse) {
                return $resultado;
            }

            $this->assertSame('contenido.paginas.grupos.grafico-del-ministerio', $resultado->name());

            return response()->json($resultado->getData());
        });
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
            'fecha_nacimiento' => '1990-06-28', 'deleted_at' => $eliminada ? now() : null,
        ]));
    }

    private function grupo(int $id, int $encargado): Grupo
    {
        $grupo = Grupo::withoutEvents(fn (): Grupo => Grupo::query()->create([
            'id' => $id, 'nombre' => 'Grupo '.$id, 'tipo_grupo_id' => 1,
        ]));
        $grupo->encargados()->attach($encargado);

        return $grupo;
    }

    private function cadena(int $niveles): void
    {
        foreach (range(1, $niveles) as $nivel) {
            $this->persona($nivel + 1);
            $this->grupo($nivel * 10, $nivel)->asistentes()->attach($nivel + 1);
        }
    }

    public function test_la_carga_inicial_respeta_niveles_y_no_recorre_la_cobertura_completa(): void
    {
        $this->cadena(3);
        DB::enableQueryLog();
        DB::flushQueryLog();
        $corta = $this->getJson('/prueba-grafico/G-10/2')->assertOk();
        $consultasCortas = count(DB::getQueryLog());
        $this->assertSame(['A-1', 'G-10', 'A-2', 'G-20'], array_column($corta->json('nodos'), 'id'));
        $this->assertArrayNotHasKey('totalPersonas', $corta->json());
        $this->assertStringContainsString('G-10/2?solo_totales=1', $corta->json('urlTotales'));

        foreach (range(4, 15) as $nivel) {
            $this->persona($nivel + 1);
            $this->grupo($nivel * 10, $nivel)->asistentes()->attach($nivel + 1);
        }
        DB::flushQueryLog();
        $larga = $this->getJson('/prueba-grafico/G-10/2')->assertOk();
        $this->assertLessThanOrEqual($consultasCortas, count(DB::getQueryLog()));
        $this->assertSame($corta->json('nodos'), $larga->json('nodos'));
    }

    public function test_los_totales_diferidos_conservan_cobertura_y_no_cargan_menus(): void
    {
        $this->cadena(5);
        DB::enableQueryLog();
        DB::flushQueryLog();
        $this->getJson('/prueba-grafico/G-10/2?solo_totales=1')->assertOk()
            ->assertExactJson(['totalPersonas' => 6, 'totalGrupos' => 5]);
        $sql = implode(' ', array_column(DB::getQueryLog(), 'query'));
        $this->assertStringNotContainsString('formularios_usuario', $sql);
        $this->assertStringNotContainsString('reporte_grupos', $sql);
        $this->assertStringNotContainsString('grupos_excluidos', $sql);
    }

    public function test_ciclos_no_duplican_personas_grupos_ni_aristas(): void
    {
        $this->persona(2);
        $grupo = $this->grupo(10, 1);
        $grupo->asistentes()->attach([1, 2]);
        $grupo->asistentes()->attach(2);
        $datos = $this->getJson('/prueba-grafico/G-10/20')->assertOk()->json();
        $this->assertSame(['A-1', 'G-10', 'A-2'], array_column($datos['nodos'], 'id'));
        $this->assertCount(3, $datos['aristas']);
        $this->getJson('/prueba-grafico/G-10/2?solo_totales=1')->assertOk()
            ->assertExactJson(['totalPersonas' => 2, 'totalGrupos' => 1]);
    }

    public function test_los_totales_conservan_eliminados_y_exclusiones_del_dibujo(): void
    {
        $this->persona(2, true);
        $this->persona(3);
        $this->grupo(10, 1)->asistentes()->attach(2);
        $this->grupo(20, 2)->asistentes()->attach(3);
        $inactivo = $this->grupo(30, 3);
        $inactivo->dado_baja = true;
        $inactivo->saveQuietly();
        DB::table('grupos_excluidos')->insert(['user_id' => 1, 'grupo_id' => 10]);
        $this->getJson('/prueba-grafico/G-10/2?solo_totales=1')->assertOk()
            ->assertExactJson(['totalPersonas' => 3, 'totalGrupos' => 2]);
        $datos = $this->getJson('/prueba-grafico/G-10/2')->assertOk()->json();
        $this->assertSame(['A-1'], array_column($datos['nodos'], 'id'));
    }

    public function test_ambas_respuestas_requieren_el_permiso_del_rol_activo(): void
    {
        $this->grupo(10, 1);
        $this->operador->roles()->first()->revokePermissionTo('grupos.subitem_grafico_ministerio');
        $this->getJson('/prueba-grafico/G-10/2')->assertForbidden();
        $this->getJson('/prueba-grafico/G-10/2?solo_totales=1')->assertForbidden();
    }

    public function test_consulta_sin_usuario_o_con_opcion_invalida_es_rechazada(): void
    {
        $this->getJson('/prueba-grafico/G-10/2?solo_totales=otro')->assertUnprocessable()
            ->assertJsonValidationErrors('solo_totales');
        auth()->logout();
        $this->getJson('/prueba-grafico/G-10/2?solo_totales=1')->assertForbidden();
    }

    public function test_grupo_sin_encargados_devuelve_cero_y_grupo_inexistente_devuelve_404(): void
    {
        $grupo = $this->grupo(10, 1);
        $grupo->encargados()->detach();
        $this->getJson('/prueba-grafico/G-10/2?solo_totales=1')->assertOk()
            ->assertExactJson(['totalPersonas' => 0, 'totalGrupos' => 0]);
        $this->getJson('/prueba-grafico/G-999/2?solo_totales=1')->assertNotFound();
    }

    public function test_la_ruta_real_inyecta_la_peticion_y_devuelve_los_totales(): void
    {
        $this->grupo(10, 1);
        $this->withoutMiddleware()->getJson('/grupo/grafico-del-ministerio/G-10/2?solo_totales=1')
            ->assertOk()->assertExactJson(['totalPersonas' => 1, 'totalGrupos' => 1]);
    }

    public function test_la_cobertura_conserva_el_limite_de_veinte_niveles(): void
    {
        $this->cadena(22);
        $this->getJson('/prueba-grafico/G-10/2?solo_totales=1')->assertOk()
            ->assertExactJson(['totalPersonas' => 20, 'totalGrupos' => 20]);
    }

    public function test_reporte_al_dia_conserva_el_limite_de_ocho_dias(): void
    {
        $this->travelTo(\Carbon\Carbon::parse('2026-11-25 10:00:00'));
        foreach ([[10, '2026-11-18', true], [20, '2026-11-17', true], [30, '2026-11-18', false]] as [$id, $fecha, $finalizado]) {
            $this->grupo($id, 1);
            DB::table('reporte_grupos')->insert(['grupo_id' => $id, 'fecha' => $fecha, 'finalizado' => $finalizado]);
        }
        $this->grupo(40, 1);
        $nodos = collect($this->getJson('/prueba-grafico/G-10/2')->assertOk()->json('nodos'))->keyBy('id');
        $this->assertTrue($nodos['G-10']['al_dia']);
        $this->assertFalse($nodos['G-20']['al_dia']);
        $this->assertFalse($nodos['G-30']['al_dia']);
        $this->assertFalse($nodos['G-40']['al_dia']);
    }

    public function test_la_vista_blade_compila_sin_errores_de_sintaxis(): void
    {
        $compilada = app('blade.compiler')->compileString(file_get_contents(
            resource_path('views/contenido/paginas/grupos/grafico-del-ministerio.blade.php')
        ));

        $this->assertNotEmpty(token_get_all($compilada, TOKEN_PARSE));
    }
}
