<?php

namespace Tests\Feature;

use App\Http\Middleware\LocaleMiddleware;
use App\Http\Middleware\RevisarSuspensionTenant;
use App\Livewire\Carrito\Checkout;
use App\Models\Actividad;
use App\Models\Compra;
use App\Models\Configuracion;
use App\Models\EstadoPago;
use App\Models\HorarioMateriaPeriodo;
use App\Models\Materia;
use App\Models\MateriaPeriodo;
use App\Models\Matricula;
use App\Models\MatriculaHorarioMateriaPeriodo;
use App\Models\Moneda;
use App\Models\Pago;
use App\Models\Tenant;
use App\Models\TipoActividad;
use App\Models\TipoPago;
use App\Models\User;
use App\Services\ZonaPagosService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use Tests\TestCase;

class ZonaPagosEscuelasSondaTest extends TestCase
{
    protected function setUp(): void
    {
        putenv('VIEW_COMPILED_PATH='.sys_get_temp_dir());
        $_ENV['VIEW_COMPILED_PATH'] = sys_get_temp_dir();
        $_SERVER['VIEW_COMPILED_PATH'] = sys_get_temp_dir();

        parent::setUp();

        config([
            'database.default' => 'zonapagos_test',
            'database.connections.zonapagos_test' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
            ],
            'cache.default' => 'array',
            'session.driver' => 'array',
            'logging.default' => 'null',
            'view.compiled' => sys_get_temp_dir(),
            'services.zonapagos' => [
                'api_url' => 'https://zonapagos-env.test/api',
                'id_comercio' => 99999,
                'usuario' => 'ENV_USER',
                'clave' => 'ENV_PASS',
                'codigo_servicio' => '2701',
                'minutos_abandono_pasarela' => 45,
                'minutos_abandono_borrador' => 30,
            ],
        ]);

        DB::purge('zonapagos_test');
        Http::preventStrayRequests();
        Mail::fake();

        $this->crearEsquemaDePrueba();
        $this->sembrarCatalogosBase();

        $tenant = new Tenant;
        $tenant->id = 'escuelas_test';
        $tenancy = app(\Stancl\Tenancy\Tenancy::class);
        $tenancy->tenant = $tenant;
        $tenancy->initialized = true;

