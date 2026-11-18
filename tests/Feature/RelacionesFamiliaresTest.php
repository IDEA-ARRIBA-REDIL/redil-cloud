<?php

namespace Tests\Feature;

use App\Http\Requests\CrearParienteRequest;
use App\Models\ParienteUsuario;
use App\Models\Role;
use App\Models\User;
use App\Policies\UserPolicy;
use App\Services\RelacionesFamiliares;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class RelacionesFamiliaresTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.connections.familia_prueba' => ['driver' => 'sqlite', 'database' => ':memory:']]);
        DB::setDefaultConnection('familia_prueba');
        DB::statement('create table users (id integer primary key, deleted_at timestamp)');
        DB::statement('create table tipos_parentesco (id integer primary key, relacionado_con integer)');
        DB::statement('create table parientes_usuarios (id integer primary key, user_id integer, pariente_user_id integer, tipo_pariente_id integer, es_el_responsable boolean, created_at timestamp, updated_at timestamp, unique(user_id, pariente_user_id))');
        DB::statement('insert into users (id) values (10),(20),(30),(40)');
        DB::statement('insert into tipos_parentesco values (7,8),(8,7),(9,null),(11,12)');
        DB::statement('insert into parientes_usuarios values (74,30,40,7,1,null,null),(81,40,30,8,0,null,null)');
    }

    public function test_crear_editar_y_eliminar_desde_cualquiera_de_las_personas(): void
    {
        $servicio = new RelacionesFamiliares;
        $originales = DB::table('parientes_usuarios')->orderBy('id')->get();
        foreach ([[10, 20], [20, 10]] as [$a, $b]) {
            $servicio->guardar($a, $b, 7, 2);
            $directa = ParienteUsuario::where('user_id', $a)->sole();
            $inversa = ParienteUsuario::where('user_id', $b)->sole();
            $this->assertSame(8, $inversa->tipo_pariente_id);
            $this->assertEquals(1, $directa->es_el_responsable);
            $this->assertEquals(0, $inversa->es_el_responsable);
            $servicio->guardar($b, $a, 9, 2, $inversa->id);
            $this->assertSame(9, $directa->fresh()->tipo_pariente_id);
            $this->assertSame(9, $inversa->fresh()->tipo_pariente_id);
            $this->assertEquals(0, $directa->fresh()->es_el_responsable);
            $this->assertEquals(1, $inversa->fresh()->es_el_responsable);
            $servicio->eliminar($inversa);
            $this->assertEquals($originales, DB::table('parientes_usuarios')->orderBy('id')->get());
        }
    }

    public function test_exige_permiso_de_accion_y_acceso_a_la_persona(): void
    {
        $actor = \Mockery::mock(User::class)->makePartial();
        $actor->id = 10;
        $persona = User::findOrFail(20);
        $rol = \Mockery::mock(Role::class)->makePartial();
        $actor->shouldReceive('rolActivoParaPermisos')->andReturn($rol);
        $permisos = ['personas.pestana_gentionar_relaciones_familiares'];
        $rol->shouldReceive('hasPermissionTo')->andReturnUsing(function (string $permiso) use (&$permisos): bool {
            return in_array($permiso, $permisos, true);
        });
        $policy = new UserPolicy;
        foreach (['familiar.ver_boton_nueva_relacion_familiar', 'familiar.opcion_modificar_relacion_familiar', 'familiar.opcion_eliminar_relacion_familiar'] as $accion) {
            $permisos = ['personas.lista_asistentes_todos', 'personas.pestana_gentionar_relaciones_familiares'];
            $this->assertFalse($policy->gestionarParentesco($actor, $persona, $accion));
            $permisos[] = $accion;
            $this->assertTrue($policy->gestionarParentesco($actor, $persona, $accion));
            array_shift($permisos);
            $this->assertFalse($policy->gestionarParentesco($actor, $persona, $accion));
        }
    }

    public function test_rechaza_la_misma_persona_parentesco_vacio_y_responsabilidad_desconocida(): void
    {
        $request = new CrearParienteRequest;
        $validador = Validator::make(['parientePrincipal' => 10, 'buscador_asistente_modal' => 10, 'tipoParentesco' => '', 'responsabilidad' => 4], $request->rules(), $request->messages());
        $this->assertTrue($validador->fails());
        $this->assertEqualsCanonicalizing(['buscador_asistente_modal', 'tipoParentesco', 'responsabilidad'], $validador->errors()->keys());
    }

    public function test_rechaza_duplicados_desde_la_ficha_inversa(): void
    {
        $this->expectException(ValidationException::class);
        (new RelacionesFamiliares)->guardar(40, 30, 8, 1);
    }

    public function test_rechaza_un_tipo_sin_su_inverso(): void
    {
        $this->expectException(ValidationException::class);
        (new RelacionesFamiliares)->guardar(10, 20, 11, 1);
    }

    public function test_no_edita_una_relacion_incompleta(): void
    {
        ParienteUsuario::findOrFail(81)->delete();
        try {
            (new RelacionesFamiliares)->guardar(30, 40, 9, 1, 74);
            $this->fail('Se aceptó una relación incompleta.');
        } catch (ValidationException) {
            $this->assertSame(7, ParienteUsuario::findOrFail(74)->tipo_pariente_id);
        }
    }

    public function test_revierte_la_primera_escritura_si_falla_la_segunda(): void
    {
        DB::statement("create trigger falla before insert on parientes_usuarios when new.user_id = 20 begin select raise(abort, 'fallo de prueba'); end");
        try {
            (new RelacionesFamiliares)->guardar(10, 20, 7, 2);
            $this->fail('Se esperaba un fallo en la segunda escritura.');
        } catch (QueryException) {
            $this->assertSame([74, 81], ParienteUsuario::orderBy('id')->pluck('id')->all());
        }
    }
}
