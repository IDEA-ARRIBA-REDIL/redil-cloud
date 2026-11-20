<?php

namespace Tests\Feature;

use App\Http\Middleware\LocaleMiddleware;
use App\Http\Middleware\RevisarSuspensionTenant;
use App\Livewire\Ofrendas\DonacionesOnline;
use App\Mail\DefaultMail;
use App\Models\Configuracion;
use App\Models\Destinatario;
use App\Models\Iglesia;
use App\Models\Moneda;
use App\Models\Ofrenda;
use App\Models\Pais;
use App\Models\Tenant;
use App\Models\TipoIdentificacion;
use App\Models\TipoOfrenda;
use App\Models\TipoPago;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use Tests\TestCase;

class DonacionesOnlineTest extends TestCase
{
    protected function setUp(): void
    {
        putenv('VIEW_COMPILED_PATH='.sys_get_temp_dir());
        $_ENV['VIEW_COMPILED_PATH'] = sys_get_temp_dir();
        $_SERVER['VIEW_COMPILED_PATH'] = sys_get_temp_dir();

        parent::setUp();

        config([
            'database.default' => 'donaciones_test',
            'database.connections.donaciones_test' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
            ],
            'cache.default' => 'array',
            'session.driver' => 'array',
            'logging.default' => 'null',
            'view.compiled' => sys_get_temp_dir(),
        ]);

        DB::purge('donaciones_test');

        $this->crearEsquemaDePrueba();
        $this->sembrarCatalogosBase();

        $tenant = new Tenant;
        $tenant->id = 'crecer_test';
        app(\Stancl\Tenancy\Tenancy::class)->tenant = $tenant;