        $this->withoutMiddleware([
            InitializeTenancyByDomain::class,
            PreventAccessFromCentralDomains::class,
            RevisarSuspensionTenant::class,
            LocaleMiddleware::class,
        ]);
    }

    protected function tearDown(): void
    {
        $tenancy = app(\Stancl\Tenancy\Tenancy::class);
        $tenancy->tenant = null;
        $tenancy->initialized = false;
        DB::purge('zonapagos_test');
        parent::tearDown();
    }

    public function test_iniciar_pago_envia_credenciales_por_tenant_codigo_servicio_1001_y_payload_v5_valido(): void
    {
        Http::fake([
            'https://zonapagos-prod.test/api/InicioPago' => Http::response([
                'int_codigo' => 1,
                'str_url' => 'https://www.zonapagos.com/Ciclo_Pago/Pago.aspx?rut=abc123',
            ], 200),
        ]);

        $escenario = $this->crearEscenarioEscuelas(
            nombreActividad: 'Matrícula Escuela Bíblica de Liderazgo Avanzado Generación 2026 Especial'
        );

        $servicio = app(ZonaPagosService::class);
        $resultado = $servicio->iniciarPago($escenario['pago'], [
            'nombre' => 'María José Rodríguez',
            'apellido' => '',
            'identificacion' => '1018223344',
            'email' => 'maria@iglesia.org',
            'telefono' => '3001234567',
        ], 'ESCUELAS');

        $this->assertTrue($resultado['success']);
        $this->assertSame('https://www.zonapagos.com/Ciclo_Pago/Pago.aspx?rut=abc123', $resultado['payment_url']);

        Http::assertSent(function (ClientRequest $request) use ($escenario): bool {
            $body = $request->data();
            $descripcion = $body['InformacionPago']['str_descripcion_pago'] ?? '';
            $adicionalesConfig = collect($body['AdicionalesConfiguracion'] ?? []);
            $codigo50 = $adicionalesConfig->firstWhere('int_codigo', 50);
            $codigo104 = $adicionalesConfig->firstWhere('int_codigo', 104);

            return $request->url() === 'https://zonapagos-prod.test/api/InicioPago'
                && $body['InformacionSeguridad']['int_id_comercio'] === 34741
                && $body['InformacionSeguridad']['str_usuario'] === 'TENANT_PROD'
                && $body['InformacionSeguridad']['str_clave'] === 'CLAVE_PROD'
                && $body['InformacionSeguridad']['int_modalidad'] === -1
                && $body['InformacionPago']['str_id_pago'] === 'P-'.$escenario['pago']->id
                && $body['InformacionPago']['str_nombre_cliente'] === 'María'
                && $body['InformacionPago']['str_apellido_cliente'] === 'José Rodríguez'
                && $body['InformacionPago']['str_opcional1'] === 'ESCUELAS'
                && mb_strlen($descripcion, 'UTF-8') <= 70
                && mb_check_encoding($descripcion, 'UTF-8')
                && array_key_exists('AdicionalesPago', $body)
                && $body['AdicionalesPago'] === []
                && ($codigo50['str_valor'] ?? null) === '1001'
                && ($codigo104['str_valor'] ?? null) === route('zonapagos.retornoUsuario');
        });
    }

    public function test_credenciales_hacen_fallback_a_config_cuando_tipos_pago_no_las_define(): void
    {
        TipoPago::query()->where('id', 1)->update([
            'enlace' => null,
            'client_id' => null,
            'bussines_id' => null,
            'key_id' => null,
            'account_id' => null,
        ]);

        config(['services.zonapagos.codigo_servicio' => '1001']);

        $credenciales = app(ZonaPagosService::class)->obtenerCredenciales();

        $this->assertSame('https://zonapagos-env.test/api', $credenciales['apiUrl']);
        $this->assertSame(99999, $credenciales['idComercio']);
        $this->assertSame('ENV_USER', $credenciales['usuario']);
        $this->assertSame('ENV_PASS', $credenciales['clave']);
        $this->assertSame('1001', $credenciales['codigoServicio']);
    }

    public function test_parsear_respuesta_verificacion_maneja_separador_final_y_prioriza_aprobado(): void
    {
        $servicio = app(ZonaPagosService::class);

        $tramaRechazo = $this->construirTramaVerificacion('1000', '9001', 'CUS-RECHAZO');
        $tramaAprobada = $this->construirTramaVerificacion('1', '9002', 'CUS-APROBADO-777');

        // Incluye intento rechazado + intento aprobado + separador final vacío '|;|'
        $parseado = $servicio->parsearRespuestaVerificacion($tramaRechazo.' |;| '.$tramaAprobada.' |;| ');

        $this->assertSame('1', $parseado['int_estado_pago']);
        $this->assertSame('9002', $parseado['int_n_pago']);
        $this->assertSame('CUS-APROBADO-777', $parseado['str_codigo_transaccion']);
        $this->assertSame('29', $parseado['int_id_forma_pago']);
    }

    public function test_sonda_protege_matricula_y_cupo_mientras_pago_pse_esta_pendiente_dentro_de_45_minutos(): void
    {
        $escenario = $this->crearEscenarioEscuelas(
            cuposIniciales: 9,
            minutosAtras: 10
        );

        Http::fake([
            'https://zonapagos-prod.test/api/VerificacionPago' => Http::response([
                'int_error' => 0,
                'int_cantidad_pagos' => 1,
                'str_res_pago' => $this->construirTramaVerificacion('999', '7001', 'CUS-PENDIENTE'),
            ], 200),
        ]);

        $exitCode = Artisan::call('pagos:verificar-zonapagos');

        $this->assertSame(0, $exitCode);
        $this->assertSame(5, $escenario['pago']->fresh()->estado_pago_id);
        $this->assertFalse($escenario['matricula']->fresh()->trashed());
        $this->assertSame('pendiente', $escenario['matricula']->fresh()->estado_pago_matricula);
        $this->assertSame(1, MatriculaHorarioMateriaPeriodo::count());
        $this->assertSame(9, $escenario['horario']->fresh()->cupos_disponibles);
        $this->assertSame(1, $escenario['usuario']->fresh()->tipo_usuario_id);
    }

    public function test_sonda_no_expira_pago_tc_4001_pendiente_cr_a_los_45_minutos(): void
    {
        $escenario = $this->crearEscenarioEscuelas(
            cuposIniciales: 9,
            minutosAtras: 65
        );

        Http::fake([
            'https://zonapagos-prod.test/api/VerificacionPago' => Http::response([
                'int_error' => 0,
                'int_cantidad_pagos' => 1,
                'str_res_pago' => $this->construirTramaVerificacion('4001', '7002', 'TICKET-CR', formaPago: '32'),
            ], 200),
        ]);

        Artisan::call('pagos:verificar-zonapagos');

        // Debe actualizar a estado 6 (4001 Pendiente CR) sin borrar la matrícula ni devolver el cupo
        $this->assertSame(6, $escenario['pago']->fresh()->estado_pago_id);
        $this->assertFalse($escenario['matricula']->fresh()->trashed());
        $this->assertSame(9, $escenario['horario']->fresh()->cupos_disponibles);
    }

    public function test_sonda_aprueba_pago_escuelas_actualiza_compra_matricula_y_promueve_tipo_usuario_inicial(): void
    {
        $escenario = $this->crearEscenarioEscuelas(
            cuposIniciales: 9,
            minutosAtras: 12,
            tipoUsuarioInicialMateria: 2
        );

        Http::fake([
            'https://zonapagos-prod.test/api/VerificacionPago' => Http::response([
                'int_error' => 0,
                'int_cantidad_pagos' => 1,
                'str_res_pago' => $this->construirTramaVerificacion('1', '88001', 'CUS-PSE-987654'),
            ], 200),
        ]);

        Artisan::call('pagos:verificar-zonapagos');

        $pago = $escenario['pago']->fresh();
        $compra = $escenario['compra']->fresh();
        $matricula = $escenario['matricula']->fresh();
        $usuario = $escenario['usuario']->fresh();

        $this->assertSame(9, $pago->estado_pago_id);
        $this->assertSame('88001', $pago->referencia_pago);
        $this->assertSame('CUS-PSE-987654', $pago->gateway_transaction_id);
        $this->assertSame(29, (int) $pago->int_id_forma_pago);
        $this->assertSame(Compra::PAGADA, (int) $compra->estado);
        $this->assertFalse($matricula->trashed());
        $this->assertSame('pagada', $matricula->estado_pago_matricula);
        $this->assertSame('150000.00', $matricula->valor_pagado);
        $this->assertNotNull($matricula->fecha_pago);
        $this->assertSame(2, (int) $usuario->tipo_usuario_id);
        $this->assertSame(9, $escenario['horario']->fresh()->cupos_disponibles);
    }

    public function test_sonda_en_rechazo_1000_y_error_ach_1001_libera_matricula_y_restaura_cupo_sin_duplicar(): void
    {
        $escenario1000 = $this->crearEscenarioEscuelas(cuposIniciales: 8, minutosAtras: 10);
        $escenario1001 = $this->crearEscenarioEscuelas(
            cuposIniciales: 5,
            minutosAtras: 10,
            usuarioEmail: 'otro@iglesia.org'
        );

        Http::fake(function (ClientRequest $request) use ($escenario1000, $escenario1001) {
            $idPago = $request->data()['str_id_pago'] ?? '';
            if ($idPago === 'P-'.$escenario1000['pago']->id) {
                return Http::response([
                    'int_error' => 0,
                    'int_cantidad_pagos' => 1,
                    'str_res_pago' => $this->construirTramaVerificacion('1000', '7100', 'CUS-1000'),
                ], 200);
            }

            if ($idPago === 'P-'.$escenario1001['pago']->id) {
                return Http::response([
                    'int_error' => 0,
                    'int_cantidad_pagos' => 1,
                    'str_res_pago' => $this->construirTramaVerificacion('1001', '7101', 'CUS-1001'),
                ], 200);
            }

            return Http::response(['int_error' => -1, 'int_cantidad_pagos' => 0], 200);
        });

        Artisan::call('pagos:verificar-zonapagos');
        // Segunda corrida inmediata para verificar idempotencia (no debe sumar +1 cupo dos veces)
        Artisan::call('pagos:verificar-zonapagos');

        $this->assertSame(4, $escenario1000['pago']->fresh()->estado_pago_id);
        $this->assertSame(Compra::ANULADA, (int) $escenario1000['compra']->fresh()->estado);
        $this->assertTrue($escenario1000['matricula']->fresh()->trashed());
        $this->assertSame(9, $escenario1000['horario']->fresh()->cupos_disponibles);
        $this->assertSame(1, $escenario1000['usuario']->fresh()->tipo_usuario_id);

        $this->assertSame(21, $escenario1001['pago']->fresh()->estado_pago_id);
        $this->assertSame(Compra::ANULADA, (int) $escenario1001['compra']->fresh()->estado);
        $this->assertTrue($escenario1001['matricula']->fresh()->trashed());
        $this->assertSame(6, $escenario1001['horario']->fresh()->cupos_disponibles);
        $this->assertSame(0, MatriculaHorarioMateriaPeriodo::count());
    }

    public function test_sonda_vence_pagos_abandonados_en_pasarela_tras_45_minutos_no_encontrado_y_888(): void
    {
        $noEncontrado = $this->crearEscenarioEscuelas(cuposIniciales: 7, minutosAtras: 46, usuarioEmail: 'abandono1@iglesia.org');
        $atorado888 = $this->crearEscenarioEscuelas(cuposIniciales: 4, minutosAtras: 50, usuarioEmail: 'abandono2@iglesia.org');

        Http::fake(function (ClientRequest $request) use ($noEncontrado, $atorado888) {
            $idPago = $request->data()['str_id_pago'] ?? '';
            if ($idPago === 'P-'.$noEncontrado['pago']->id) {
                return Http::response([
                    'int_error' => -1,
                    'int_cantidad_pagos' => 0,
                    'str_detalle' => 'No se encontraron pagos asociados.',
                    'str_res_pago' => '',
                ], 200);
            }

            if ($idPago === 'P-'.$atorado888['pago']->id) {
                return Http::response([
                    'int_error' => 0,
                    'int_cantidad_pagos' => 1,
                    'str_res_pago' => $this->construirTramaVerificacion('888', '7200', ''),
                ], 200);
            }

            return Http::response(['int_error' => -1, 'int_cantidad_pagos' => 0], 200);
        });

        Artisan::call('pagos:verificar-zonapagos');

        // Ambos deben pasar a ID 16 (Transacción Abandonada), anular compra, soft-delete matrícula y devolver cupo
        $this->assertSame(16, $noEncontrado['pago']->fresh()->estado_pago_id);
        $this->assertSame(Compra::ANULADA, (int) $noEncontrado['compra']->fresh()->estado);
        $this->assertTrue($noEncontrado['matricula']->fresh()->trashed());
        $this->assertSame(8, $noEncontrado['horario']->fresh()->cupos_disponibles);

        $this->assertSame(16, $atorado888['pago']->fresh()->estado_pago_id);
        $this->assertSame(Compra::ANULADA, (int) $atorado888['compra']->fresh()->estado);
        $this->assertTrue($atorado888['matricula']->fresh()->trashed());
        $this->assertSame(5, $atorado888['horario']->fresh()->cupos_disponibles);
        $this->assertSame(0, MatriculaHorarioMateriaPeriodo::count());
    }

    public function test_sonda_expira_borrador_sin_clic_en_pagar_tras_30_minutos_y_conserva_borrador_reciente(): void
    {
        $reciente = $this->crearEscenarioEscuelas(
            cuposIniciales: 10,
            minutosAtras: 10,
            comoBorradorSinPasarela: true,
            usuarioEmail: 'borrador1@iglesia.org'
        );
        $expirado = $this->crearEscenarioEscuelas(
            cuposIniciales: 3,
            minutosAtras: 35,
            comoBorradorSinPasarela: true,
            usuarioEmail: 'borrador2@iglesia.org'
        );

        Http::fake();

        Artisan::call('pagos:verificar-zonapagos');

        // Reciente (10 min): se conserva intacto
        $this->assertFalse($reciente['matricula']->fresh()->trashed());
        $this->assertSame(10, $reciente['horario']->fresh()->cupos_disponibles);
        $this->assertFalse($reciente['pago']->fresh()->trashed());

        // Expirado (35 min >= 30 min): se libera matrícula, se restaura cupo (+1) y se anula compra
        $this->assertTrue($expirado['matricula']->fresh()->trashed());
        $this->assertTrue($expirado['pago']->fresh()->trashed());
        $this->assertSame(Compra::ANULADA, (int) $expirado['compra']->fresh()->estado);
        $this->assertSame(4, $expirado['horario']->fresh()->cupos_disponibles);
        $this->assertSame(1, MatriculaHorarioMateriaPeriodo::count());
    }

    public function test_sonda_limpia_matricula_legacy_huerfana_sin_compra_id_ni_pago_id_cuando_pago_se_abandona(): void
    {
        // Reproduce el caso real de producción previo al fix: matrícula sin compra_id ni pago_id
        $escenario = $this->crearEscenarioEscuelas(
            cuposIniciales: 6,
            minutosAtras: 50,
            matriculaLegacySinFks: true
        );

        Http::fake([
            'https://zonapagos-prod.test/api/VerificacionPago' => Http::response([
                'int_error' => -1,
                'int_cantidad_pagos' => 0,
                'str_detalle' => 'Transacción no localizada',
                'str_res_pago' => '',
            ], 200),
        ]);

        Artisan::call('pagos:verificar-zonapagos');

        $this->assertSame(16, $escenario['pago']->fresh()->estado_pago_id);
        $this->assertTrue($escenario['matricula']->fresh()->trashed());
        $this->assertSame(7, $escenario['horario']->fresh()->cupos_disponibles);
        $this->assertSame(0, MatriculaHorarioMateriaPeriodo::count());
    }

    public function test_error_de_red_en_zonapagos_no_borra_matricula_ni_marca_abandono(): void
    {
        $escenario = $this->crearEscenarioEscuelas(
            cuposIniciales: 9,
            minutosAtras: 90
        );

        Http::fake([
            'https://zonapagos-prod.test/api/VerificacionPago' => Http::response('Service Unavailable', 503),
        ]);

        Artisan::call('pagos:verificar-zonapagos');

        $this->assertSame(5, $escenario['pago']->fresh()->estado_pago_id);
        $this->assertFalse($escenario['matricula']->fresh()->trashed());
        $this->assertSame(9, $escenario['horario']->fresh()->cupos_disponibles);
    }

    public function test_callback_idempotencia_y_retorno_usuario_verifica_antes_de_redirigir(): void
    {
        $escenario = $this->crearEscenarioEscuelas(cuposIniciales: 9, minutosAtras: 5);

        Http::fake([
            'https://zonapagos-prod.test/api/VerificacionPago' => Http::sequence()
                ->push([
                    'int_error' => 0,
                    'int_cantidad_pagos' => 1,
                    'str_res_pago' => $this->construirTramaVerificacion('1', '99100', 'CUS-RET-1'),
                ], 200)
                ->push([
                    'int_error' => 0,
                    'int_cantidad_pagos' => 1,
                    'str_res_pago' => $this->construirTramaVerificacion('1000', '99100', 'CUS-RET-1'),
                ], 200),
        ]);

        // 1. Retorno de usuario debe verificar el pago ANTES de redirigir a compraFinalizada
        $response = $this->get(route('zonapagos.retornoUsuario', ['id_pago' => 'P-'.$escenario['pago']->id]));
        $response->assertRedirect(route('carrito.compraFinalizada', ['pago' => $escenario['pago']->id]));

        $this->assertSame(9, $escenario['pago']->fresh()->estado_pago_id);
        $this->assertSame('pagada', $escenario['matricula']->fresh()->estado_pago_matricula);

        // 2. Callback tardío con rechazo NO puede degradar un pago ya aprobado
        $this->get(route('zonapagos.handleCallback', ['id_pago' => 'P-'.$escenario['pago']->id]))
            ->assertOk();

        $this->assertSame(9, $escenario['pago']->fresh()->estado_pago_id);
        $this->assertSame(Compra::PAGADA, (int) $escenario['compra']->fresh()->estado);
        $this->assertSame('pagada', $escenario['matricula']->fresh()->estado_pago_matricula);
    }

    public function test_reintento_en_checkout_tras_rechazo_crea_nuevo_pago_con_str_id_pago_unico(): void
    {
        $escenario = $this->crearEscenarioEscuelas(cuposIniciales: 9, minutosAtras: 5);
        $primerPago = $escenario['pago'];

        // Marcamos el primer pago como rechazado (ID 4)
        $primerPago->update(['estado_pago_id' => 4]);

        Http::fake([
            'https://zonapagos-prod.test/api/InicioPago' => Http::response([
                'int_codigo' => 1,
                'str_url' => 'https://www.zonapagos.com/Ciclo_Pago/Pago.aspx?rut=reintento456',
            ], 200),
        ]);

        $this->actingAs($escenario['usuario']);

        Livewire::test(Checkout::class, [
            'actividad' => $escenario['actividad'],
            'compra' => $escenario['compra'],
        ])
            ->set('tipoPagoSeleccionado', 1)
            ->call('procesarPago')
            ->assertSet('esperandoPagoZonaPagos', true)
            ->assertDispatched('abrirPasarelaZonaPagos');

        $pagos = Pago::where('compra_id', $escenario['compra']->id)->orderBy('id')->get();
        $this->assertCount(2, $pagos);

        $nuevoPago = $pagos->last();
        $this->assertNotSame($primerPago->id, $nuevoPago->id);
        $this->assertSame(5, $nuevoPago->estado_pago_id);
        $this->assertSame('https://www.zonapagos.com/Ciclo_Pago/Pago.aspx?rut=reintento456', $nuevoPago->payment_url);

        Http::assertSent(function (ClientRequest $request) use ($nuevoPago): bool {
            return $request->data()['InformacionPago']['str_id_pago'] === 'P-'.$nuevoPago->id;
        });
    }

    public function test_scheduler_tiene_registrada_la_sonda_cada_5_minutos(): void
    {
        $schedule = app(Schedule::class);

        $eventoSonda = collect($schedule->events())->first(function ($event): bool {
            return str_contains((string) $event->command, 'pagos:verificar-zonapagos');
        });

        $this->assertNotNull($eventoSonda, 'El comando pagos:verificar-zonapagos debe estar programado en routes/console.php.');
        $this->assertSame('*/5 * * * *', $eventoSonda->expression);
        $this->assertTrue($eventoSonda->withoutOverlapping);
    }

    public function test_sonda_acepta_parametros_minutos_pasarela_y_minutos_borrador_para_pruebas_controladas(): void
    {
        $pasarela = $this->crearEscenarioEscuelas(cuposIniciales: 8, minutosAtras: 2, usuarioEmail: 'fast1@iglesia.org');
        $borrador = $this->crearEscenarioEscuelas(
            cuposIniciales: 4,
            minutosAtras: 2,
            comoBorradorSinPasarela: true,
            usuarioEmail: 'fast2@iglesia.org'
        );

        Http::fake([
            'https://zonapagos-prod.test/api/VerificacionPago' => Http::response([
                'int_error' => -1,
                'int_cantidad_pagos' => 0,
                'str_detalle' => 'No encontrado',
                'str_res_pago' => '',
            ], 200),
        ]);

        Artisan::call('pagos:verificar-zonapagos', [
            '--minutos-pasarela' => 1,
            '--minutos-borrador' => 1,
        ]);

        $this->assertSame(16, $pasarela['pago']->fresh()->estado_pago_id);
        $this->assertTrue($pasarela['matricula']->fresh()->trashed());
        $this->assertSame(9, $pasarela['horario']->fresh()->cupos_disponibles);

        $this->assertTrue($borrador['matricula']->fresh()->trashed());
        $this->assertSame(5, $borrador['horario']->fresh()->cupos_disponibles);
    }

    public function test_polling_en_checkout_detecta_rechazo_libera_matricula_y_restaura_cupo(): void
    {
        $escenario = $this->crearEscenarioEscuelas(cuposIniciales: 9, minutosAtras: 3);

        Http::fake([
            'https://zonapagos-prod.test/api/VerificacionPago' => Http::response([
                'int_error' => 0,
                'int_cantidad_pagos' => 1,
                'str_res_pago' => $this->construirTramaVerificacion('1000', '9100', 'CUS-POLL-RECHAZO'),
            ], 200),
        ]);

        $this->actingAs($escenario['usuario']);

        Livewire::test(Checkout::class, [
            'actividad' => $escenario['actividad'],
            'compra' => $escenario['compra'],
        ])
            ->assertSet('esperandoPagoZonaPagos', true)
            ->call('consultarEstadoPagoAuto')
            ->assertRedirect(route('actividades.perfil', $escenario['actividad']));

        $this->assertSame(4, $escenario['pago']->fresh()->estado_pago_id);
        $this->assertSame(Compra::ANULADA, (int) $escenario['compra']->fresh()->estado);
        $this->assertTrue($escenario['matricula']->fresh()->trashed());
        $this->assertSame(10, $escenario['horario']->fresh()->cupos_disponibles);
    }

    public function test_escuelas_carrito_mount_no_borra_matricula_con_pago_pse_pendiente_activo(): void
    {
        $escenario = $this->crearEscenarioEscuelas(cuposIniciales: 9, minutosAtras: 5);

        $this->actingAs($escenario['usuario']);

        $componente = new \App\Livewire\Carrito\EscuelasCarrito;
        $componente->mount($escenario['actividad'], $escenario['compra'], 0, collect());

        $this->assertFalse($escenario['matricula']->fresh()->trashed());
        $this->assertSame(9, $escenario['horario']->fresh()->cupos_disponibles);
    }

    public function test_checkout_no_colisiona_con_referencia_pago_unique_de_otra_matricula_previa(): void
    {
        $escenario = $this->crearEscenarioEscuelas(
            cuposIniciales: 9,
            minutosAtras: 2,
            comoBorradorSinPasarela: true
        );

        // Simulamos el escenario real de producción:
        // La matrícula actual se vincula por compra_id/pago_id (sin referencia_pago),
        // mientras otra matrícula histórica (ej. Taquilla o eliminada) ya ocupa referencia_pago = pago->id.
        $escenario['matricula']->update(['referencia_pago' => null]);

        $matriculaHistorica = Matricula::query()->create([
            'user_id' => 999,
            'periodo_id' => 1,
            'horario_materia_periodo_id' => $escenario['horario']->id,
            'referencia_pago' => (string) $escenario['pago']->id,
            'estado_pago_matricula' => 'pagada',
            'valor_a_pagar' => 150000,
        ]);
        $matriculaHistorica->delete(); // Soft-delete: en PostgreSQL el índice UNIQUE sigue protegiendo el valor

        Http::fake([
            'https://zonapagos-prod.test/api/InicioPago' => Http::response([
                'int_codigo' => 1,
                'str_url' => 'https://www.zonapagos.com/Ciclo_Pago/Pago.aspx?rut=sincolision',
            ], 200),
        ]);

        $this->actingAs($escenario['usuario']);

        Livewire::test(Checkout::class, [
            'actividad' => $escenario['actividad'],
            'compra' => $escenario['compra'],
        ])
            ->set('tipoPagoSeleccionado', 1)
            ->call('procesarPago')
            ->assertSet('esperandoPagoZonaPagos', true)
            ->assertDispatched('abrirPasarelaZonaPagos');

        $matriculaActual = $escenario['matricula']->fresh();
        $this->assertSame($escenario['compra']->id, (int) $matriculaActual->compra_id);
        $this->assertSame($escenario['pago']->id, (int) $matriculaActual->pago_id);
        $this->assertNull($matriculaActual->referencia_pago);
        $this->assertSame(5, (int) $matriculaActual->estado_pago_id);
    }

    public function test_sede_matricula_prioriza_la_sede_real_del_aula_frente_al_default_2(): void
    {
        Schema::create('sedes', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre');
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('sedes')->insert([
            ['id' => 2, 'nombre' => 'z Auditorio principal'],
            ['id' => 14, 'nombre' => 'Norte'],
        ]);

        DB::table('aulas')->insert([
            'id' => 10,
            'nombre' => 'Virtual Templo - Norte',
            'sede_id' => 14,
        ]);

        DB::table('horarios_base')->insert([
            'id' => 1,
            'dia' => 1,
            'hora_inicio' => '19:00:00',
            'hora_fin' => '21:00:00',
            'aula_id' => 10,
            'capacidad' => 30,
        ]);

        $escenario = $this->crearEscenarioEscuelas(cuposIniciales: 9, minutosAtras: 5);

        // Simulamos matrícula histórica que quedó con el default(2) en matriculas.sede_id
        $escenario['matricula']->update(['sede_id' => 2]);

        $matricula = $escenario['matricula']->fresh()->load([
            'sede',
            'horarioMateriaPeriodo.horarioBase.aula.sede',
        ]);

        $sedeResuelta = $matricula->horarioMateriaPeriodo?->horarioBase?->aula?->sede?->nombre
            ?? $matricula->sede?->nombre
            ?? 'Sin sede matrícula';

        $this->assertSame('Norte', $sedeResuelta);
        $this->assertSame(14, (int) $matricula->horarioMateriaPeriodo->horarioBase->aula->sede_id);
    }

    /**
     * Construye una trama válida `str_res_pago` conforme a la Tabla 12 del manual ZonaPagos v5.0.
     */
    private function construirTramaVerificacion(
        string $codigoEstado,
        string $numeroPago,
        string $cusOTicket,
        string $formaPago = '29',
        string $tipoCompra = 'ESCUELAS'
    ): string {
        $campos = [
            0 => '55001',              // int_ped_numero
            1 => $numeroPago,          // int_n_pago
            2 => '0',                  // int_pago_parcial
            3 => $codigoEstado === '1' ? '1' : '200', // int_pago_terminado
            4 => $codigoEstado,        // int_estado_pago
            5 => '150000',             // dbl_valor_pagado
            6 => '150000',             // dbl_total_pago
            7 => '0',                  // dbl_valor_iva_pagado
            8 => 'Pago ESCUELAS',      // str_descripcion
            9 => '1018223344',         // str_id_cliente
            10 => 'María',             // str_nombre
            11 => 'Rodríguez',         // str_apellido
            12 => '3001234567',        // str_telefono
            13 => 'maria@iglesia.org', // str_email
            14 => $tipoCompra,         // str_campo1
            15 => '',                  // str_campo2
            16 => '',                  // str_campo3
            17 => '',                  // str_campo4
            18 => '',                  // str_campo5
            19 => '2026-11-20 14:30:00', // dat_fecha
            20 => $formaPago,          // int_id_forma_pago (29=PSE, 32=TC)
            21 => $cusOTicket,         // str_ticketID
            22 => '1001',              // int_codigo_servicio
            23 => '1007',              // int_codigo_banco
            24 => 'BANCOLOMBIA',       // str_nombre_banco
            25 => $cusOTicket,         // str_codigo_transaccion (CUS)
            26 => '1',                 // int_ciclo_transaccion
        ];

        return implode(' | ', $campos);
    }

    /**
     * Crea un escenario completo de Escuelas (Usuario, Actividad, Horario, Compra, Pago, Matrícula, EstadoAcadémico).
     *
     * @return array<string, mixed>
     */
    private function crearEscenarioEscuelas(
        int $cuposIniciales = 10,
        int $minutosAtras = 10,
        bool $comoBorradorSinPasarela = false,
        bool $matriculaLegacySinFks = false,
        ?int $tipoUsuarioInicialMateria = null,
        string $nombreActividad = 'Escuela Bíblica 2026',
        string $usuarioEmail = 'estudiante@iglesia.org'
    ): array {
        $usuario = User::query()->create([
            'primer_nombre' => 'María',
            'segundo_nombre' => 'José',
            'primer_apellido' => 'Rodríguez',
            'segundo_apellido' => 'Gómez',
            'identificacion' => '1018223344',
            'telefono_movil' => '3001234567',
            'email' => $usuarioEmail,
            'tipo_usuario_id' => 1,
        ]);

        $materia = Materia::query()->create([
            'nombre' => 'Fundamentos I',
            'tipo_usuario_inicial_id' => $tipoUsuarioInicialMateria,
        ]);

        $materiaPeriodo = MateriaPeriodo::query()->create([
            'materia_id' => $materia->id,
            'periodo_id' => 1,
        ]);

        $horario = HorarioMateriaPeriodo::query()->create([
            'materia_periodo_id' => $materiaPeriodo->id,
            'horario_base_id' => 1,
            'habilitado' => true,
            'cupos_disponibles' => $cuposIniciales,
        ]);

        $actividad = Actividad::query()->create([
            'nombre' => $nombreActividad,
            'tipo_actividad_id' => 1,
            'terminos_y_condiciones' => null,
        ]);
        DB::table('actividad_tipos_pago')->insert([
            'actividad_id' => $actividad->id,
            'tipo_pago_id' => 1,
        ]);

        $fechaCreacion = now()->subMinutes($minutosAtras);

        $compra = Compra::query()->create([
            'user_id' => $usuario->id,
            'actividad_id' => $actividad->id,
            'moneda_id' => 1,
            'valor' => 150000,
            'estado' => $comoBorradorSinPasarela ? Compra::PENDIENTE : Compra::EN_PASARELA,
            'fecha' => $fechaCreacion->toDateString(),
            'nombre_completo_comprador' => 'María Rodríguez',
            'identificacion_comprador' => '1018223344',
            'email_comprador' => $usuarioEmail,
            'telefono_comprador' => '3001234567',
            'metodo_pago_id' => $comoBorradorSinPasarela ? 0 : 1,
        ]);

        $pago = Pago::query()->create([
            'compra_id' => $compra->id,
            'moneda_id' => 1,
            'tipo_pago_id' => $comoBorradorSinPasarela ? null : 1,
            'estado_pago_id' => $comoBorradorSinPasarela ? null : 5,
            'valor' => 150000,
            'fecha' => $fechaCreacion->toDateString(),
            'payment_url' => $comoBorradorSinPasarela ? null : 'https://www.zonapagos.com/Ciclo_Pago/Pago.aspx?rut=test',
        ]);
        DB::table('pagos')->where('id', $pago->id)->update([
            'created_at' => $fechaCreacion,
            'updated_at' => $fechaCreacion,
        ]);
        $pago->refresh();

        $matricula = Matricula::query()->create([
            'user_id' => $usuario->id,
            'periodo_id' => 1,
            'horario_materia_periodo_id' => $horario->id,
            'compra_id' => $matriculaLegacySinFks ? null : $compra->id,
            'pago_id' => $matriculaLegacySinFks ? null : $pago->id,
            'referencia_pago' => (string) $pago->id,
            'estado_pago_matricula' => 'pendiente',
            'valor_a_pagar' => 150000,
            'tipo_pago_id' => $comoBorradorSinPasarela ? null : 1,
            'estado_pago_id' => $comoBorradorSinPasarela ? null : 5,
            'fecha_matricula' => $fechaCreacion->toDateString(),
        ]);
        DB::table('matriculas')->where('id', $matricula->id)->update([
            'created_at' => $fechaCreacion,
            'updated_at' => $fechaCreacion,
        ]);
        $matricula->refresh();

        $estadoAcademico = MatriculaHorarioMateriaPeriodo::query()->create([
            'user_id' => $usuario->id,
            'horario_materia_periodo_id' => $horario->id,
            'matricula_id' => $matricula->id,
            'periodo_id' => 1,
        ]);

        return [
            'usuario' => $usuario,
            'materia' => $materia,
            'horario' => $horario,
            'actividad' => $actividad,
            'compra' => $compra,
            'pago' => $pago,
            'matricula' => $matricula,
            'estadoAcademico' => $estadoAcademico,
        ];
    }

    private function crearEsquemaDePrueba(): void
    {
        Schema::create('configuraciones', function (Blueprint $table): void {
            $table->id();
            $table->boolean('envio_material')->default(false);
            $table->timestamps();
        });

        Schema::create('monedas', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre');
            $table->string('nombre_corto')->default('COP');
            $table->string('simbolo')->default('$');
            $table->timestamps();
        });

        Schema::create('tipo_usuarios', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre');
            $table->integer('puntaje')->default(0);
            $table->unsignedBigInteger('id_rol_dependiente')->nullable();
            $table->timestamps();
        });

        Schema::create('roles', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('guard_name')->default('web');
            $table->boolean('dependiente')->default(false);
            $table->timestamps();
        });

        Schema::create('model_has_roles', function (Blueprint $table): void {
            $table->unsignedBigInteger('role_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->boolean('activo')->default(false);
            $table->boolean('dependiente')->default(false);
        });

        Schema::create('bitacora_tipos_usuarios', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('tipo_usuario_id_anterior')->nullable();
            $table->unsignedBigInteger('tipo_usuario_id_nuevo')->nullable();
            $table->unsignedBigInteger('autor_id')->nullable();
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('primer_nombre')->nullable();
            $table->string('segundo_nombre')->nullable();
            $table->string('primer_apellido')->nullable();
            $table->string('segundo_apellido')->nullable();
            $table->string('identificacion')->nullable();
            $table->string('telefono_movil')->nullable();
            $table->string('email')->nullable();
            $table->unsignedBigInteger('tipo_usuario_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('tipos_pago', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre');
            $table->string('enlace')->nullable();
            $table->string('client_id')->nullable();
            $table->string('key_id')->nullable();
            $table->string('bussines_id')->nullable();
            $table->string('account_id')->nullable();
            $table->string('key_reservada')->nullable();
            $table->string('fondo')->nullable();
            $table->boolean('activo')->default(true);
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });

        Schema::create('estados_pago', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre');
            $table->string('color')->nullable();
            $table->unsignedBigInteger('tipo_pago_id')->nullable();
            $table->boolean('estado_inicial_defecto')->default(false);
            $table->boolean('estado_final_inscripcion')->default(false);
            $table->string('id_codigo_externo')->nullable();
            $table->boolean('estado_anulado_inscripcion')->default(false);
            $table->boolean('estado_pendiente')->default(false);
        });

        Schema::create('tipos_actividad', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre');
            $table->boolean('tipo_escuelas')->default(false);
            $table->boolean('permite_abonos')->default(false);
            $table->timestamps();
        });

        Schema::create('actividades', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre');
            $table->unsignedBigInteger('tipo_actividad_id')->nullable();
            $table->text('terminos_y_condiciones')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('actividad_tipos_pago', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('actividad_id');
            $table->unsignedBigInteger('tipo_pago_id');
            $table->timestamps();
        });

        Schema::create('actividad_monedas', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('actividad_id');
            $table->unsignedBigInteger('moneda_id');
            $table->timestamps();
        });

        Schema::create('elementos_formulario_actividad', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('actividad_id');
            $table->integer('orden')->default(1);
            $table->boolean('required')->default(false);
            $table->unsignedBigInteger('tipo_elemento_id')->nullable();
            $table->string('titulo')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('respuestas_formulario_elemento_compra', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('compra_id')->nullable();
            $table->unsignedBigInteger('elemento_formulario_actividad_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
        });

        Schema::create('compras', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('actividad_id')->nullable();
            $table->unsignedBigInteger('moneda_id')->nullable();
            $table->decimal('valor', 12, 2)->default(0);
            $table->integer('estado')->default(1);
            $table->string('nombre_completo_comprador')->nullable();
            $table->string('identificacion_comprador')->nullable();
            $table->string('email_comprador')->nullable();
            $table->string('telefono_comprador')->nullable();
            $table->integer('metodo_pago_id')->default(0);
            $table->date('fecha')->nullable();
            $table->boolean('anulacion_solicitada')->default(false);
            $table->text('listado_carrito')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('pagos', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('compra_id')->nullable();
            $table->unsignedBigInteger('moneda_id')->nullable();
            $table->unsignedBigInteger('tipo_pago_id')->nullable();
            $table->unsignedBigInteger('estado_pago_id')->nullable();
            $table->decimal('valor', 12, 2)->default(0);
            $table->decimal('comision', 12, 2)->nullable();
            $table->decimal('valor_neto', 12, 2)->nullable();
            $table->boolean('anulado_pdp')->default(false);
            $table->string('referencia_pago')->nullable();
            $table->text('payment_url')->nullable();
            $table->string('gateway_transaction_id')->nullable();
            $table->integer('int_id_forma_pago')->nullable();
            $table->json('gateway_response')->nullable();
            $table->date('fecha')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('inscripciones', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('compra_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->integer('estado')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('actividad_carritos_compra', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('compra_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('actividad_id')->nullable();
            $table->unsignedBigInteger('actividad_categoria_id')->nullable();
            $table->integer('cantidad')->default(1);
            $table->decimal('precio', 12, 2)->default(0);
            $table->unsignedBigInteger('pago_id')->nullable();
            $table->date('fecha')->nullable();
            $table->timestamps();
        });

        Schema::create('actividad_campos_adicionales_compra', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('compra_id')->nullable();
            $table->text('respuesta')->nullable();
            $table->timestamps();
        });

        Schema::create('materias', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre');
            $table->unsignedBigInteger('nivel_id')->nullable();
            $table->unsignedBigInteger('tipo_usuario_inicial_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('materia_paso_crecimiento', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('materia_id');
            $table->unsignedBigInteger('paso_crecimiento_id');
            $table->integer('al_iniciar')->default(0);
            $table->integer('indice')->default(0);
            $table->unsignedBigInteger('estado_paso_crecimiento_usuario_id')->nullable();
            $table->integer('estado')->nullable();
        });

        Schema::create('pasos_crecimiento', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre');
            $table->timestamps();
        });

        Schema::create('materia_tarea_iniciar', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('materia_id');
            $table->unsignedBigInteger('tarea_consolidacion_id')->nullable();
            $table->unsignedBigInteger('estado_tarea_consolidacion_id')->nullable();
            $table->integer('indice')->default(0);
            $table->timestamps();
        });

        Schema::create('aulas', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre')->default('Aula 101');
            $table->unsignedBigInteger('sede_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('horarios_base', function (Blueprint $table): void {
            $table->id();
            $table->integer('dia')->default(1);
            $table->string('hora_inicio')->default('08:00:00');
            $table->string('hora_fin')->default('10:00:00');
            $table->unsignedBigInteger('aula_id')->nullable();
            $table->integer('capacidad')->default(20);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('materia_periodo', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('materia_id');
            $table->unsignedBigInteger('periodo_id');
            $table->timestamps();
        });

        Schema::create('horarios_materia_periodo', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('materia_periodo_id');
            $table->unsignedBigInteger('horario_base_id')->nullable();
            $table->boolean('habilitado')->default(true);
            $table->integer('cupos_disponibles')->default(10);
            $table->timestamps();
        });

        Schema::create('matriculas', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('periodo_id')->nullable();
            $table->unsignedBigInteger('horario_materia_periodo_id')->nullable();
            $table->unsignedBigInteger('compra_id')->nullable();
            $table->unsignedBigInteger('pago_id')->nullable();
            $table->string('referencia_pago')->nullable()->unique();
            $table->string('estado_pago_matricula')->default('pendiente');
            $table->decimal('valor_a_pagar', 12, 2)->default(0);
            $table->decimal('valor_pagado', 12, 2)->nullable();
            $table->timestamp('fecha_pago')->nullable();
            $table->unsignedBigInteger('tipo_pago_id')->nullable();
            $table->unsignedBigInteger('estado_pago_id')->nullable();
            $table->date('fecha_matricula')->nullable();
            $table->unsignedBigInteger('material_sede_id')->nullable();
            $table->unsignedBigInteger('sede_id')->nullable();
            $table->unsignedBigInteger('escuela_id')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('matricula_horario_materia_periodo', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('horario_materia_periodo_id')->nullable();
            $table->unsignedBigInteger('matricula_id');
            $table->unsignedBigInteger('periodo_id')->nullable();
            $table->timestamps();
        });

        Schema::create('ofrendas', function (Blueprint $table): void {
            $table->id();
            $table->integer('ingresada_por')->default(1);
            $table->unsignedBigInteger('estado_pago_id')->nullable();
            $table->unsignedBigInteger('tipo_pago_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    private function sembrarCatalogosBase(): void
    {
        Configuracion::query()->create(['id' => 1, 'envio_material' => false]);
        Moneda::query()->create(['id' => 1, 'nombre' => 'Peso Colombiano', 'nombre_corto' => 'COP', 'simbolo' => '$']);

        DB::table('tipo_usuarios')->insert([
            ['id' => 1, 'nombre' => 'Miembro', 'puntaje' => 1],
            ['id' => 2, 'nombre' => 'Estudiante Escuela', 'puntaje' => 2],
        ]);

        TipoActividad::query()->create([
            'id' => 1,
            'nombre' => 'Escuelas',
            'tipo_escuelas' => true,
            'permite_abonos' => false,
        ]);

        TipoPago::query()->create([
            'id' => 1,
            'nombre' => 'ZonaPagos PSE',
            'key_reservada' => 'zona',
            'enlace' => 'https://zonapagos-prod.test/api',
            'client_id' => '34741',
            'bussines_id' => 'TENANT_PROD',
            'key_id' => 'CLAVE_PROD',
            'account_id' => '1001',
            'activo' => true,
        ]);

        EstadoPago::query()->insert([
            [
                'id' => 5,
                'nombre' => 'Pago Pendiente por Finalizar',
                'color' => '#f39c12',
                'tipo_pago_id' => 1,
                'estado_inicial_defecto' => true,
                'estado_final_inscripcion' => false,
                'id_codigo_externo' => '999',
                'estado_anulado_inscripcion' => false,
                'estado_pendiente' => true,
            ],
            [
                'id' => 9,
                'nombre' => 'Pago Finalizado OK',
                'color' => '#00a65a',
                'tipo_pago_id' => 1,
                'estado_inicial_defecto' => false,
                'estado_final_inscripcion' => true,
                'id_codigo_externo' => '1',
                'estado_anulado_inscripcion' => false,
                'estado_pendiente' => false,
            ],
            [
                'id' => 4,
                'nombre' => 'Pago Rechazado',
                'color' => '#dd4b39',
                'tipo_pago_id' => 1,
                'estado_inicial_defecto' => false,
                'estado_final_inscripcion' => false,
                'id_codigo_externo' => '1000',
                'estado_anulado_inscripcion' => true,
                'estado_pendiente' => false,
            ],
            [
                'id' => 21,
                'nombre' => 'Error ACH-Banco (Rechazado)',
                'color' => '#dd4b39',
                'tipo_pago_id' => 1,
                'estado_inicial_defecto' => false,
                'estado_final_inscripcion' => false,
                'id_codigo_externo' => '1001',
                'estado_anulado_inscripcion' => true,
                'estado_pendiente' => false,
            ],
            [
                'id' => 6,
                'nombre' => 'Pendiente por CR (TC)',
                'color' => '#f39c12',
                'tipo_pago_id' => 1,
                'estado_inicial_defecto' => false,
                'estado_final_inscripcion' => false,
                'id_codigo_externo' => '4001',
                'estado_anulado_inscripcion' => false,
                'estado_pendiente' => true,
            ],
            [
                'id' => 39,
                'nombre' => 'Pendiente por Iniciar',
                'color' => '#f39c12',
                'tipo_pago_id' => 1,
                'estado_inicial_defecto' => false,
                'estado_final_inscripcion' => false,
                'id_codigo_externo' => '888',
                'estado_anulado_inscripcion' => false,
                'estado_pendiente' => true,
            ],
            [
                'id' => 16,
                'nombre' => 'Transacción Abandonada',
                'color' => '#dd4d39',
                'tipo_pago_id' => 1,
                'estado_inicial_defecto' => false,
                'estado_final_inscripcion' => false,
                'id_codigo_externo' => null,
                'estado_anulado_inscripcion' => true,
                'estado_pendiente' => false,
            ],
        ]);
    }
}
