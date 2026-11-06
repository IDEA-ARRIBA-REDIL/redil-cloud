<?php

namespace Tests\Feature;

use App\Livewire\Actividades\DashboardFormularios;
use App\Models\Actividad;
use App\Models\ActividadCategoria;
use App\Models\Caja;
use App\Models\Compra;
use App\Models\EstadoPago;
use App\Models\Inscripcion;
use App\Models\Matricula;
use App\Models\Pago;
use App\Services\EstadoFinancieroService;
use App\Services\ImporteMonetario;
use App\Services\RegistroPagoTaquillaService;
use App\Services\TotalesFinancierosService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class MejoraBdFinanzasTest extends TestCase
{
    private ?string $esquema = null;

    protected function setUp(): void
    {
        parent::setUp();
        $base = getenv('MEJORA_BD_TEST_DATABASE');
        if ($base !== 'redil_mejora_bd_ensayo_20261028') {
            $this->markTestSkipped('Requiere la base local de ensayo autorizada mediante MEJORA_BD_TEST_DATABASE.');
        }
        $config = config('database.connections.pgsql');
        $this->assertContains($config['host'], ['127.0.0.1', 'localhost']);
        unset($config['url'], $config['read'], $config['write']);
        $config['database'] = $base;
        $config['search_path'] = 'public';
        config(['database.connections.mejora_bd_test' => $config, 'cache.default' => 'array']);
        DB::setDefaultConnection('mejora_bd_test');
        $this->assertSame($base, DB::connection()->getDatabaseName());
        $this->esquema = 'mbd_test_'.bin2hex(random_bytes(6));
        DB::statement('CREATE SCHEMA '.$this->esquema);
        DB::statement('SET search_path TO '.$this->esquema);
        Http::preventStrayRequests();
        Mail::fake();
        Queue::fake();

        foreach ([
            'users' => 'id bigint PRIMARY KEY, deleted_at timestamp',
            'monedas' => 'id bigint PRIMARY KEY, nombre text',
            'tipos_pago' => 'id bigint PRIMARY KEY, nombre text, activo boolean DEFAULT true, habilitado_punto_pago boolean DEFAULT true, tiene_limite_dinero_acumulado boolean DEFAULT true',
            'estados_pago' => 'id bigint PRIMARY KEY, tipo_pago_id bigint, nombre text, estado_pendiente boolean DEFAULT false, estado_final_inscripcion boolean DEFAULT false, estado_anulado_inscripcion boolean DEFAULT false',
            'cajas' => 'id bigserial PRIMARY KEY, estado boolean DEFAULT true, dinero_acumulado bigint DEFAULT 0, limite_dinero_acumulado bigint DEFAULT 1000, created_at timestamp, updated_at timestamp, deleted_at timestamp',
            'compras' => 'id bigserial PRIMARY KEY, user_id bigint, actividad_id bigint, moneda_id bigint, valor double precision, estado integer DEFAULT 1, listado_carrito text, fecha date, created_at timestamp, updated_at timestamp, deleted_at timestamp',
            'pagos' => 'id bigserial PRIMARY KEY, compra_id bigint, moneda_id bigint, tipo_pago_id bigint, estado_pago_id bigint, valor double precision, comision double precision, valor_neto double precision, anulado_pdp boolean DEFAULT false, registro_caja_id bigint, actividad_categoria_id bigint, codigo_vaucher text, referencia_pago text, gateway_response json, fecha date, created_at timestamp, updated_at timestamp, deleted_at timestamp',
            'matriculas' => 'id bigserial PRIMARY KEY, user_id bigint, referencia_pago text UNIQUE, estado_pago_matricula text, tipo_pago_id bigint, valor_pagado numeric, fecha_pago timestamp, created_at timestamp, updated_at timestamp, deleted_at timestamp',
            'matricula_horario_materia_periodo' => 'id bigserial PRIMARY KEY, matricula_id bigint',
            'inscripciones' => 'id bigserial PRIMARY KEY, user_id integer, actividad_categoria_id integer NOT NULL, compra_id integer, fecha date, fecha_pago date, estado integer NOT NULL DEFAULT 1, json_campos_adicionales json, email varchar(100), nombre_inscrito varchar(100), inscripcion_asociada integer, limite_invitados integer, created_at timestamp, updated_at timestamp, deleted_at timestamp',
            'reporte_grupos' => 'id bigserial PRIMARY KEY, grupo_id bigint, sede_id bigint, fecha date, ids_grupos_ascendentes json, aprobado boolean, autor_aprobacion bigint, fecha_aprobacion timestamp, movito_desaprobacion_id bigint, observacion_desaprobacion text, created_at timestamp, updated_at timestamp',
            'tipo_ofrenda' => 'id bigint PRIMARY KEY, generica boolean',
            'ofrendas' => 'id bigserial PRIMARY KEY, tipo_ofrenda_id bigint, user_id bigint, moneda_id bigint, valor numeric, valor_real numeric, fecha date, created_at timestamp, updated_at timestamp',
            'ofrenda_grupos' => 'id bigserial PRIMARY KEY, reporte_grupo_id bigint, ofrenda_id bigint, created_at timestamp, updated_at timestamp',
            'ingresos' => 'id bigserial PRIMARY KEY, ofrenda_id bigint, valor_real numeric, created_at timestamp, updated_at timestamp',
            'asistencia_grupos' => 'id bigserial PRIMARY KEY, reporte_grupo_id bigint, user_id bigint',
        ] as $tabla => $columnas) {
            DB::statement('CREATE TABLE '.$tabla.' ('.$columnas.')');
        }
        DB::table('users')->insert([['id' => 1], ['id' => 2]]);
        $this->migrar();
        DB::table('monedas')->insert([['id' => 1, 'nombre' => 'COP'], ['id' => 2, 'nombre' => 'USD']]);
        DB::table('tipos_pago')->insert([['id' => 1, 'nombre' => 'Efectivo'], ['id' => 2, 'nombre' => 'Pasarela']]);
        DB::table('estados_pago')->insert([
            ['id' => 11, 'tipo_pago_id' => 1, 'nombre' => 'Pendiente', 'estado_pendiente' => true, 'estado_final_inscripcion' => false, 'estado_anulado_inscripcion' => false],
            ['id' => 12, 'tipo_pago_id' => 1, 'nombre' => 'Aprobado', 'estado_pendiente' => false, 'estado_final_inscripcion' => true, 'estado_anulado_inscripcion' => false],
            ['id' => 13, 'tipo_pago_id' => 1, 'nombre' => 'Rechazado', 'estado_pendiente' => false, 'estado_final_inscripcion' => false, 'estado_anulado_inscripcion' => true],
            ['id' => 22, 'tipo_pago_id' => 2, 'nombre' => 'Aprobado pasarela', 'estado_pendiente' => false, 'estado_final_inscripcion' => true, 'estado_anulado_inscripcion' => false],
        ]);
    }

    protected function tearDown(): void
    {
        if ($this->esquema !== null) {
            DB::statement('SET search_path TO public');
            DB::statement('DROP SCHEMA '.$this->esquema.' CASCADE');
            DB::purge('mejora_bd_test');
        }
        parent::tearDown();
    }

    private function migrar(): void
    {
        DB::transaction(function (): void {
            (require database_path('migrations/tenant/2026_10_28_113027_add_explicit_financial_relations_and_indexes.php'))->up();
        });
    }

    private function completarTablasDeAuditoria(): void
    {
        foreach ([
            'grupos' => 'id bigint PRIMARY KEY',
            'integrantes_grupo' => 'id bigserial PRIMARY KEY, grupo_id bigint, user_id bigint',
            'encargados_grupo' => 'id bigserial PRIMARY KEY, grupo_id bigint, user_id bigint',
            'ofrenda_reuniones' => 'id bigserial PRIMARY KEY, reporte_reunion_id bigint, ofrenda_id bigint',
        ] as $tabla => $columnas) {
            DB::statement('CREATE TABLE '.$tabla.' ('.$columnas.')');
        }
    }

    public function test_auditoria_rechaza_huerfanos_aunque_no_exista_una_fk_pendiente(): void
    {
        $this->completarTablasDeAuditoria();
        DB::table('integrantes_grupo')->insert(['grupo_id' => 999, 'user_id' => 1]);

        $this->artisan('bd:auditar-mejoras', ['--json' => true])
            ->expectsOutputToContain('"apto_para_promover": false')
            ->assertExitCode(1);
    }

    public function test_auditoria_acepta_datos_sin_incidencias_en_su_alcance(): void
    {
        $this->completarTablasDeAuditoria();

        $this->artisan('bd:auditar-mejoras', ['--json' => true])
            ->expectsOutputToContain('"apto_para_promover": true')
            ->assertExitCode(0);
    }

    private function compra(string $valor = '100.00', int $moneda = 1): Compra
    {
        return Compra::query()->create(['valor' => $valor, 'moneda_id' => $moneda, 'fecha' => '2026-10-28', 'estado' => Compra::PENDIENTE]);
    }

    private function pago(Compra $compra, string $valor = '100.00', int $estado = 11): Pago
    {
        return Pago::query()->create(['compra_id' => $compra->id, 'moneda_id' => $compra->moneda_id, 'tipo_pago_id' => 1, 'estado_pago_id' => $estado, 'valor' => $valor, 'fecha' => '2026-10-28']);
    }

    private function matricula(Compra $compra, Pago $pago): Matricula
    {
        $matricula = Matricula::query()->create(['compra_id' => $compra->id, 'pago_id' => $pago->id, 'user_id' => 1, 'estado_pago_matricula' => 'pendiente']);
        DB::table('matricula_horario_materia_periodo')->insert(['matricula_id' => $matricula->id]);

        return $matricula;
    }

    public function test_callback_repetido_es_idempotente_y_rechazo_tardio_no_regresa_un_pago_aprobado(): void
    {
        $compra = $this->compra();
        $pago = $this->pago($compra);
        $matricula = $this->matricula($compra, $pago);
        $inscripcion = Inscripcion::query()->create(['compra_id' => $compra->id, 'actividad_categoria_id' => 1]);
        $this->assertSame(1, $inscripcion->fresh()->estado);
        $servicio = app(EstadoFinancieroService::class);
        $aprobado = EstadoPago::findOrFail(12);
        $servicio->aplicar($pago, $aprobado, ['gateway_response' => ['resultado' => 'ok']]);
        $this->assertSame(3, $inscripcion->fresh()->estado);
        $servicio->aplicar($pago, $aprobado);
        $this->assertSame(3, $inscripcion->fresh()->estado);
        $servicio->aplicar($pago, EstadoPago::findOrFail(13));
        $this->assertSame(12, $pago->fresh()->estado_pago_id);
        $this->assertSame(Compra::PAGADA, $compra->fresh()->estado);
        $this->assertSame('pagada', $matricula->fresh()->estado_pago_matricula);
        $this->assertSame('100.00', $matricula->fresh()->valor_pagado);
        $this->assertSame(['resultado' => 'ok'], $pago->fresh()->gateway_response);
        $this->assertSame(1, Pago::count());
        $this->assertSame(3, $inscripcion->fresh()->estado);
    }

    public function test_mismo_estado_repara_efectos_incompletos(): void
    {
        $compra = $this->compra();
        $pago = $this->pago($compra, '100', 12);
        $inscripcion = Inscripcion::query()->create(['compra_id' => $compra->id, 'actividad_categoria_id' => 1, 'estado' => 2]);
        app(EstadoFinancieroService::class)->aplicar($pago, EstadoPago::findOrFail(12));
        $this->assertSame(Compra::PAGADA, $compra->fresh()->estado);
        $this->assertSame(3, $inscripcion->fresh()->estado);
    }

    public function test_abono_no_finaliza_compra_hasta_completar_saldo(): void
    {
        $compra = $this->compra();
        $pago = $this->pago($compra, '40');
        $inscripcion = Inscripcion::query()->create(['compra_id' => $compra->id, 'actividad_categoria_id' => 1, 'estado' => 2]);
        $servicio = app(EstadoFinancieroService::class);
        $servicio->aplicar($pago, EstadoPago::findOrFail(12));
        $this->assertSame(Compra::PENDIENTE, $compra->fresh()->estado);
        $this->assertSame(2, $inscripcion->fresh()->estado);
        $servicio->aplicar($this->pago($compra, '60'), EstadoPago::findOrFail(12));
        $this->assertSame(Compra::PAGADA, $compra->fresh()->estado);
        $this->assertSame(3, $inscripcion->fresh()->estado);
    }

    public function test_conciliacion_conserva_inscripciones_finalizadas_bajas_y_otras_compras(): void
    {
        $compra = $this->compra();
        $pago = $this->pago($compra);
        $finalizada = Inscripcion::query()->create(['compra_id' => $compra->id, 'actividad_categoria_id' => 1, 'estado' => 3]);
        $baja = Inscripcion::query()->create(['compra_id' => $compra->id, 'actividad_categoria_id' => 1, 'estado' => 2]);
        $baja->delete();
        $otra = Inscripcion::query()->create(['compra_id' => $this->compra()->id, 'actividad_categoria_id' => 1, 'estado' => 2]);
        $sinCompra = Inscripcion::query()->create(['actividad_categoria_id' => 1, 'estado' => 1]);

        app(EstadoFinancieroService::class)->aplicar($pago, EstadoPago::findOrFail(12));

        $this->assertSame(3, $finalizada->fresh()->estado);
        $this->assertSame(2, $baja->fresh()->estado);
        $this->assertTrue($baja->fresh()->trashed());
        $this->assertSame(2, $otra->fresh()->estado);
        $this->assertSame(1, $sinCompra->fresh()->estado);
    }

    public function test_inscripcion_pagada_permite_ajustar_cupos_sin_duplicarlos_al_repetir_callback(): void
    {
        DB::statement('CREATE TABLE actividad_categorias (id bigserial PRIMARY KEY, actividad_id integer, aforo integer, aforo_ocupado integer, limite_invitados integer, created_at timestamp, updated_at timestamp)');
        $categoria = ActividadCategoria::query()->create(['actividad_id' => 1, 'aforo' => 10, 'aforo_ocupado' => 1, 'limite_invitados' => 4]);
        $compra = $this->compra();
        $pago = $this->pago($compra);
        $inscripcion = Inscripcion::query()->create(['compra_id' => $compra->id, 'actividad_categoria_id' => $categoria->id, 'estado' => 2, 'limite_invitados' => 0]);
        $componente = new DashboardFormularios;
        $componente->actividad = new Actividad;
        $componente->actividad->id = 1;
        $componente->cantidadInvitadosAprobados[$inscripcion->id] = 2;

        $componente->actualizarCuposInvitadosAprobados($inscripcion->id);
        $this->assertSame(0, $inscripcion->fresh()->limite_invitados);
        $this->assertSame(1, $categoria->fresh()->aforo_ocupado);

        $servicio = app(EstadoFinancieroService::class);
        $servicio->aplicar($pago, EstadoPago::findOrFail(12));
        $componente->actualizarCuposInvitadosAprobados($inscripcion->id);
        $this->assertSame(3, $inscripcion->fresh()->estado);
        $this->assertSame(2, $inscripcion->fresh()->limite_invitados);
        $this->assertSame(3, $categoria->fresh()->aforo_ocupado);

        $servicio->aplicar($pago, EstadoPago::findOrFail(12));
        $componente->actualizarCuposInvitadosAprobados($inscripcion->id);
        $this->assertSame(3, $categoria->fresh()->aforo_ocupado);
        $this->assertSame(2, $inscripcion->fresh()->limite_invitados);
    }

    public function test_pago_fallido_solo_limpia_su_reserva_explicita_y_conserva_legacy_y_otras_compras(): void
    {
        $compra = $this->compra();
        $pago = $this->pago($compra);
        $matricula = $this->matricula($compra, $pago);
        $otra = $this->compra();
        $otraMatricula = $this->matricula($otra, $this->pago($otra));
        $legacy = Matricula::create(['user_id' => 1, 'referencia_pago' => (string) $pago->id, 'estado_pago_matricula' => 'pendiente']);
        app(EstadoFinancieroService::class)->aplicar($pago, EstadoPago::findOrFail(13));
        $this->assertTrue($matricula->fresh()->trashed());
        $this->assertFalse($otraMatricula->fresh()->trashed());
        $this->assertFalse($legacy->fresh()->trashed());
        $this->assertSame(1, DB::table('matricula_horario_materia_periodo')->count());
    }

    public function test_otro_intento_pendiente_impide_liberar_la_reserva(): void
    {
        $compra = $this->compra();
        $pago = $this->pago($compra);
        $matricula = $this->matricula($compra, $pago);
        $this->pago($compra);
        app(EstadoFinancieroService::class)->aplicar($pago, EstadoPago::findOrFail(13));
        $this->assertFalse($matricula->fresh()->trashed());
    }

    public function test_estado_de_otro_tipo_se_rechaza_sin_modificar_el_pago(): void
    {
        $pago = $this->pago($this->compra());
        try {
            app(EstadoFinancieroService::class)->aplicar($pago, EstadoPago::findOrFail(22));
            $this->fail('Debió rechazar un estado de otro método.');
        } catch (ValidationException) {
            $this->assertSame(11, $pago->fresh()->estado_pago_id);
        }
    }

    public function test_fallo_al_actualizar_inscripcion_revierte_pago_y_compra(): void
    {
        $compra = $this->compra();
        $pago = $this->pago($compra);
        $inscripcion = Inscripcion::query()->create(['compra_id' => $compra->id, 'actividad_categoria_id' => 1, 'estado' => 2]);
        DB::unprepared("CREATE FUNCTION rechazar_actualizacion() RETURNS trigger LANGUAGE plpgsql AS $$ BEGIN RAISE EXCEPTION 'fallo de prueba'; END $$");
        DB::unprepared('CREATE TRIGGER fallo BEFORE UPDATE ON inscripciones FOR EACH ROW EXECUTE PROCEDURE rechazar_actualizacion()');
        try {
            app(EstadoFinancieroService::class)->aplicar($pago, EstadoPago::findOrFail(12));
            $this->fail('Debió fallar la transacción.');
        } catch (QueryException) {
            $this->assertSame(11, $pago->fresh()->estado_pago_id);
            $this->assertSame(Compra::PENDIENTE, $compra->fresh()->estado);
            $this->assertSame(2, $inscripcion->fresh()->estado);
        }
    }

    public function test_total_sql_respeta_moneda_filtro_y_soft_delete_sin_cargar_relaciones(): void
    {
        $this->compra('0.10');
        $this->compra('0.20');
        $this->compra('9.25', 2);
        $this->compra('900')->delete();
        $this->compra('100')->update(['fecha' => '2025-01-01']);
        DB::enableQueryLog();
        DB::flushQueryLog();
        $totales = app(TotalesFinancierosService::class)->porMoneda(Compra::with('relacionInexistente')->where('fecha', '2026-10-28')->orderBy('id'));
        $consultas = DB::getQueryLog();
        DB::disableQueryLog();
        $this->assertSame('0.30', $totales[1]['total']);
        $this->assertSame(2, $totales[1]['count']);
        $this->assertSame('9.25', $totales[2]['total']);
        $this->assertCount(2, $consultas);
        $this->assertSame('0.30', ImporteMonetario::sumar(['0.1', '0.2']));
    }

    public function test_caja_con_centavos_e_idempotencia_de_taquilla(): void
    {
        $compra = $this->compra('0.30');
        $caja = Caja::create(['dinero_acumulado' => '0.00', 'limite_dinero_acumulado' => '1.00']);
        $pagos = [['tipo_pago_id' => 1, 'valor' => '0.10'], ['tipo_pago_id' => 1, 'valor' => '0.20']];
        $operacion = (string) Str::uuid();
        $servicio = app(RegistroPagoTaquillaService::class);
        $servicio->registrar($compra, $caja, $pagos, $operacion, 1);
        $servicio->registrar($compra, $caja, $pagos, $operacion, 1);
        $this->assertSame('0.30', ImporteMonetario::normalizar($caja->fresh()->dinero_acumulado));
        $this->assertSame(2, Pago::count());
        $this->assertSame(Compra::PAGADA, $compra->fresh()->estado);
        $this->assertSame(12, Pago::first()->estado_pago_id);
    }

    public function test_limite_caja_revierte_todas_las_lineas_del_cobro(): void
    {
        $compra = $this->compra('2');
        $caja = Caja::create(['dinero_acumulado' => '0.00', 'limite_dinero_acumulado' => '1.00']);
        try {
            app(RegistroPagoTaquillaService::class)->registrar($compra, $caja, [['tipo_pago_id' => 1, 'valor' => '0.50'], ['tipo_pago_id' => 1, 'valor' => '1.50']], (string) Str::uuid(), 1);
            $this->fail('Debió rechazar el límite.');
        } catch (ValidationException) {
            $this->assertSame(0, Pago::count());
            $this->assertSame('0.00', ImporteMonetario::normalizar($caja->fresh()->dinero_acumulado));
        }
    }

    public function test_saldo_se_relee_al_abonar_y_no_admite_sobrepago(): void
    {
        $compra = $this->compra('100');
        $caja = Caja::create([]);
        $servicio = app(RegistroPagoTaquillaService::class);
        $servicio->registrar($compra, $caja, [['tipo_pago_id' => 1, 'valor' => '60']], (string) Str::uuid(), 1, false);
        try {
            $servicio->registrar($compra, $caja, [['tipo_pago_id' => 1, 'valor' => '60']], (string) Str::uuid(), 1, false);
            $this->fail('Debió releer el saldo.');
        } catch (ValidationException) {
            $this->assertSame(1, Pago::count());
            $this->assertSame('60.00', app(EstadoFinancieroService::class)->totalAprobado($compra));
        }
    }

    public function test_fk_compuesta_rechaza_matricula_con_pago_de_otra_compra(): void
    {
        $compra = $this->compra();
        $pago = $this->pago($this->compra());
        $this->expectException(QueryException::class);
        $this->matricula($compra, $pago);
    }

    public function test_no_acepta_pago_sin_compra_en_matricula(): void
    {
        $pago = $this->pago($this->compra());
        $this->expectException(QueryException::class);
        Matricula::create(['pago_id' => $pago->id]);
    }

    public function test_no_admite_asistencia_duplicada(): void
    {
        DB::table('reporte_grupos')->insert(['id' => 1]);
        DB::table('asistencia_grupos')->insert(['reporte_grupo_id' => 1, 'user_id' => 2]);
        $this->expectException(QueryException::class);
        DB::table('asistencia_grupos')->insert(['reporte_grupo_id' => 1, 'user_id' => 2]);
    }

    public function test_migracion_repetible_no_redondea_importes_ni_cambia_snapshot_json(): void
    {
        DB::table('compras')->insert(['valor' => '123.456789']);
        DB::table('reporte_grupos')->insert(['ids_grupos_ascendentes' => '[1, 2, 3]']);
        $this->migrar();
        $this->assertSame('123.456789', DB::table('compras')->value('valor'));
        $this->assertSame('[1, 2, 3]', DB::table('reporte_grupos')->value('ids_grupos_ascendentes'));
        $this->assertTrue(Schema::hasIndex('reporte_grupos', 'mbd_reportes_ancestros_gin'));
        $this->assertSame(1, DB::table('reporte_grupos')->whereJsonContains('ids_grupos_ascendentes', 2)->count());
    }

    public function test_estado_comercial_no_depende_de_ids_del_catalogo_de_pasarelas(): void
    {
        $compra = $this->compra();
        $compra->update(['estado' => Compra::PAGADA]);
        $this->assertSame('Pagada', $compra->fresh()->estadoComercial()->nombre);
        $this->assertSame(1, Compra::estadoInforme('2')->count());
        $this->assertSame(0, Compra::estadoInforme('1')->count());
        $compra->update(['anulacion_solicitada' => true]);
        $this->assertSame('Anulación solicitada', $compra->fresh()->estadoComercial()->nombre);
        $this->assertSame(Compra::PAGADA, $compra->fresh()->estado);
    }

    public function test_pago_en_otra_moneda_no_se_aplica_a_la_compra(): void
    {
        $pago = $this->pago($this->compra());
        $pago->update(['moneda_id' => 2]);
        $this->expectException(ValidationException::class);
        app(EstadoFinancieroService::class)->aplicar($pago, EstadoPago::findOrFail(12));
    }

    public function test_abono_anulado_no_cuenta_como_saldo_pagado(): void
    {
        $compra = $this->compra();
        $this->pago($compra, '100', 12)->update(['anulado_pdp' => true]);
        $this->assertSame('0.00', app(EstadoFinancieroService::class)->totalAprobado($compra));
    }

    public function test_bloqueo_de_compra_impide_actualizacion_concurrente_parcial(): void
    {
        $compra = $this->compra();
        $pago = $this->pago($compra);
        $config = config('database.connections.mejora_bd_test');
        $config['search_path'] = $this->esquema;
        config(['database.connections.mejora_bd_contendiente' => $config]);
        $primera = DB::connection('mejora_bd_test');
        $primera->beginTransaction();
        $primera->table('compras')->where('id', $compra->id)->lockForUpdate()->first();
        DB::setDefaultConnection('mejora_bd_contendiente');
        DB::statement("SET lock_timeout = '150ms'");
        try {
            app(EstadoFinancieroService::class)->aplicar($pago->setConnection('mejora_bd_contendiente'), EstadoPago::findOrFail(12));
            $this->fail('La segunda conexión debió esperar el bloqueo.');
        } catch (QueryException $error) {
            $this->assertSame('55P03', $error->errorInfo[0]);
        } finally {
            $primera->rollBack();
            DB::setDefaultConnection('mejora_bd_test');
            DB::purge('mejora_bd_contendiente');
        }
        $this->assertSame(11, Pago::findOrFail($pago->id)->estado_pago_id);
        $this->assertSame(Compra::PENDIENTE, $compra->fresh()->estado);
    }

    private function aprobacionOfrenda(): \App\Livewire\ReporteGrupos\GestionarAprobacionDesaprobacionDeReportes
    {
        DB::table('tipo_ofrenda')->insert(['id' => 1, 'generica' => true]);
        DB::table('reporte_grupos')->insert(['id' => 1, 'aprobado' => null]);
        DB::table('ofrendas')->insert(['id' => 1, 'tipo_ofrenda_id' => 1, 'moneda_id' => 1, 'valor' => '10.50', 'valor_real' => '0']);
        DB::table('ofrenda_grupos')->insert(['reporte_grupo_id' => 1, 'ofrenda_id' => 1]);
        DB::table('ingresos')->insert(['ofrenda_id' => 1, 'valor_real' => '0']);
        $usuario = new \App\Models\User;
        $usuario->id = 1;
        $this->actingAs($usuario);
        request()->headers->set('Referer', 'http://localhost');
        $componente = new \App\Livewire\ReporteGrupos\GestionarAprobacionDesaprobacionDeReportes;
        $componente->reporte = \App\Models\ReporteGrupo::findOrFail(1);
        $componente->seAprobo = 'si';

        return $componente;
    }

    public function test_aprobacion_ofrenda_actualiza_ingreso_y_repeticion_no_duplica(): void
    {
        $componente = $this->aprobacionOfrenda();
        $componente->submitFormulario();
        $componente->submitFormulario();
        $this->assertTrue((bool) DB::table('reporte_grupos')->value('aprobado'));
        $this->assertSame('10.50', DB::table('ingresos')->value('valor_real'));
        $this->assertSame(1, DB::table('ofrendas')->count());
    }

    public function test_fallo_en_ingreso_revierte_aprobacion_y_ofrenda(): void
    {
        $componente = $this->aprobacionOfrenda();
        DB::unprepared("CREATE FUNCTION rechazar_ingreso() RETURNS trigger LANGUAGE plpgsql AS $$ BEGIN RAISE EXCEPTION 'fallo de prueba'; END $$");
        DB::unprepared('CREATE TRIGGER fallo BEFORE UPDATE ON ingresos FOR EACH ROW EXECUTE PROCEDURE rechazar_ingreso()');
        try {
            $componente->submitFormulario();
            $this->fail('Debió revertir toda la aprobación.');
        } catch (QueryException) {
            $this->assertNull(DB::table('reporte_grupos')->value('aprobado'));
            $this->assertSame('0', DB::table('ofrendas')->value('valor_real'));
            $this->assertSame('0', DB::table('ingresos')->value('valor_real'));
        }
    }

    public function test_informe_compras_filtra_estado_y_mantiene_etiquetas_y_totales(): void
    {
        $this->compra('50')->update(['estado' => Compra::PAGADA]);
        $this->compra('90');
        $componente = new \App\Livewire\Informes\InformeCompras;
        $componente->activeFilters = ['estado' => '2'];
        $this->assertSame([['label' => 'Estado: Pagada', 'field' => 'estado']], $componente->getTagsProperty());
        $metodo = new \ReflectionMethod($componente, 'calcularTotales');
        $this->assertSame('50.00', $metodo->invoke($componente)[1]['total']);
    }

    public function test_un_borrador_sin_metodo_aun_protege_la_reserva_de_otro_intento(): void
    {
        $compra = $this->compra();
        $pago = $this->pago($compra);
        $matricula = $this->matricula($compra, $pago);
        $this->pago($compra)->update(['estado_pago_id' => null, 'tipo_pago_id' => null]);
        app(EstadoFinancieroService::class)->aplicar($pago, EstadoPago::findOrFail(13));
        $this->assertFalse($matricula->fresh()->trashed());
        $this->assertSame(Compra::PENDIENTE, $compra->fresh()->estado);
    }

    public function test_base_rechaza_un_estado_de_otro_metodo_aunque_se_omita_el_servicio(): void
    {
        $pago = $this->pago($this->compra());
        $this->expectException(QueryException::class);
        $pago->update(['estado_pago_id' => 22]);
    }
}
