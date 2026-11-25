<?php

namespace Tests\Feature;

use App\Models\Actividad;
use App\Models\Aula;
use App\Models\BannerGeneral;
use App\Models\Compra;
use App\Models\CorteEscuela;
use App\Models\Curso;
use App\Models\Escuela;
use App\Models\Grupo;
use App\Models\Hito;
use App\Models\HorarioBase;
use App\Models\Inscripcion;
use App\Models\ItemPlantilla;
use App\Models\Maestro;
use App\Models\Materia;
use App\Models\Periodo;
use App\Models\Peticion;
use App\Models\PlanLector;
use App\Models\PlanLectorContenido;
use App\Models\PlanLectorDia;
use App\Models\Post;
use App\Models\ReporteGrupo;
use App\Models\ReporteReunion;
use App\Models\Sede;
use App\Models\Tenant;
use App\Models\User;
use App\Models\VersiculoDiario;
use Database\Seeders\DemoComercialTenantSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DemoComercialTenantTest extends TestCase
{
    private static ?string $tenantId = null;

    private string $databaseName = '';

    protected function setUp(): void
    {
        parent::setUp();
        $database = getenv('CUENTA_INICIAL_TEST_DATABASE');
        if (! is_string($database) || ! preg_match('/^redil_integracion_test_[0-9]{8}$/', $database)) {
            $this->markTestSkipped('Requiere CUENTA_INICIAL_TEST_DATABASE=redil_integracion_test_YYYYMMDD; nunca utiliza la base de trabajo.');
        }

        $this->databaseName = $database;
        foreach (['central', 'pgsql'] as $name) {
            config([
                'database.connections.'.$name.'.host' => '127.0.0.1',
                'database.connections.'.$name.'.port' => 5432,
                'database.connections.'.$name.'.database' => $database,
                'database.connections.'.$name.'.url' => null,
            ]);
            app('db')->purge($name);
        }

        config([
            'cache.default' => 'array',
            'logging.default' => 'null',
            'cuenta_inicial.soporte_password' => 'Solo-para-pruebas-123!',
            'cuenta_inicial.disco_marca' => null,
            'cuenta_inicial.tema_dinamico' => true,
        ]);

        Storage::fake('public');
        Storage::fake('local');
        Mail::fake();
        Queue::fake();
    }

    protected function tearDown(): void
    {
        if (tenancy()->initialized) {
            auth()->forgetGuards();
            tenancy()->end();
        }
        parent::tearDown();
    }

    public function test_command_rejects_non_commercial_domains_and_wrong_database(): void
    {
        $exitWrongDomain = Artisan::call('tenant:poblar-demo-comercial', [
            '--dominio' => 'inicial.redil.cloud',
            '--base' => $this->databaseName,
            '--crear-si-no-existe' => true,
        ]);
        $this->assertSame(1, $exitWrongDomain);

        $exitWrongDb = Artisan::call('tenant:poblar-demo-comercial', [
            '--dominio' => 'comercial.redil.cloud',
            '--base' => 'otra_base_inexistente',
            '--crear-si-no-existe' => true,
        ]);
        $this->assertSame(1, $exitWrongDb);
    }

    public function test_populates_two_thousand_users_school_without_periods_and_complete_demo_content(): void
    {
        self::$tenantId ??= 'comercial_test_'.bin2hex(random_bytes(3));
        $dominio = 'comercial.'.self::$tenantId.'.redil.cloud';

        $codigo = Artisan::call('tenant:poblar-demo-comercial', [
            '--dominio' => $dominio,
            '--tenant' => self::$tenantId,
            '--base' => $this->databaseName,
            '--usuarios' => 2000,
            '--iglesia' => 'Iglesia Vida Plena',
            '--crear-si-no-existe' => true,
            '--vence' => '2027-12-31',
        ]);
        $this->assertSame(0, $codigo, Artisan::output());

        $tenant = Tenant::findOrFail(self::$tenantId);
        $this->assertSame('active', $tenant->status);
        $this->assertSame(DemoComercialTenantSeeder::VERSION, (int) $tenant->demo_comercial_version);

        $tenant->run(function (): void {
            $conexion = (new User)->getConnection();

            $this->assertSame(2000, User::count());
            $this->assertSame(12, Sede::count());
            $this->assertSame(233, Grupo::count());
            $this->assertGreaterThan(200, $conexion->table('grupos_de_grupos')->count());

            // Verificar Escuela Manantial de Vida Eterna SIN periodos ni matrículas
            $this->assertSame(1, Escuela::where('nombre', 'Escuela Manantial de Vida Eterna')->count());
            $this->assertSame(3, CorteEscuela::count());
            $this->assertSame(18, Materia::count());
            $this->assertSame(162, ItemPlantilla::count());
            $this->assertSame(24, Aula::count());
            $this->assertSame(18, Maestro::count());
            $this->assertSame(42, HorarioBase::count());
            $this->assertSame(0, Periodo::count());
            $this->assertSame(0, $conexion->table('materia_periodo')->count());
            $this->assertSame(0, $conexion->table('horarios_materia_periodo')->count());
            $this->assertSame(0, $conexion->table('matriculas')->count());

            // Verificar Actividades, Planes Lectores, Versículos y módulos complementarios
            $this->assertSame(4, Actividad::count());
            $this->assertSame(60, Compra::count());
            $this->assertSame(60, Inscripcion::count());

            $this->assertSame(30, PlanLector::count());
            $this->assertSame(147, PlanLectorDia::count());
            $this->assertGreaterThanOrEqual(294, PlanLectorContenido::count());
            $this->assertSame(0, PlanLectorContenido::where('contenido', '')->orWhere('contenido', '[]')->count());
            $this->assertGreaterThan(100, $conexion->table('plan_lector_users')->count());

            $this->assertSame(120, VersiculoDiario::count());
            $this->assertSame(1, VersiculoDiario::whereDate('fecha_publicacion', now()->toDateString())->count());
            $this->assertGreaterThan(100, $conexion->table('versiculo_usuario_like')->count());

            $this->assertSame(384, ReporteGrupo::count());
            $this->assertSame(8, ReporteReunion::count());
            $this->assertSame(3, Curso::count());
            $this->assertSame(6, Hito::count());
            $this->assertSame(4, Post::count());
            $this->assertSame(3, BannerGeneral::count());
            $this->assertSame(45, Peticion::count());

            // Verificar que el acceso de soporte se conserva intacto
            $soporte = User::where('email', 'soporte@redil.co')->firstOrFail();
            $this->assertTrue(auth('web')->validate([
                'email' => $soporte->email,
                'password' => config('cuenta_inicial.soporte_password'),
            ]));

            // Verificar que tanto Soporte REDIL como Administrador iglesia visualizan los 233 grupos en Listado, Mapa y Gráfico del Ministerio
            $adminIglesia = User::where('email', 'comercial@redil.co')->firstOrFail();
            foreach ([$soporte, $adminIglesia] as $usuarioAdmin) {
                $this->actingAs($usuarioAdmin);
                $controladorGrupos = app(\App\Http\Controllers\GrupoController::class);

                $vistaLista = $controladorGrupos->listar(new \Illuminate\Http\Request);
                $datosLista = $vistaLista->getData();
                $this->assertSame(233, $datosLista['grupos']->total());

                $vistaMapa = $controladorGrupos->mapaDeGrupos(new \Illuminate\Http\Request);
                $datosMapa = $vistaMapa->getData();
                $this->assertSame(233, $datosMapa['grupos']->count());

                $vistaGrafico = $controladorGrupos->graficoDelMinisterio(new \App\Http\Requests\GraficoMinisterioRequest, 'U-logueado', 4);
                $datosGrafico = $vistaGrafico->getData();
                $peticionTotales = \App\Http\Requests\GraficoMinisterioRequest::create('/', 'GET', ['solo_totales' => 1]);
                $totalesGrafico = $controladorGrupos->graficoDelMinisterio($peticionTotales, 'U-logueado', 4)->getData(true);
                $this->assertSame(233, $totalesGrafico['totalGrupos']);
                $this->assertGreaterThan(1900, $totalesGrafico['totalPersonas']);
                $this->assertGreaterThan(15, count($datosGrafico['nodos']));
                $this->assertGreaterThan(15, count($datosGrafico['aristas']));
            }
        });

        // Segunda ejecución idempotente: no duplica ni altera conteos
        $segundaEjecucion = Artisan::call('tenant:poblar-demo-comercial', [
            '--dominio' => $dominio,
            '--tenant' => self::$tenantId,
            '--base' => $this->databaseName,
            '--usuarios' => 2000,
        ]);
        $this->assertSame(0, $segundaEjecucion);

        $tenant->run(function (): void {
            $this->assertSame(2000, User::count());
            $this->assertSame(1, Escuela::count());
            $this->assertSame(30, PlanLector::count());
            $this->assertSame(120, VersiculoDiario::count());
        });
    }

    public function test_seeder_rejects_central_context(): void
    {
        $this->expectException(\LogicException::class);
        app(DemoComercialTenantSeeder::class)->run();
    }
}
