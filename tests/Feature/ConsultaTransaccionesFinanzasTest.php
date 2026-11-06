<?php

namespace Tests\Feature;

use App\Livewire\Finanzas\ConsultaTransacciones;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class ConsultaTransaccionesFinanzasTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'consulta_finanzas_test',
            'database.connections.consulta_finanzas_test' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''],
        ]);
        DB::purge('consulta_finanzas_test');

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('primer_nombre')->nullable();
            $table->string('identificacion')->nullable();
            $table->softDeletes();
        });
        Schema::create('roles', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
        });
        Schema::create('permissions', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('guard_name');
            $table->string('titulo')->nullable();
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });
        Schema::create('role_has_permissions', function (Blueprint $table): void {
            $table->integer('permission_id');
            $table->integer('role_id');
        });
        Schema::create('model_has_roles', function (Blueprint $table): void {
            $table->integer('role_id');
            $table->string('model_type');
            $table->integer('model_id');
            $table->boolean('activo');
            $table->boolean('dependiente');
        });
        Schema::create('tipo_ofrenda', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre');
        });
        Schema::create('tipos_pago', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre');
        });
        Schema::create('estados_pago', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre');
        });
        Schema::create('actividades', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre');
            $table->softDeletes();
        });
        Schema::create('puntos_de_pago', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre');
            $table->softDeletes();
        });
        Schema::create('cajas', function (Blueprint $table): void {
            $table->id();
            $table->integer('punto_de_pago_id');
        });
        Schema::create('compras', function (Blueprint $table): void {
            $table->id();
            $table->string('actividad_id');
            $table->string('nombre_completo_comprador');
            $table->string('identificacion_comprador');
            $table->softDeletes();
        });
        Schema::create('pagos', function (Blueprint $table): void {
            $table->id();
            $table->integer('compra_id');
            $table->integer('tipo_pago_id');
            $table->integer('estado_pago_id');
            $table->integer('registro_caja_id')->nullable();
            $table->integer('int_id_forma_pago')->nullable();
            $table->date('fecha');
            $table->decimal('valor', 12, 2);
            $table->string('referencia_pago')->nullable();
            $table->softDeletes();
        });
        Schema::create('ofrendas', function (Blueprint $table): void {
            $table->id();
            $table->integer('tipo_ofrenda_id');
            $table->integer('user_id')->nullable();
            $table->integer('tipo_pago_id');
            $table->integer('estado_pago_id');
            $table->integer('int_id_forma_pago')->nullable();
            $table->date('fecha');
            $table->decimal('valor', 12, 2);
            $table->string('nombre_donante')->nullable();
            $table->string('identificacion_donante')->nullable();
            $table->string('referencia_donacion')->nullable();
        });

        DB::table('tipo_ofrenda')->insert(['id' => 1, 'nombre' => 'Diezmo']);
        DB::table('tipos_pago')->insert(['id' => 1, 'nombre' => 'ZonaPagos']);
        DB::table('estados_pago')->insert(['id' => 9, 'nombre' => 'Aprobado']);
        DB::table('actividades')->insert(['id' => 7, 'nombre' => 'Encuentro']);
        DB::table('puntos_de_pago')->insert(['id' => 4, 'nombre' => 'Punto Norte']);
        DB::table('cajas')->insert(['id' => 3, 'punto_de_pago_id' => 4]);
        DB::table('compras')->insert(['id' => 11, 'actividad_id' => '7', 'nombre_completo_comprador' => 'Ana', 'identificacion_comprador' => '123']);
        DB::table('pagos')->insert(['id' => 21, 'compra_id' => 11, 'tipo_pago_id' => 1, 'estado_pago_id' => 9, 'registro_caja_id' => 3, 'int_id_forma_pago' => 29, 'fecha' => '2026-11-03', 'valor' => 50000, 'referencia_pago' => 'P-1']);
        DB::table('ofrendas')->insert(['id' => 31, 'tipo_ofrenda_id' => 1, 'tipo_pago_id' => 1, 'estado_pago_id' => 9, 'int_id_forma_pago' => 32, 'fecha' => '2026-11-03', 'valor' => 100000, 'nombre_donante' => 'Beatriz', 'identificacion_donante' => '456', 'referencia_donacion' => 'O-1']);
    }

    protected function tearDown(): void
    {
        DB::purge('consulta_finanzas_test');
        parent::tearDown();
    }

    public function test_combina_una_fila_por_movimiento_y_cuadra_el_total(): void
    {
        $consulta = $this->componente()->consultaMovimientos();

        $this->assertSame(2, $consulta->count());
        $this->assertEquals(150000, $consulta->sum('valor'));
        $this->assertEqualsCanonicalizing(['actividad', 'ofrenda'], $consulta->pluck('origen')->all());
    }

    public function test_filtra_actividad_punto_y_forma_sin_incluir_ofrendas(): void
    {
        $componente = $this->componente();
        $componente->actividadIds = ['7'];
        $componente->puntoPagoIds = ['4'];
        $componente->formasPago = ['29'];

        $resultado = $componente->consultaMovimientos()->get();

        $this->assertCount(1, $resultado);
        $this->assertSame('actividad', $resultado[0]->origen);
        $this->assertSame('Encuentro', $resultado[0]->concepto);

        $componente->formasPago = ['32'];
        $this->assertSame(0, $componente->consultaMovimientos()->count());
    }

    public function test_acepta_varias_opciones_por_filtro_y_combina_los_filtros(): void
    {
        DB::table('tipos_pago')->insert(['id' => 2, 'nombre' => 'Efectivo']);
        DB::table('actividades')->insert(['id' => 8, 'nombre' => 'Retiro']);
        DB::table('puntos_de_pago')->insert(['id' => 5, 'nombre' => 'Punto Sur']);
        DB::table('cajas')->insert(['id' => 4, 'punto_de_pago_id' => 5]);
        DB::table('compras')->insert(['id' => 12, 'actividad_id' => '8', 'nombre_completo_comprador' => 'Luis', 'identificacion_comprador' => '789']);
        DB::table('pagos')->insert(['id' => 22, 'compra_id' => 12, 'tipo_pago_id' => 2, 'estado_pago_id' => 9, 'registro_caja_id' => 4, 'int_id_forma_pago' => null, 'fecha' => '2026-11-03', 'valor' => 70000, 'referencia_pago' => 'P-2']);

        $componente = $this->componente();
        $componente->tipoIngreso = 'actividad';
        $componente->metodoPagoIds = ['1', '2'];
        $componente->formasPago = ['29', 'sin_codigo'];
        $componente->actividadIds = ['7', '8'];
        $componente->puntoPagoIds = ['4', '5'];

        $this->assertSame(2, $componente->consultaMovimientos()->count());
        $this->assertEquals(120000, $componente->consultaMovimientos()->sum('valor'));

        $componente->puntoPagoIds = ['5'];
        $this->assertEqualsCanonicalizing(['P-2'], $componente->consultaMovimientos()->pluck('referencia')->all());

        $componente->formasPago = ['sin_codigo'];
        $this->assertEqualsCanonicalizing(['P-2'], $componente->consultaMovimientos()->pluck('referencia')->all());

        DB::table('pagos')->where('id', 22)->update(['int_id_forma_pago' => 0]);
        $this->assertEqualsCanonicalizing(['P-2'], $componente->consultaMovimientos()->pluck('referencia')->all());

        $componente->puntoPagoIds = [];
        $componente->metodoPagoIds = ['1'];
        $componente->formasPago = ['29', 'sin_codigo'];
        $this->assertEqualsCanonicalizing(['P-1'], $componente->consultaMovimientos()->pluck('referencia')->all());
    }

    public function test_conserva_pagos_eliminados_en_la_auditoria_y_los_identifica(): void
    {
        DB::table('pagos')->where('id', 21)->update(['deleted_at' => '2026-11-04 12:00:00']);

        $pago = $this->componente()->consultaMovimientos()->where('origen', 'actividad')->first();

        $this->assertSame('Eliminado', $pago->estado);
        $this->assertEquals(50000, $pago->valor);
    }

    public function test_el_permiso_controla_la_consulta_y_la_pantalla_muestra_los_movimientos(): void
    {
        DB::table('users')->insert(['id' => 1, 'primer_nombre' => 'Finanzas', 'identificacion' => '999']);
        DB::table('roles')->insert(['id' => 1, 'name' => 'Finanzas', 'guard_name' => 'web']);
        DB::table('permissions')->insert(['id' => 1, 'name' => 'finanzas.consultar_transacciones', 'guard_name' => 'web']);
        DB::table('model_has_roles')->insert(['role_id' => 1, 'model_type' => User::class, 'model_id' => 1, 'activo' => true, 'dependiente' => false]);
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        $usuario = User::query()->findOrFail(1);

        Livewire::actingAs($usuario)->test(ConsultaTransacciones::class)->assertForbidden();

        DB::table('role_has_permissions')->insert(['permission_id' => 1, 'role_id' => 1]);
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        Livewire::actingAs($usuario)->test(ConsultaTransacciones::class)
            ->set('fechaInicio', '2026-11-03')
            ->set('fechaFin', '2026-11-03')
            ->call('buscar')
            ->assertSee('Beatriz')
            ->assertSee('Encuentro')
            ->assertSee('150.000,00');
    }

    public function test_migracion_crea_permiso_y_lo_asigna_a_roles_con_finanzas(): void
    {
        DB::table('roles')->insert(['id' => 1, 'name' => 'Finanzas', 'guard_name' => 'web']);
        DB::table('permissions')->insert(['id' => 1, 'name' => 'finanzas.item_finanzas', 'guard_name' => 'web']);
        DB::table('role_has_permissions')->insert(['permission_id' => 1, 'role_id' => 1]);
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        $migracion = require base_path('database/migrations/tenant/2026_11_06_152031_add_consulta_transacciones_finanzas_permission.php');
        $migracion->up();

        $permisoId = DB::table('permissions')->where('name', 'finanzas.consultar_transacciones')->value('id');
        $this->assertNotNull($permisoId);
        $this->assertSame(1, DB::table('role_has_permissions')->where('role_id', 1)->where('permission_id', $permisoId)->count());
    }

    private function componente(): object
    {
        $componente = new ConsultaTransacciones;
        $componente->fechaInicio = '2026-11-03';
        $componente->fechaFin = '2026-11-03';

        return new class($componente)
        {
            public function __construct(private ConsultaTransacciones $componente) {}

            public function __set(string $nombre, mixed $valor): void
            {
                $this->componente->{$nombre} = $valor;
            }

            public function consultaMovimientos(): \Illuminate\Database\Query\Builder
            {
                return (new \ReflectionMethod($this->componente, 'consultaMovimientos'))->invoke($this->componente);
            }
        };
    }
}