        $this->withoutMiddleware([
            InitializeTenancyByDomain::class,
            PreventAccessFromCentralDomains::class,
            RevisarSuspensionTenant::class,
            LocaleMiddleware::class,
        ]);
    }

    protected function tearDown(): void
    {
        app(\Stancl\Tenancy\Tenancy::class)->tenant = null;
        DB::purge('donaciones_test');
        parent::tearDown();
    }

    public function test_usuario_registrado_carga_datos_bloqueados_y_controla_boton_actualizar_segun_sesion(): void
    {
        $usuario = User::query()->create([
            'primer_nombre' => 'Carlos',
            'segundo_nombre' => 'Andrés',
            'primer_apellido' => 'Gómez',
            'segundo_apellido' => 'Pérez',
            'identificacion' => '1020304050',
            'tipo_identificacion_id' => 1,
            'telefono_movil' => '3001112233',
            'email' => 'carlos@iglesia.org',
            'pais_id' => 1,
            'direccion' => 'Calle 10 # 20-30',
        ]);

        // Caso 1: Usuario NO logueado -> carga datos bloqueados, NO muestra botón "Actualizar datos" y muestra aviso
        Livewire::test(DonacionesOnline::class)
            ->set('identificacionBusqueda', '1020304050')
            ->call('buscarUsuarioRegistrado')
            ->assertSet('pestanaActiva', 2)
            ->assertSet('esRegistrado', true)
            ->assertSet('userId', $usuario->id)
            ->assertSet('usuarioEstaLogueado', false)
            ->assertSet('email', 'carlos@iglesia.org')
            ->assertSee('debes ingresar a tu cuenta en la plataforma y actualizar tus datos')
            ->assertDontSee('Actualizar datos');

        // Caso 2: Usuario SÍ está logueado -> muestra botón "Actualizar datos"
        $this->actingAs($usuario);

        Livewire::test(DonacionesOnline::class)
            ->set('identificacionBusqueda', '1020304050')
            ->call('buscarUsuarioRegistrado')
            ->assertSet('pestanaActiva', 2)
            ->assertSet('esRegistrado', true)
            ->assertSet('usuarioEstaLogueado', true)
            ->assertSee('Actualizar datos');
    }

    public function test_donante_independiente_exige_campos_obligatorios_y_filtra_destinatarios_por_tipo_ofrenda(): void
    {
        $tipoDiezmo = TipoOfrenda::where('nombre', 'Diezmos')->firstOrFail();
        $destinatarioSedeNorte = Destinatario::where('nombre', 'Sede Norte')->firstOrFail();

        Livewire::test(DonacionesOnline::class)
            ->call('continuarComoIndependiente')
            ->assertSet('pestanaActiva', 2)
            ->assertSet('esRegistrado', false)
            ->call('continuarAPestana3')
            ->assertHasErrors([
                'nombreCompleto',
                'identificacion',
                'telefono',
                'email',
                'paisId',
                'direccion',
                'aceptaTerminos',
            ])
            ->set('nombreCompleto', 'María Fernanda López')
            ->set('tipoIdentificacionId', 1)
            ->set('identificacion', '987654321')
            ->set('telefono', '3109998877')
            ->set('email', 'maria@ejemplo.com')
            ->set('paisId', 1)
            ->set('direccion', 'Carrera 50 # 80-10')
            ->set('aceptaTerminos', true)
            ->call('continuarAPestana3')
            ->assertHasNoErrors()
            ->assertSet('pestanaActiva', 3)
            ->set('tipoOfrendaId', $tipoDiezmo->id)
            ->assertSet('destinatarioId', $destinatarioSedeNorte->id);
    }

    public function test_flujo_completo_zonapagos_prefijo_o_retorno_y_envio_unico_de_correo_default_mail(): void
    {
        Mail::fake();

        Http::fake([
            'https://zonapagos.test/api/InicioPago' => Http::response([
                'int_codigo' => 1,
                'str_url' => 'https://www.zonapagos.com/Ciclo_Pago/Pago.aspx?rut=token-ofrenda-123',
            ], 200),
            'https://zonapagos.test/api/VerificacionPago' => Http::response([
                'int_error' => 0,
                // Posiciones según Tabla 12: 0=ped, 1=n_pago, 2=parcial, 3=terminado(1), 4=estado(1=Aprobado), 5=valor(50000), ..., 20=forma_pago(29=PSE), 21=ticketID
                'str_res_pago' => '100|555|0|1|1|50000|50000|0|Donacion Diezmos|987654321|Maria|Lopez|3109998877|maria@ejemplo.com|OFRENDA|1|1|||2026-11-05|29|CUS-999888|1001|1022|BANCOLOMBIA|CUS-999888|1',
            ], 200),
        ]);

        $tipoDiezmo = TipoOfrenda::where('nombre', 'Diezmos')->firstOrFail();
        $destinatario = Destinatario::where('nombre', 'Sede Norte')->firstOrFail();

        Livewire::test(DonacionesOnline::class)
            ->call('continuarComoIndependiente')
            ->set('nombreCompleto', 'María Fernanda López')
            ->set('tipoIdentificacionId', 1)
            ->set('identificacion', '987654321')
            ->set('telefono', '3109998877')
            ->set('email', 'maria@ejemplo.com')
            ->set('paisId', 1)
            ->set('direccion', 'Carrera 50 # 80-10')
            ->set('aceptaTerminos', true)
            ->call('continuarAPestana3')
            ->set('tipoOfrendaId', $tipoDiezmo->id)
            ->set('destinatarioId', $destinatario->id)
            ->set('monedaId', 1)
            ->set('valorDonacion', '50000')
            ->call('procesarDonacion')
            ->assertRedirect('https://www.zonapagos.com/Ciclo_Pago/Pago.aspx?rut=token-ofrenda-123');

        $ofrenda = Ofrenda::firstOrFail();
        $this->assertSame($destinatario->id, $ofrenda->destinatarioRelacion?->id);
        $this->assertSame(3, (int) $ofrenda->ingresada_por);
        $this->assertSame('María Fernanda López', $ofrenda->nombre_donante);
        $this->assertFalse($ofrenda->correo_confirmacion_enviado);

        // Verificar que se envió el prefijo O-{id} y las credenciales del tenant desde TipoPago
        Http::assertSent(function (ClientRequest $request) use ($ofrenda, $destinatario): bool {
            return $request->url() === 'https://zonapagos.test/api/InicioPago'
                && $request['InformacionPago']['str_id_pago'] === 'O-'.$ofrenda->id
                && $request['InformacionPago']['str_opcional1'] === 'OFRENDA'
                && $request['InformacionPago']['str_opcional3'] === (string) $destinatario->id
                && $request['InformacionSeguridad']['int_id_comercio'] === 777888;
        });

        // Simular el retorno del usuario a la ruta única de ZonaPagos con id_pago=O-{id}
        $responseRetorno = $this->get(route('zonapagos.retornoUsuario', ['id_pago' => 'O-'.$ofrenda->id]));
        $responseRetorno->assertRedirect(route('ofrendas.donacion.finalizada', ['ofrenda' => $ofrenda->id]));

        $ofrenda->refresh();
        $this->assertTrue((bool) $ofrenda->estadoPago?->estado_final_inscripcion);
        $this->assertTrue($ofrenda->correo_confirmacion_enviado);
        $this->assertSame('CUS-999888', $ofrenda->referencia_donacion);

        // Verificar que se envió DefaultMail exactamente 1 vez con el mensaje configurado en Configuracion
        Mail::assertSent(DefaultMail::class, 1);
        Mail::assertSent(DefaultMail::class, function (DefaultMail $mail): bool {
            return $mail->hasTo('maria@ejemplo.com')
                && str_contains($mail->mailData->mensaje, 'Mensaje personalizado de agradecimiento por tu ofrenda')
                && str_contains($mail->mailData->htmlAdicional, '50.000');
        });

        // Si ZonaPagos envía posteriormente el callback para la misma ofrenda, NO debe duplicar el correo
        $this->get(route('zonapagos.handleCallback', ['id_pago' => 'O-'.$ofrenda->id]))
            ->assertStatus(200);

        Mail::assertSent(DefaultMail::class, 1);
    }

    public function test_enrutador_zonapagos_separa_prefijo_p_de_compras_y_prefijo_o_de_ofrendas(): void
    {
        $ofrenda = Ofrenda::query()->create([
            'tipo_ofrenda_id' => 1,
            'valor' => 25000,
            'valor_real' => 25000,
            'fecha' => now()->toDateString(),
            'ingresada_por' => 3,
            'estado_pago_id' => 3,
            'tipo_pago_id' => 1,
            'moneda_id' => 1,
            'destinatario_id' => 1,
            'nombre_donante' => 'Pedro Gómez',
            'identificacion_donante' => '11223344',
            'email_donante' => 'pedro@ejemplo.com',
            'correo_confirmacion_enviado' => true,
        ]);

        // Retorno con prefijo P- redirige a carrito.compraFinalizada
        $this->get(route('zonapagos.retornoUsuario', ['id_pago' => 'P-99']))
            ->assertRedirect(route('carrito.compraFinalizada', ['pago' => 99]));

        // Retorno con prefijo O- redirige a ofrendas.donacion.finalizada
        $this->get(route('zonapagos.retornoUsuario', ['id_pago' => 'O-'.$ofrenda->id]))
            ->assertRedirect(route('ofrendas.donacion.finalizada', ['ofrenda' => $ofrenda->id]));
    }

    public function test_tipo_ofrenda_sincroniza_destinatarios_en_pivote(): void
    {
        $usuario = User::query()->create([
            'primer_nombre' => 'Admin',
            'email' => 'admin@iglesia.org',
            'email_verified_at' => now(),
        ]);
        $this->actingAs($usuario);

        $dest2 = Destinatario::query()->create([
            'nombre' => 'Sede Sur',
            'estado' => true,
        ]);

        $this->post(route('tipo-ofrenda.crear'), [
            'nombre' => 'Misiones',
            'descripcion' => 'Ofrenda misionera',
            'codigo_sap' => 'SAP-MIS',
            'formulario_donaciones' => '1',
            'destinatarios' => [1, $dest2->id],
        ])->assertRedirect(route('tipo-ofrenda.listar'));

        $misiones = TipoOfrenda::where('nombre', 'Misiones')->firstOrFail();
        $this->assertEqualsCanonicalizing([1, $dest2->id], $misiones->destinatarios()->pluck('destinatarios.id')->all());

        // Si se desmarca formulario_donaciones al actualizar, desasocia los destinatarios
        $this->put(route('tipo-ofrenda.actualizar', $misiones), [
            'nombre' => 'Misiones',
            'descripcion' => 'Ofrenda misionera actualizada',
            'codigo_sap' => 'SAP-MIS',
        ])->assertRedirect(route('tipo-ofrenda.listar'));

        $this->assertCount(0, $misiones->fresh()->destinatarios);
    }

    public function test_configuracion_accessor_banner_donacion_ofrenda_url_usa_tenant_asset(): void
    {
        $config = Configuracion::firstOrFail();
        $this->assertNotNull($config->banner_donacion_ofrenda_url);
        $this->assertStringContainsString('img/donaciones/banner-donaciones-test.jpg', $config->banner_donacion_ofrenda_url);
    }

    private function crearEsquemaDePrueba(): void
    {
        Schema::create('iglesias', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre')->nullable();
            $table->string('logo')->nullable();
            $table->timestamps();
        });

        Schema::create('configuraciones', function (Blueprint $table): void {
            $table->id();
            $table->string('terminos_condiciones')->nullable();
            $table->text('mensaje_donacion_ofrenda')->nullable();
            $table->string('banner_donacion_ofrenda')->nullable();
            $table->string('ruta_almacenamiento')->nullable();
            $table->timestamps();
        });

        Schema::create('versiculos_diarios', function (Blueprint $table): void {
            $table->id();
            $table->string('cita')->nullable();
            $table->text('texto')->nullable();
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('primer_nombre')->nullable();
            $table->string('segundo_nombre')->nullable();
            $table->string('primer_apellido')->nullable();
            $table->string('segundo_apellido')->nullable();
            $table->string('identificacion')->nullable();
            $table->unsignedBigInteger('tipo_identificacion_id')->nullable();
            $table->string('telefono_movil')->nullable();
            $table->string('telefono_fijo')->nullable();
            $table->string('email')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->unsignedBigInteger('pais_id')->nullable();
            $table->string('direccion')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('tipo_identificaciones', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre');
            $table->boolean('formulario_donacion')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('paises', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre');
            $table->timestamps();
        });

        Schema::create('tipo_ofrenda', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->boolean('generica')->default(false);
            $table->boolean('formulario_donaciones')->default(true);
            $table->string('codigo_sap')->nullable();
            $table->boolean('tipo_reunion')->default(false);
            $table->boolean('ofrenda_obligatoria')->default(false);
            $table->timestamps();
        });

        Schema::create('destinatarios', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre');
            $table->boolean('estado')->default(true);
            $table->timestamps();
        });

        Schema::create('destinatario_tipo_ofrenda', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('destinatario_id');
            $table->unsignedBigInteger('tipo_ofrenda_id');
            $table->timestamps();
        });

        Schema::create('monedas', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre');
            $table->string('nombre_corto')->nullable();
            $table->string('simbolo')->nullable();
            $table->boolean('habilitada_donacion')->default(true);
            $table->decimal('donacion_maxima', 15, 2)->default(50000000);
            $table->timestamps();
        });

        Schema::create('tipos_pago', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre');
            $table->string('key_reservada')->nullable();
            $table->boolean('activo')->default(true);
            $table->string('enlace')->nullable();
            $table->string('client_id')->nullable();
            $table->string('bussines_id')->nullable();
            $table->string('key_id')->nullable();
            $table->string('account_id')->nullable();
            $table->integer('valor_minimo_donacion')->default(5000);
            $table->timestamps();
        });

        Schema::create('estados_pago', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre');
            $table->string('id_codigo_externo')->nullable();
            $table->unsignedBigInteger('tipo_pago_id')->nullable();
            $table->boolean('estado_inicial_defecto')->default(false);
            $table->boolean('estado_pendiente')->default(false);
            $table->boolean('estado_final_inscripcion')->default(false);
            $table->boolean('estado_anulado_inscripcion')->default(false);
            $table->timestamps();
        });

        Schema::create('ofrendas', function (Blueprint $table): void {
            $table->id();
            $table->integer('tipo_ofrenda_id');
            $table->decimal('valor', 12, 2);
            $table->decimal('valor_real', 12, 2)->default(0);
            $table->date('fecha');
            $table->smallInteger('ingresada_por');
            $table->text('observacion')->nullable();
            $table->integer('user_id')->nullable();
            $table->integer('estado_pago_id')->nullable();
            $table->integer('tipo_pago_id')->nullable();
            $table->integer('moneda_id')->default(1);
            $table->integer('persona_externa_id')->nullable();
            $table->string('referencia_donacion')->nullable();
            $table->integer('int_id_forma_pago')->nullable();
            $table->integer('destinatario_id')->nullable();
            $table->integer('registro_caja_id')->nullable();
            $table->integer('ofrenda_multiple_id')->nullable();
            $table->string('nombre_donante', 150)->nullable();
            $table->string('identificacion_donante', 50)->nullable();
            $table->unsignedBigInteger('tipo_identificacion_donante_id')->nullable();
            $table->string('telefono_donante', 50)->nullable();
            $table->string('email_donante', 150)->nullable();
            $table->unsignedBigInteger('pais_donante_id')->nullable();
            $table->string('direccion_donante', 255)->nullable();
            $table->boolean('correo_confirmacion_enviado')->default(false);
            $table->json('gateway_response')->nullable();
            $table->timestamps();
        });
    }

    private function sembrarCatalogosBase(): void
    {
        Iglesia::query()->create([
            'id' => 1,
            'nombre' => 'Iglesia Crecer Prueba',
        ]);

        Configuracion::query()->create([
            'id' => 1,
            'mensaje_donacion_ofrenda' => '<p>Mensaje personalizado de agradecimiento por tu ofrenda.</p>',
            'banner_donacion_ofrenda' => 'banner-donaciones-test.jpg',
        ]);

        TipoIdentificacion::query()->create([
            'id' => 1,
            'nombre' => 'Cédula de Ciudadanía',
            'formulario_donacion' => true,
        ]);

        Pais::query()->create([
            'id' => 1,
            'nombre' => 'Colombia',
        ]);

        $destinatario = Destinatario::query()->create([
            'id' => 1,
            'nombre' => 'Sede Norte',
            'estado' => true,
        ]);

        $tipoOfrenda = TipoOfrenda::query()->create([
            'id' => 1,
            'nombre' => 'Diezmos',
            'descripcion' => 'Diezmos generales',
            'formulario_donaciones' => true,
        ]);

        $tipoOfrenda->destinatarios()->attach($destinatario->id);

        Moneda::query()->create([
            'id' => 1,
            'nombre' => 'Pesos Colombianos',
            'nombre_corto' => 'COP',
            'simbolo' => '$',
            'habilitada_donacion' => true,
            'donacion_maxima' => 50000000,
        ]);

        $tipoPago = TipoPago::query()->create([
            'id' => 1,
            'nombre' => 'ZonaPagos PSE',
            'key_reservada' => 'zona',
            'activo' => true,
            'enlace' => 'https://zonapagos.test/api',
            'client_id' => '777888',
            'bussines_id' => 'usr_tenant',
            'key_id' => 'pwd_tenant',
            'account_id' => '9001',
            'valor_minimo_donacion' => 5000,
        ]);

        DB::table('estados_pago')->insert([
            [
                'id' => 1,
                'nombre' => 'Pendiente PSE',
                'id_codigo_externo' => '999',
                'tipo_pago_id' => $tipoPago->id,
                'estado_inicial_defecto' => true,
                'estado_pendiente' => true,
                'estado_final_inscripcion' => false,
                'estado_anulado_inscripcion' => false,
            ],
            [
                'id' => 3,
                'nombre' => 'Aprobado / Finalizado',
                'id_codigo_externo' => '1',
                'tipo_pago_id' => $tipoPago->id,
                'estado_inicial_defecto' => false,
                'estado_pendiente' => false,
                'estado_final_inscripcion' => true,
                'estado_anulado_inscripcion' => false,
            ],
            [
                'id' => 4,
                'nombre' => 'Pago Rechazado',
                'id_codigo_externo' => '1000',
                'tipo_pago_id' => $tipoPago->id,
                'estado_inicial_defecto' => false,
                'estado_pendiente' => false,
                'estado_final_inscripcion' => false,
                'estado_anulado_inscripcion' => true,
            ],
            [
                'id' => 21,
                'nombre' => 'Error ACH-Banco (Rechazado)',
                'id_codigo_externo' => '1001',
                'tipo_pago_id' => $tipoPago->id,
                'estado_inicial_defecto' => false,
                'estado_pendiente' => false,
                'estado_final_inscripcion' => false,
                'estado_anulado_inscripcion' => true,
            ],
            [
                'id' => 6,
                'nombre' => 'Pendiente por CR (TC)',
                'id_codigo_externo' => '4001',
                'tipo_pago_id' => $tipoPago->id,
                'estado_inicial_defecto' => false,
                'estado_pendiente' => true,
                'estado_final_inscripcion' => false,
                'estado_anulado_inscripcion' => false,
            ],
            [
                'id' => 39,
                'nombre' => 'Pendiente por Iniciar',
                'id_codigo_externo' => '888',
                'tipo_pago_id' => $tipoPago->id,
                'estado_inicial_defecto' => false,
                'estado_pendiente' => true,
                'estado_final_inscripcion' => false,
                'estado_anulado_inscripcion' => false,
            ],
            [
                'id' => 16,
                'nombre' => 'Transacción Abandonada',
                'id_codigo_externo' => null,
                'tipo_pago_id' => $tipoPago->id,
                'estado_inicial_defecto' => false,
                'estado_pendiente' => false,
                'estado_final_inscripcion' => false,
                'estado_anulado_inscripcion' => true,
            ],
        ]);
    }

    public function test_error_en_inicio_pago_anula_ofrenda_con_valor_real_cero_sin_eliminarla(): void
    {
        Mail::fake();

        Http::fake([
            'https://zonapagos.test/api/InicioPago' => Http::response([
                'int_codigo' => 2,
                'str_descripcion_error' => 'Servicio no encontrado',
                'str_url' => null,
            ], 200),
        ]);

        $tipoDiezmo = TipoOfrenda::where('nombre', 'Diezmos')->firstOrFail();
        $destinatario = Destinatario::where('nombre', 'Sede Norte')->firstOrFail();

        Livewire::test(DonacionesOnline::class)
            ->call('continuarComoIndependiente')
            ->set('nombreCompleto', 'Laura Martínez')
            ->set('tipoIdentificacionId', 1)
            ->set('identificacion', '55443322')
            ->set('telefono', '3112223344')
            ->set('email', 'laura@ejemplo.com')
            ->set('paisId', 1)
            ->set('direccion', 'Calle 20 # 5-10')
            ->set('aceptaTerminos', true)
            ->call('continuarAPestana3')
            ->set('tipoOfrendaId', $tipoDiezmo->id)
            ->set('destinatarioId', $destinatario->id)
            ->set('monedaId', 1)
            ->set('valorDonacion', '30000')
            ->call('procesarDonacion')
            ->assertSet('mensajeErrorPasarela', 'Servicio no encontrado');

        // La ofrenda NO se elimina: pasa a estado anulado (ID 16) con valor_real = 0
        $this->assertSame(1, Ofrenda::count());
        $ofrenda = Ofrenda::firstOrFail();
        $this->assertSame(16, (int) $ofrenda->estado_pago_id);
        $this->assertEquals(30000.0, (float) $ofrenda->valor);
        $this->assertEquals(0.0, (float) $ofrenda->valor_real);
        Mail::assertNothingSent();
    }

    public function test_sonda_protege_ofrenda_pendiente_pse_reciente_y_4001_pendiente_cr(): void
    {
        Mail::fake();

        $ofrendaReciente = $this->crearOfrendaOnlinePendiente(minutosAtras: 10, valor: 40000);
        $ofrendaCr4001 = $this->crearOfrendaOnlinePendiente(minutosAtras: 65, valor: 80000);

        Http::fake(function (ClientRequest $request) use ($ofrendaReciente, $ofrendaCr4001) {
            $idPago = $request['str_id_pago'] ?? '';
            if ($idPago === 'O-'.$ofrendaReciente->id) {
                return Http::response([
                    'int_error' => 0,
                    'int_cantidad_pagos' => 1,
                    'str_res_pago' => '100|556|0|200|999|40000|40000|0|Donacion|111|Ana|P|300|ana@t.co|OFRENDA|1|1|||2026-11-20|29|CUS-PEND|1001|1007|BANCO|CUS-PEND|1',
                ], 200);
            }

            if ($idPago === 'O-'.$ofrendaCr4001->id) {
                return Http::response([
                    'int_error' => 0,
                    'int_cantidad_pagos' => 1,
                    'str_res_pago' => '101|557|0|200|4001|80000|80000|0|Donacion|222|Luis|P|300|luis@t.co|OFRENDA|1|1|||2026-11-20|32|TKT-CR|4111|VISA|000|000',
                ], 200);
            }

            return Http::response(['int_error' => -1, 'int_cantidad_pagos' => 0], 200);
        });

        $sonda = app(\App\Console\Commands\VerificarPagosPendientes::class);
        $sonda->setOutput(new \Illuminate\Console\OutputStyle(
            new \Symfony\Component\Console\Input\ArrayInput([]),
            new \Symfony\Component\Console\Output\NullOutput
        ));
        $sonda->verificarOfrendasPendientesZonaPagos(45);

        $this->assertSame(1, (int) $ofrendaReciente->fresh()->estado_pago_id);
        $this->assertSame(6, (int) $ofrendaCr4001->fresh()->estado_pago_id);
        $this->assertSame(2, Ofrenda::count());
        Mail::assertNothingSent();
    }

    public function test_sonda_pasa_ofrendas_rechazadas_y_abandonadas_a_anulado_sin_eliminar_para_informes(): void
    {
        Mail::fake();

        $ofrendaRechazada1000 = $this->crearOfrendaOnlinePendiente(minutosAtras: 5, valor: 20000);
        $ofrendaErrorAch1001 = $this->crearOfrendaOnlinePendiente(minutosAtras: 5, valor: 35000);
        $ofrendaAbandonadaNoEncontrada = $this->crearOfrendaOnlinePendiente(minutosAtras: 50, valor: 60000);
        $ofrendaAbandonada888 = $this->crearOfrendaOnlinePendiente(minutosAtras: 55, valor: 75000);
        $ofrendaErrorRed = $this->crearOfrendaOnlinePendiente(minutosAtras: 90, valor: 90000);

        Http::fake(function (ClientRequest $request) use (
            $ofrendaRechazada1000,
            $ofrendaErrorAch1001,
            $ofrendaAbandonadaNoEncontrada,
            $ofrendaAbandonada888,
            $ofrendaErrorRed
        ) {
            $idPago = $request['str_id_pago'] ?? '';
            if ($idPago === 'O-'.$ofrendaRechazada1000->id) {
                return Http::response([
                    'int_error' => 0,
                    'int_cantidad_pagos' => 1,
                    'str_res_pago' => '200|601|0|1|1000|20000|20000|0|Donacion|111|A|B|300|a@t.co|OFRENDA|1|1|||2026-11-20|29|T1|1001|1007|BANCO|CUS-1000|1',
                ], 200);
            }
            if ($idPago === 'O-'.$ofrendaErrorAch1001->id) {
                return Http::response([
                    'int_error' => 0,
                    'int_cantidad_pagos' => 1,
                    'str_res_pago' => '201|602|0|1|1001|35000|35000|0|Donacion|222|C|D|300|c@t.co|OFRENDA|1|1|||2026-11-20|29|T2|1001|1007|BANCO|CUS-1001|1',
                ], 200);
            }
            if ($idPago === 'O-'.$ofrendaAbandonadaNoEncontrada->id) {
                return Http::response([
                    'int_error' => -1,
                    'int_cantidad_pagos' => 0,
                    'str_detalle' => 'No se encontraron pagos',
                    'str_res_pago' => '',
                ], 200);
            }
            if ($idPago === 'O-'.$ofrendaAbandonada888->id) {
                return Http::response([
                    'int_error' => 0,
                    'int_cantidad_pagos' => 1,
                    'str_res_pago' => '203|604|0|200|888|75000|75000|0|Donacion|444|E|F|300|e@t.co|OFRENDA|1|1|||2026-11-20|29|T4|1001|1007|BANCO||1',
                ], 200);
            }
            if ($idPago === 'O-'.$ofrendaErrorRed->id) {
                return Http::response('Gateway Timeout', 503);
            }

            return Http::response(['int_error' => -1, 'int_cantidad_pagos' => 0], 200);
        });

        $sonda = app(\App\Console\Commands\VerificarPagosPendientes::class);
        $sonda->setOutput(new \Illuminate\Console\OutputStyle(
            new \Symfony\Component\Console\Input\ArrayInput([]),
            new \Symfony\Component\Console\Output\NullOutput
        ));
        $actualizadas = $sonda->verificarOfrendasPendientesZonaPagos(45);

        $this->assertSame(4, $actualizadas);
        // NINGUNA ofrenda fue eliminada: todas existen para auditoría e informes
        $this->assertSame(5, Ofrenda::count());

        $this->assertSame(4, (int) $ofrendaRechazada1000->fresh()->estado_pago_id);
        $this->assertEquals(0.0, (float) $ofrendaRechazada1000->fresh()->valor_real);

        $this->assertSame(21, (int) $ofrendaErrorAch1001->fresh()->estado_pago_id);
        $this->assertEquals(0.0, (float) $ofrendaErrorAch1001->fresh()->valor_real);

        $this->assertSame(16, (int) $ofrendaAbandonadaNoEncontrada->fresh()->estado_pago_id);
        $this->assertEquals(0.0, (float) $ofrendaAbandonadaNoEncontrada->fresh()->valor_real);

        $this->assertSame(16, (int) $ofrendaAbandonada888->fresh()->estado_pago_id);
        $this->assertEquals(0.0, (float) $ofrendaAbandonada888->fresh()->valor_real);

        // Error 503 de red NO debe anular la ofrenda
        $this->assertSame(1, (int) $ofrendaErrorRed->fresh()->estado_pago_id);
        Mail::assertNothingSent();
    }

    public function test_callback_tardio_de_rechazo_no_degrada_ofrenda_ya_aprobada(): void
    {
        Mail::fake();

        $ofrenda = $this->crearOfrendaOnlinePendiente(minutosAtras: 5, valor: 100000);
        $ofrenda->update([
            'estado_pago_id' => 3, // Ya aprobada
            'valor_real' => 100000,
            'correo_confirmacion_enviado' => true,
        ]);

        Http::fake([
            'https://zonapagos.test/api/VerificacionPago' => Http::response([
                'int_error' => 0,
                'int_cantidad_pagos' => 1,
                'str_res_pago' => '300|701|0|1|1000|100000|100000|0|Donacion|111|A|B|300|a@t.co|OFRENDA|1|1|||2026-11-20|29|T1|1001|1007|BANCO|CUS-X|1',
            ], 200),
        ]);

        $this->get(route('zonapagos.handleCallback', ['id_pago' => 'O-'.$ofrenda->id]))
            ->assertOk();

        $ofrenda->refresh();
        $this->assertSame(3, (int) $ofrenda->estado_pago_id);
        $this->assertEquals(100000.0, (float) $ofrenda->valor_real);
        Mail::assertNothingSent();
    }

    private function crearOfrendaOnlinePendiente(int $minutosAtras, float $valor): Ofrenda
    {
        $fechaCreacion = now()->subMinutes($minutosAtras);
        $ofrenda = Ofrenda::query()->create([
            'tipo_ofrenda_id' => 1,
            'valor' => $valor,
            'valor_real' => 0,
            'fecha' => $fechaCreacion->toDateString(),
            'ingresada_por' => 3,
            'estado_pago_id' => 1,
            'tipo_pago_id' => 1,
            'moneda_id' => 1,
            'destinatario_id' => 1,
            'nombre_donante' => 'Donante Prueba',
            'identificacion_donante' => '12345678',
            'email_donante' => 'donante@ejemplo.com',
            'correo_confirmacion_enviado' => false,
        ]);
        DB::table('ofrendas')->where('id', $ofrenda->id)->update([
            'created_at' => $fechaCreacion,
            'updated_at' => $fechaCreacion,
        ]);

        return $ofrenda->fresh();
    }
}
