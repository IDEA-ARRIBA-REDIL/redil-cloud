<?php

namespace Tests\Feature;

use App\Http\Controllers\DashboardPeriodoController;
use App\Models\AlumnoRespuestaItem;
use App\Models\Calificaciones;
use App\Models\CortePeriodo;
use App\Models\Escuela;
use App\Models\HorarioMateriaPeriodo;
use App\Models\ItemCorteMateriaPeriodo;
use App\Models\Materia;
use App\Models\MateriaAprobadaUsuario;
use App\Models\MateriaPeriodo;
use App\Models\Matricula;
use App\Models\NivelEscuela;
use App\Models\NivelPeriodo;
use App\Models\Periodo;
use App\Models\ReporteAsistenciaAlumnos;
use App\Models\ReporteAsistenciaClase;
use App\Models\User;
use App\Services\ResumenPeriodoService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DashboardPeriodoTest extends TestCase
{
    private Periodo $periodo;

    public function createApplication(): Application
    {
        $aplicacion = require dirname(__DIR__, 2).'/bootstrap/app.php';
        $aplicacion->instance('routes.cached', false);
        $this->traitsUsedByTest = array_flip(class_uses_recursive(static::class));
        $aplicacion->make(Kernel::class)->bootstrap();

        return $aplicacion;
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->app->instance('env', 'testing');

        config()->set('database.default', 'periodo_testing');
        config()->set('database.connections.periodo_testing', [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => true,
        ]);
        config()->set('logging.default', 'null');
        config()->set('cache.default', 'array');
        config()->set('session.driver', 'array');
        config()->set('view.compiled', storage_path('framework/views'));
        $this->app->make('view')->getFinder()->setPaths([resource_path('views')]);
        $this->app->forgetInstance('blade.compiler');
        Blade::clearResolvedInstance('blade.compiler');
        $this->app->make('view.engine.resolver')->forget('blade');
        DB::purge('periodo_testing');
        DB::setDefaultConnection('periodo_testing');

        foreach (['escuelas', 'niveles_escuelas', 'materias'] as $tabla) {
            Schema::create($tabla, function (Blueprint $tabla): void {
                $tabla->id();
                $tabla->timestamps();
                $tabla->string('nombre');
                $tabla->softDeletes();
            });
        }
        Schema::create('users', function (Blueprint $tabla): void {
            $tabla->id();
            $tabla->timestamps();
            $tabla->integer('genero')->nullable();
            foreach (['identificacion', 'primer_nombre', 'segundo_nombre', 'primer_apellido', 'segundo_apellido'] as $campo) {
                $tabla->string($campo)->nullable();
            }
            $tabla->softDeletes();
        });
        Schema::create('periodos', function (Blueprint $tabla): void {
            $tabla->id();
            $tabla->timestamps();
            $tabla->string('nombre');
            $tabla->unsignedBigInteger('escuela_id');
            $tabla->unsignedBigInteger('sistema_calificaciones_id')->nullable();
            $tabla->boolean('estado');
            $tabla->date('fecha_inicio')->nullable();
            $tabla->date('fecha_fin')->nullable();
            $tabla->date('fecha_maxima_entrega_notas')->nullable();
        });
        Schema::create('niveles_periodo', function (Blueprint $tabla): void {
            $tabla->id();
            $tabla->timestamps();
            $tabla->unsignedBigInteger('periodo_id');
            $tabla->unsignedBigInteger('nivel_escuela_id');
        });
        Schema::create('materia_periodo', function (Blueprint $tabla): void {
            $tabla->id();
            $tabla->timestamps();
            $tabla->unsignedBigInteger('periodo_id');
            $tabla->unsignedBigInteger('materia_id');
            $tabla->unsignedBigInteger('nivel_id')->nullable();
            $tabla->boolean('habilitar_calificaciones')->default(true);
            $tabla->boolean('habilitar_asistencias')->default(true);
            $tabla->unsignedInteger('asistencias_minimas')->nullable()->default(1);
            $tabla->boolean('finalizado')->default(false);
        });
        Schema::create('horarios_materia_periodo', function (Blueprint $tabla): void {
            $tabla->id();
            $tabla->timestamps();
            $tabla->unsignedBigInteger('materia_periodo_id');
            $tabla->unsignedBigInteger('horario_base_id')->nullable();
        });
        Schema::create('calificaciones', function (Blueprint $tabla): void {
            $tabla->id();
            $tabla->timestamps();
            $tabla->unsignedBigInteger('sistema_calificacion_id');
            $tabla->boolean('aprobado');
            $tabla->decimal('nota_minima');
        });
        Schema::create('cortes_periodo', function (Blueprint $tabla): void {
            $tabla->id();
            $tabla->timestamps();
            $tabla->unsignedBigInteger('periodo_id');
            $tabla->decimal('porcentaje');
        });
        Schema::create('item_corte_materia_periodo', function (Blueprint $tabla): void {
            $tabla->id();
            $tabla->timestamps();
            $tabla->unsignedBigInteger('materia_periodo_id');
            $tabla->unsignedBigInteger('horario_materia_periodo_id');
            $tabla->unsignedBigInteger('corte_periodo_id');
            $tabla->decimal('porcentaje');
        });
        Schema::create('alumno_respuesta_items', function (Blueprint $tabla): void {
            $tabla->id();
            $tabla->timestamps();
            $tabla->unsignedBigInteger('user_id');
            $tabla->unsignedBigInteger('item_corte_materia_periodo_id');
            $tabla->decimal('nota_obtenida')->nullable();
        });
        Schema::create('matriculas', function (Blueprint $tabla): void {
            $tabla->id();
            $tabla->timestamps();
            $tabla->unsignedBigInteger('periodo_id');
            $tabla->unsignedBigInteger('user_id');
            $tabla->unsignedBigInteger('horario_materia_periodo_id');
            $tabla->boolean('bloqueado')->default(false);
            $tabla->string('estado_pago_matricula')->nullable()->default('pagada');
            $tabla->softDeletes();
        });
        Schema::create('reportes_asistencia_clase', function (Blueprint $tabla): void {
            $tabla->id();
            $tabla->timestamps();
            $tabla->unsignedBigInteger('horario_materia_periodo_id');
        });
        Schema::create('reportes_asistencia_alumnos', function (Blueprint $tabla): void {
            $tabla->id();
            $tabla->timestamps();
            $tabla->unsignedBigInteger('reporte_asistencia_clase_id');
            $tabla->unsignedBigInteger('user_id');
            $tabla->boolean('asistio')->nullable();
        });
        Schema::create('materias_aprobada_usuario', function (Blueprint $tabla): void {
            $tabla->id();
            $tabla->timestamps();
            $tabla->unsignedBigInteger('periodo_id');
            $tabla->unsignedBigInteger('materia_periodo_id');
            $tabla->unsignedBigInteger('user_id');
            $tabla->integer('aprobado')->nullable();
            $tabla->decimal('nota_final')->nullable();
            $tabla->string('motivo_reprobacion')->nullable();
            $tabla->unsignedBigInteger('materia_id')->nullable();
            $tabla->integer('total_asistencias')->nullable();
            $tabla->integer('creditos_aprobados')->nullable();
        });

        Schema::create('sedes', function (Blueprint $tabla): void {
            $tabla->id();
            $tabla->string('nombre');
        });
        Schema::create('aulas', function (Blueprint $tabla): void {
            $tabla->id();
            $tabla->string('nombre');
            $tabla->unsignedBigInteger('sede_id');
            $tabla->softDeletes();
        });
        Schema::create('horarios_base', function (Blueprint $tabla): void {
            $tabla->id();
            $tabla->unsignedBigInteger('aula_id');
            $tabla->integer('dia');
            $tabla->time('hora_inicio');
            $tabla->time('hora_fin');
            $tabla->softDeletes();
        });

        Escuela::query()->insert(['id' => 1, 'nombre' => 'Escuela de formación']);
        Periodo::query()->insert(['id' => 1, 'nombre' => 'Periodo de prueba', 'estado' => true, 'escuela_id' => 1, 'sistema_calificaciones_id' => 1]);
        Calificaciones::query()->insert(['sistema_calificacion_id' => 1, 'nota_minima' => 3.5, 'aprobado' => true]);
        $this->periodo = Periodo::query()->findOrFail(1);
    }

    public function test_cuenta_personas_unicas_generos_niveles_y_materias_sin_duplicar_matriculas(): void
    {
        $primero = $this->crearMateria(1);
        $segundo = $this->crearMateria(2);
        $mujer = $this->crearAlumno(1);
        $hombre = $this->crearAlumno(0);
        $sinGenero = $this->crearAlumno(null);
        $this->matricular($mujer, $primero);
        $this->matricular($mujer, $primero);
        $this->matricular($mujer, $segundo);
        $this->matricular($hombre, $primero);
        $this->matricular($sinGenero, $segundo);

        $dashboard = $this->resumen();

        $this->assertSame(5, $dashboard['matriculas']);
        $this->assertSame(3, $dashboard['general']['estudiantes']);
        $this->assertSame(['Femenino' => 1, 'Masculino' => 1, 'Sin registrar' => 1], $dashboard['general']['generos']);
        $this->assertSame([2, 2], $dashboard['materias']->pluck('resumen.estudiantes')->all());
        $this->assertSame([2, 2], $dashboard['niveles']->pluck('resumen.estudiantes')->all());
    }

    public function test_pondera_cortes_e_items_y_calcula_asistencia_sobre_registros_reales(): void
    {
        $horario = $this->crearMateria();
        $alumno = $this->crearAlumno(1);
        $otro = $this->crearAlumno(0);
        $this->matricular($alumno, $horario);
        $this->matricular($otro, $horario);
        $this->calificar($alumno, $horario, 5, 50, 60);
        $this->calificar($alumno, $horario, 5, 50, 60);
        $this->calificar($alumno, $horario, 2, 100, 40);
        $this->asistir($alumno, $horario, true);
        $this->asistir($alumno, $horario, false);
        $this->asistir($otro, $horario, true);

        $resumen = $this->resumen()['materias'][0]['resumen'];

        $this->assertSame(1, $resumen['aprobados']);
        $this->assertSame(1, $resumen['riesgo']);
        $this->assertEqualsWithDelta(1.9, $resumen['notaPromedio'], 0.0001);
        $this->assertSame(66.7, $resumen['asistencia']);
        $this->assertSame(1.0, $resumen['promedioAsistencias']);
        $this->assertSame(50.0, $resumen['avanceCalificacion']);
        $this->assertSame(6, $resumen['evaluaciones']);
    }

    public function test_aplica_nota_minima_asistencia_y_bloqueo_y_respeta_criterios_desactivados(): void
    {
        $horario = $this->crearMateria();
        $aprobado = $this->crearAlumno(1);
        $sinAsistencia = $this->crearAlumno(0);
        $bloqueado = $this->crearAlumno(null);
        foreach ([$aprobado, $sinAsistencia, $bloqueado] as $alumno) {
            $this->matricular($alumno, $horario, ['bloqueado' => $alumno === $bloqueado]);
        }
        $item = $this->calificar($aprobado, $horario, 3.5);
        AlumnoRespuestaItem::query()->insert(['user_id' => $sinAsistencia, 'item_corte_materia_periodo_id' => $item, 'nota_obtenida' => 5]);
        $this->asistir($aprobado, $horario, true);
        $dashboard = $this->resumen();
        $this->assertSame(1, $dashboard['general']['aprobados']);
        $this->assertSame(2, $dashboard['general']['riesgo']);
        $this->assertSame(1, $dashboard['general']['motivos']['ASISTENCIA_INSUFICIENTE']);
        $this->assertSame(1, $dashboard['general']['motivos']['MATRICULA_BLOQUEADA']);

        MateriaPeriodo::query()->update(['habilitar_calificaciones' => false, 'habilitar_asistencias' => false]);
        $dashboard = $this->resumen();
        $this->assertSame(2, $dashboard['general']['aprobados']);
        $this->assertSame(1, $dashboard['general']['riesgo']);
        $this->assertNull($dashboard['general']['asistencia']);
    }

    public function test_excluye_anuladas_rechazadas_eliminadas_y_otros_periodos_pero_incluye_pendientes(): void
    {
        $horario = $this->crearMateria();
        foreach (['anulada', 'rechazada', null] as $estado) {
            $this->matricular($this->crearAlumno(1), $horario, ['estado_pago_matricula' => $estado]);
        }
        $this->matricular($this->crearAlumno(1), $horario, ['deleted_at' => now()]);
        $this->matricular($this->crearAlumno(1), $horario, ['periodo_id' => 2]);
        $this->matricular($this->crearAlumno(0), $horario, ['estado_pago_matricula' => 'pendiente']);

        $dashboard = $this->resumen();
        $this->assertSame(1, $dashboard['general']['estudiantes']);
        $this->assertSame(4, $dashboard['excluidas']);
    }

    public function test_no_presenta_configuracion_incompleta_como_reprobacion_y_maneja_periodo_vacio(): void
    {
        $dashboard = $this->resumen();
        $this->assertSame(0, $dashboard['general']['estudiantes']);
        $this->assertNull($dashboard['general']['asistencia']);
        $this->assertNull($dashboard['general']['porcentajeAprobacion']);
        $horario = $this->crearMateria();
        $alumno = $this->crearAlumno(1);
        $this->matricular($alumno, $horario);
        $this->assertSame(1, $this->resumen()['general']['pendientes']);
        $this->calificar($alumno, $horario, 5, 50);
        $this->assertSame(1, $this->resumen()['general']['pendientes']);
        ItemCorteMateriaPeriodo::query()->update(['porcentaje' => 100]);
        Calificaciones::query()->delete();
        $this->assertSame(1, $this->resumen()['general']['pendientes']);
    }

    public function test_periodo_cerrado_y_materia_finalizada_respetan_resultados_y_estado_en_proceso(): void
    {
        $horario = $this->crearMateria();
        $aprobado = $this->crearAlumno(1);
        $reprobado = $this->crearAlumno(0);
        $pendiente = $this->crearAlumno(null);
        $sinResultado = $this->crearAlumno(null);
        foreach ([$aprobado, $reprobado, $pendiente, $sinResultado] as $alumno) {
            $this->matricular($alumno, $horario);
        }
        foreach ([$aprobado => 1, $reprobado => 0, $pendiente => 2] as $alumno => $estado) {
            MateriaAprobadaUsuario::query()->insert(['periodo_id' => 1, 'materia_periodo_id' => 1, 'user_id' => $alumno, 'aprobado' => $estado, 'nota_final' => 4]);
        }
        MateriaPeriodo::query()->update(['finalizado' => true]);
        $this->assertSame(1, $this->resumen()['general']['aprobados']);
        $this->assertSame(1, $this->resumen()['general']['riesgo']);
        $this->assertSame(2, $this->resumen()['general']['pendientes']);
        Periodo::query()->update(['estado' => false]);
        MateriaPeriodo::query()->update(['finalizado' => false]);
        $dashboard = $this->resumen();
        $this->assertSame(1, $dashboard['general']['aprobados']);
        $this->assertSame(2, $dashboard['general']['pendientes']);
        $this->assertSame(4.0, $dashboard['general']['notaPromedio']);
    }

    public function test_riesgo_en_una_materia_prevalece_sobre_aprobacion_y_pendientes_en_el_resumen(): void
    {
        $primera = $this->crearMateria();
        $segunda = $this->crearMateria();
        $alumno = $this->crearAlumno(1);
        $this->matricular($alumno, $primera, ['bloqueado' => true]);
        $this->matricular($alumno, $segunda);
        $dashboard = $this->resumen();
        $this->assertSame(1, $dashboard['general']['riesgo']);
        $this->assertSame(0, $dashboard['general']['pendientes']);
        $this->assertSame(0, $dashboard['general']['aprobados']);
    }

    public function test_varios_horarios_de_la_misma_materia_no_suman_notas_ni_inflan_estudiantes(): void
    {
        $primero = $this->crearMateria();
        $segundo = HorarioMateriaPeriodo::query()->insertGetId(['materia_periodo_id' => 1]);
        $alumno = $this->crearAlumno(1);
        $this->matricular($alumno, $primero);
        $this->matricular($alumno, $segundo);
        $this->calificar($alumno, $primero, 5);
        $this->calificar($alumno, $segundo, 5);
        $dashboard = $this->resumen();
        $this->assertSame(1, $dashboard['general']['estudiantes']);
        $this->assertSame(1, $dashboard['general']['pendientes']);
        $this->assertSame(1, $dashboard['general']['motivos']['MATRICULA_INCONSISTENTE']);
        $this->assertNull($dashboard['general']['notaPromedio']);
    }

    public function test_mantiene_materias_y_niveles_sin_matriculados_y_tolera_relaciones_ausentes(): void
    {
        $this->crearMateria(1);
        $this->crearMateria(2);
        $this->matricular($this->crearAlumno(null), 999);
        $dashboard = $this->resumen();
        $this->assertSame([0, 0], $dashboard['materias']->pluck('resumen.estudiantes')->all());
        $this->assertCount(2, $dashboard['niveles']);
        $this->assertSame(1, $dashboard['general']['pendientes']);
    }

    public function test_consulta_solo_lectura_con_numero_de_consultas_independiente_de_los_alumnos(): void
    {
        $horario = $this->crearMateria();
        $this->matricular($this->crearAlumno(0), $horario);
        DB::connection()->enableQueryLog();
        $this->resumen();
        $cantidad = count(DB::connection()->getQueryLog());
        for ($indice = 0; $indice < 12; $indice++) {
            $this->matricular($this->crearAlumno(1), $horario);
        }
        DB::connection()->flushQueryLog();
        $this->resumen();
        $consultas = DB::connection()->getQueryLog();
        $this->assertCount($cantidad, $consultas);
        foreach ($consultas as $consulta) {
            $this->assertStringStartsWith('select ', strtolower(ltrim($consulta['query'])));
        }
    }

    public function test_vista_muestra_resumen_y_escapa_nombres_sin_permisos_adicionales(): void
    {
        $this->crearMateria();
        $this->periodo->nombre = '<script>alert(1)</script>';
        $dashboard = app(ResumenPeriodoService::class)->obtener($this->periodo);
        $html = view('contenido.paginas.escuelas.periodos.resumen-dashboard-periodo', ['periodo' => $this->periodo, 'dashboard' => $dashboard])->render();
        $this->assertStringContainsString('&lt;script&gt;', $html);
        $this->assertStringNotContainsString('<script>', $html);
        foreach (['Detalle por nivel', 'Detalle por materia', 'Sin datos', 'Cumplirían hoy', 'Promedio'] as $texto) {
            $this->assertStringContainsString(strtolower($texto), strtolower($html));
        }
        $rutas = Route::getRoutes();
        $ruta = $rutas->getByName('periodo.dashboard');
        $this->assertSame($rutas->getByName('periodo.gestionar')->gatherMiddleware(), $ruta->gatherMiddleware());
        $this->assertSame(DashboardPeriodoController::class, $ruta->getControllerClass());
        $this->assertContains('auth', $ruta->gatherMiddleware());
        $this->assertContains('verified', $ruta->gatherMiddleware());
    }

    public function test_no_mezcla_notas_ni_asistencias_de_otros_horarios_o_periodos(): void
    {
        $primero = $this->crearMateria();
        $segundo = $this->crearMateria();
        $alumno = $this->crearAlumno(1);
        $this->matricular($alumno, $primero);
        $this->calificar($alumno, $primero, null);
        $this->calificar($alumno, $segundo, 5);
        $this->asistir($alumno, $segundo, true);
        $itemAjeno = $this->calificar($alumno, $primero, 5);
        $corteAjeno = ItemCorteMateriaPeriodo::query()->findOrFail($itemAjeno)->corte_periodo_id;
        CortePeriodo::query()->whereKey($corteAjeno)->update(['periodo_id' => 2]);

        $resumen = $this->resumen()['general'];

        $this->assertSame(1, $resumen['riesgo']);
        $this->assertSame(0.0, $resumen['notaPromedio']);
        $this->assertSame(0, $resumen['calificadas']);
        $this->assertSame(1, $resumen['evaluaciones']);
        $this->assertNull($resumen['asistencia']);
        $this->assertSame(1, $resumen['motivos']['ASISTENCIA_INSUFICIENTE']);
    }

    public function test_el_dashboard_requiere_sesion_y_un_periodo_existente(): void
    {
        Route::get('/prueba-dashboard/{periodo}', DashboardPeriodoController::class)
            ->middleware(['auth', \Illuminate\Routing\Middleware\SubstituteBindings::class]);

        $this->getJson('/prueba-dashboard/999')->assertUnauthorized();
        $this->actingAs(User::factory()->make(['id' => 99]));
        $this->getJson('/prueba-dashboard/999')->assertNotFound();
    }

    public function test_cierre_encola_una_sola_vez_y_no_marca_finalizada_antes_de_calcular(): void
    {
        \Illuminate\Support\Facades\Queue::fake();
        $this->crearMateria();
        $materia = MateriaPeriodo::query()->first();
        $usuario = User::factory()->make(['id' => 99]);
        $gestion = app(\App\Services\GestionCierreMateriaService::class);
        $gestion->solicitar($materia, $usuario);
        $this->assertSame('procesando', $gestion->estado($materia->id));
        $this->assertFalse($materia->fresh()->finalizado);
        try {
            $gestion->solicitar($materia, $usuario);
            $this->fail('No debe permitir dos cierres simultáneos.');
        } catch (\Illuminate\Validation\ValidationException $error) {
            $this->assertStringContainsString('cierre en proceso', $error->getMessage());
        }
        \Illuminate\Support\Facades\Queue::assertPushed(\App\Jobs\FinalizarMateriaJob::class, 1);
    }

    public function test_job_completa_el_cierre_y_libera_el_estado_y_los_errores_permiten_reintentar(): void
    {
        \Illuminate\Support\Facades\Queue::fake();
        \Illuminate\Support\Facades\Mail::fake();
        $this->crearMateria();
        $materia = MateriaPeriodo::query()->first();
        $gestion = app(\App\Services\GestionCierreMateriaService::class);
        $gestion->solicitar($materia, User::factory()->make(['id' => 99]));
        $trabajo = \Illuminate\Support\Facades\Queue::pushed(\App\Jobs\FinalizarMateriaJob::class)->first();
        $servicio = \Mockery::mock(\App\Services\ServicioValidacionMateriaPeriodo::class);
        $servicio->shouldReceive('procesarLoteDeAlumnosPorMateria')->once()->andThrow(new \RuntimeException('Error de prueba'));
        $trabajo->handle($servicio);
        $this->assertSame('error', $gestion->estado($materia->id));
        $this->assertFalse($materia->fresh()->finalizado);
        $gestion->solicitar($materia, User::factory()->make(['id' => 99]));
        $nuevoTrabajo = \Illuminate\Support\Facades\Queue::pushed(\App\Jobs\FinalizarMateriaJob::class)->last();
        $servicioFinal = \Mockery::mock(\App\Services\ServicioValidacionMateriaPeriodo::class);
        $servicioFinal->shouldReceive('procesarLoteDeAlumnosPorMateria')->once()->andReturn(0);
        $nuevoTrabajo->handle($servicioFinal);
        $this->assertTrue($materia->fresh()->finalizado);
        $this->assertNull($gestion->estado($materia->id));
        \Illuminate\Support\Facades\Mail::assertSent(\App\Mail\MateriaFinalizadaMail::class);
    }

    public function test_reabrir_conserva_notas_y_asistencias_y_no_modifica_otras_materias(): void
    {
        $horario = $this->crearMateria();
        $this->crearMateria();
        $alumno = $this->crearAlumno(1);
        $this->matricular($alumno, $horario);
        $this->calificar($alumno, $horario, 5);
        $this->asistir($alumno, $horario, true);
        MateriaPeriodo::query()->update(['finalizado' => true]);
        foreach ([1, 2] as $materiaId) {
            MateriaAprobadaUsuario::query()->insert(['periodo_id' => 1, 'materia_periodo_id' => $materiaId, 'user_id' => $alumno, 'aprobado' => 1]);
        }
        app(\App\Services\GestionCierreMateriaService::class)->reabrir(MateriaPeriodo::query()->findOrFail(1));
        $this->assertFalse(MateriaPeriodo::query()->findOrFail(1)->finalizado);
        $this->assertTrue(MateriaPeriodo::query()->findOrFail(2)->finalizado);
        $this->assertSame([2], MateriaAprobadaUsuario::query()->pluck('materia_periodo_id')->all());
        $this->assertSame(1, AlumnoRespuestaItem::query()->count());
        $this->assertSame(1, ReporteAsistenciaAlumnos::query()->count());
    }

    public function test_no_puede_reabrir_una_materia_con_periodo_cerrado(): void
    {
        $this->crearMateria();
        MateriaPeriodo::query()->update(['finalizado' => true]);
        Periodo::query()->update(['estado' => false]);
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        app(\App\Services\GestionCierreMateriaService::class)->reabrir(MateriaPeriodo::query()->first());
    }

    public function test_calculo_individual_agrupa_por_horario_y_no_multiplica_notas_por_matriculas_repetidas(): void
    {
        $horario = $this->crearMateria();
        $alumno = $this->crearAlumno(1);
        $this->matricular($alumno, $horario);
        $this->matricular($alumno, $horario);
        $this->calificar($alumno, $horario, 4.2);
        $this->asistir($alumno, $horario, true);
        $this->asistir($alumno, $horario, false);
        $servicio = new \App\Services\ServicioValidacionMateriaPeriodo;
        $calcular = new \ReflectionMethod($servicio, 'obtenerResultadosAcademicos');
        $resultados = $calcular->invoke($servicio, MateriaPeriodo::query()->first(), collect([$alumno]));
        $this->assertCount(1, $resultados);
        $this->assertSame(4.2, $resultados[0]->nota_final_calculada);
        $this->assertSame(1, $resultados[0]->total_asistencias);
        $preparar = new \ReflectionMethod($servicio, 'prepararDatosParaGuardar');
        $finales = $preparar->invoke($servicio, $this->periodo, $resultados);
        $this->assertTrue($finales[0]['aprobado']);
        $this->assertSame(4.2, $finales[0]['nota_final']);
        $this->assertSame(1, $finales[0]['total_asistencias']);
    }

    public function test_cierre_real_guarda_reprobacion_y_actualiza_su_motivo(): void
    {
        $horario = $this->crearMateria();
        $alumno = $this->crearAlumno(1);
        $this->matricular($alumno, $horario, ['bloqueado' => true]);
        $this->calificar($alumno, $horario, 1);
        $servicio = new \App\Services\ServicioValidacionMateriaPeriodo;
        $materia = MateriaPeriodo::query()->first();
        $this->assertSame(1, $servicio->procesarLoteDeAlumnosPorMateria($materia, 1, 200));
        $this->assertSame('MATRICULA_BLOQUEADA', MateriaAprobadaUsuario::query()->first()->motivo_reprobacion);
        Matricula::query()->update(['bloqueado' => false]);
        $servicio->procesarLoteDeAlumnosPorMateria($materia, 1, 200);
        $this->assertSame(1, MateriaAprobadaUsuario::query()->count());
        $this->assertSame('NOTA_INSUFICIENTE, ASISTENCIA_INSUFICIENTE', MateriaAprobadaUsuario::query()->first()->motivo_reprobacion);
        $this->assertSame(0, $servicio->procesarLoteDeAlumnosPorMateria($materia, 2, 200));
    }

    public function test_modal_y_acordeones_muestran_sedes_reales_y_excel_solo_al_cerrar_periodo(): void
    {
        $horario = $this->crearMateria();
        $this->asignarSede($horario, 1);
        $this->asignarSede(HorarioMateriaPeriodo::query()->insertGetId(['materia_periodo_id' => 1]), 2);
        MateriaPeriodo::query()->update(['finalizado' => true]);
        $datos = ['periodo' => $this->periodo, 'dashboard' => $this->resumen()];
        $html = view('contenido.paginas.escuelas.periodos.resumen-dashboard-periodo', $datos)->render();
        $this->assertStringContainsString('accordion-button', $html);
        $this->assertStringContainsString('Reabrir materia', $html);
        $this->assertStringNotContainsString('id="informe-materia-1"', $html);
        Periodo::query()->update(['estado' => false]);
        $datos = ['periodo' => $this->periodo->fresh(), 'dashboard' => $this->resumen()];
        $html = view('contenido.paginas.escuelas.periodos.resumen-dashboard-periodo', $datos)->render();
        $this->assertStringContainsString('id="informe-materia-1"', $html);
        $this->assertStringContainsString('Sede 1', $html);
        $this->assertStringContainsString('Sede 2', $html);
        $this->assertStringNotContainsString('class="accion-cierre-materia"', $html);
    }

    public function test_informe_valida_sede_periodo_y_pertenencia_de_materia(): void
    {
        $horario = $this->crearMateria();
        $this->asignarSede($horario, 1);
        $this->registrarRutasDePrueba();
        $this->actingAs(User::factory()->make(['id' => 99]));
        $this->getJson('/prueba-informe/1/1')->assertUnprocessable();
        $this->getJson('/prueba-informe/1/1?sede_id=999')->assertUnprocessable();
        $this->getJson('/prueba-informe/1/1?sede_id=1')->assertStatus(409);
        Periodo::query()->insert(['id' => 2, 'nombre' => 'Otro', 'estado' => false, 'escuela_id' => 1]);
        $this->getJson('/prueba-informe/2/1?sede_id=1')->assertNotFound();
        Periodo::query()->whereKey(1)->update(['estado' => false]);
        $this->getJson('/prueba-informe/1/1?sede_id=1')->assertStatus(409);
        MateriaPeriodo::query()->update(['finalizado' => true]);
        \App\Models\Sede::query()->insert(['id' => 2, 'nombre' => 'Sede ajena']);
        $this->getJson('/prueba-informe/1/1?sede_id=2')->assertUnprocessable();
        \Illuminate\Support\Facades\Queue::fake();
        $this->postJson('/prueba-cerrar/2/1')->assertNotFound();
        \Illuminate\Support\Facades\Queue::assertNothingPushed();
    }

    public function test_excel_genera_una_hoja_por_horario_de_sede_con_totales_finales_numericos(): void
    {
        $primero = $this->crearMateria();
        $segundo = HorarioMateriaPeriodo::query()->insertGetId(['materia_periodo_id' => 1]);
        $tercero = HorarioMateriaPeriodo::query()->insertGetId(['materia_periodo_id' => 1]);
        $this->asignarSede($primero, 1);
        $this->asignarSede($segundo, 1);
        $this->asignarSede($tercero, 2);
        foreach ([$primero, $segundo, $tercero] as $horario) {
            $alumno = $this->crearAlumno(1);
            User::query()->whereKey($alumno)->update(['primer_nombre' => '=1+1', 'primer_apellido' => 'Prueba']);
            $this->matricular($alumno, $horario);
            $this->matricular($alumno, $horario);
            $this->matricular($this->crearAlumno(0), $horario, ['estado_pago_matricula' => 'anulada']);
            MateriaAprobadaUsuario::query()->insert(['periodo_id' => 1, 'materia_periodo_id' => 1, 'user_id' => $alumno, 'aprobado' => 1, 'nota_final' => 4.25, 'total_asistencias' => 8]);
        }
        Periodo::query()->update(['estado' => false]);
        MateriaPeriodo::query()->update(['finalizado' => true]);
        config()->set('excel.temporary_files.local_path', sys_get_temp_dir().'/redil-excel-pruebas');
        $this->registrarRutasDePrueba();
        $this->actingAs(User::factory()->make(['id' => 99]));
        $respuesta = $this->get('/prueba-informe/1/1?sede_id=1');
        $respuesta->assertOk()->assertDownload('Informe-materia-1-sede-1.xlsx');
        \PhpOffice\PhpSpreadsheet\Cell\Cell::setValueBinder(new \PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder);
        $libro = \PhpOffice\PhpSpreadsheet\IOFactory::load($respuesta->baseResponse->getFile()->getPathname());
        $this->assertSame(['Horario 1', 'Horario 2'], $libro->getSheetNames());
        foreach ($libro->getAllSheets() as $hoja) {
            $this->assertSame(2, $hoja->getHighestRow());
            $this->assertSame('00123', $hoja->getCell('A2')->getValue());
            $this->assertSame('s', $hoja->getCell('B2')->getDataType());
            $this->assertSame('=1+1 Prueba', $hoja->getCell('B2')->getValue());
            $this->assertSame('Sede 1', $hoja->getCell('E2')->getValue());
            $this->assertSame('Aprobado', $hoja->getCell('H2')->getValue());
            $this->assertSame(4.25, $hoja->getCell('I2')->getValue());
            $this->assertSame('n', $hoja->getCell('I2')->getDataType());
            $this->assertSame(8, $hoja->getCell('J2')->getValue());
        }
        $libro->disconnectWorksheets();
    }

    public function test_cierre_rechaza_criterios_incompletos_y_horarios_ambiguos(): void
    {
        \Illuminate\Support\Facades\Queue::fake();
        $horario = $this->crearMateria();
        $alumno = $this->crearAlumno(1);
        $this->matricular($alumno, $horario);
        $gestion = app(\App\Services\GestionCierreMateriaService::class);
        $materia = MateriaPeriodo::query()->first();
        $usuario = User::factory()->make(['id' => 99]);
        foreach ([false, true] as $variosHorarios) {
            if ($variosHorarios) {
                $this->calificar($alumno, $horario, 4);
                $otro = HorarioMateriaPeriodo::query()->insertGetId(['materia_periodo_id' => 1]);
                $this->matricular($alumno, $otro);
            }
            try {
                $gestion->solicitar($materia, $usuario);
                $this->fail('No debe iniciar el cierre con datos ambiguos o criterios incompletos.');
            } catch (\Illuminate\Validation\ValidationException $error) {
                $this->assertStringContainsString('Revisa los criterios', $error->getMessage());
            }
        }
        $this->assertNull($gestion->estado($materia->id));
        \Illuminate\Support\Facades\Queue::assertNothingPushed();
    }

    public function test_excel_conserva_ceros_y_no_exporta_calculos_en_proceso_como_finales(): void
    {
        $horario = $this->crearMateria();
        $this->asignarSede($horario, 1);
        foreach ([0, 2] as $estado) {
            $alumno = $this->crearAlumno(1);
            User::query()->whereKey($alumno)->update(['primer_nombre' => 'Alumno '.$estado, 'primer_apellido' => 'Prueba']);
            $this->matricular($alumno, $horario);
            MateriaAprobadaUsuario::query()->insert(['periodo_id' => 1, 'materia_periodo_id' => 1, 'user_id' => $alumno, 'aprobado' => $estado, 'nota_final' => 0, 'total_asistencias' => 0]);
        }
        $exportador = new \App\Exports\InformeHorarioMateriaExport(MateriaPeriodo::query()->first(), HorarioMateriaPeriodo::query()->with('horarioBase.aula.sede')->first());
        $filas = $exportador->collection();
        $this->assertSame('Reprobado', $filas[0][7]);
        $this->assertSame(0.0, $filas[0][8]);
        $this->assertSame(0, $filas[0][9]);
        $this->assertSame('Sin resultado definitivo', $filas[1][7]);
        $this->assertNull($filas[1][8]);
        $this->assertNull($filas[1][9]);
    }

    private function registrarRutasDePrueba(): void
    {
        $middleware = ['web', 'auth', \Illuminate\Routing\Middleware\SubstituteBindings::class];
        Route::get('/prueba-informe/{periodo}/{materiaPeriodo}', [DashboardPeriodoController::class, 'informe'])->middleware($middleware);
        Route::post('/prueba-cerrar/{periodo}/{materiaPeriodo}', [DashboardPeriodoController::class, 'cerrar'])->middleware($middleware);
    }

    private function asignarSede(int $horario, int $sede): void
    {
        if (! \App\Models\Sede::query()->whereKey($sede)->exists()) {
            \App\Models\Sede::query()->insert(['id' => $sede, 'nombre' => 'Sede '.$sede]);
        }
        $aula = \App\Models\Aula::query()->insertGetId(['nombre' => 'Aula '.$horario, 'sede_id' => $sede]);
        $base = \App\Models\HorarioBase::query()->insertGetId(['aula_id' => $aula, 'dia' => 1, 'hora_inicio' => '08:00:00', 'hora_fin' => '10:00:00']);
        HorarioMateriaPeriodo::query()->whereKey($horario)->update(['horario_base_id' => $base]);
    }

    private function resumen(): array
    {
        return app(ResumenPeriodoService::class)->obtener($this->periodo->fresh());
    }

    private function crearAlumno(?int $genero): int
    {
        $alumno = User::factory()->make(['genero' => $genero]);

        return User::query()->insertGetId(['genero' => $alumno->genero, 'primer_nombre' => $alumno->primer_nombre, 'primer_apellido' => $alumno->primer_apellido, 'identificacion' => '00123']);
    }

    private function crearMateria(?int $nivel = null): int
    {
        if ($nivel !== null && ! NivelEscuela::query()->whereKey($nivel)->exists()) {
            NivelEscuela::query()->insert(['id' => $nivel, 'nombre' => 'Nivel '.$nivel]);
            NivelPeriodo::query()->insert(['periodo_id' => 1, 'nivel_escuela_id' => $nivel]);
        }
        $materia = Materia::query()->insertGetId(['nombre' => 'Materia '.(Materia::query()->count() + 1)]);
        $instancia = MateriaPeriodo::query()->insertGetId(['materia_id' => $materia, 'periodo_id' => 1, 'nivel_id' => $nivel]);

        return HorarioMateriaPeriodo::query()->insertGetId(['materia_periodo_id' => $instancia]);
    }

    private function matricular(int $alumno, int $horario, array $atributos = []): void
    {
        Matricula::query()->insert(array_merge(['user_id' => $alumno, 'horario_materia_periodo_id' => $horario, 'periodo_id' => 1], $atributos));
    }

    private function calificar(int $alumno, int $horario, ?float $nota, float $pesoItem = 100, float $pesoCorte = 100): int
    {
        $corte = CortePeriodo::query()->insertGetId(['periodo_id' => 1, 'porcentaje' => $pesoCorte]);
        $item = ItemCorteMateriaPeriodo::query()->insertGetId([
            'materia_periodo_id' => HorarioMateriaPeriodo::query()->findOrFail($horario)->materia_periodo_id,
            'horario_materia_periodo_id' => $horario, 'corte_periodo_id' => $corte, 'porcentaje' => $pesoItem,
        ]);
        AlumnoRespuestaItem::query()->insert(['user_id' => $alumno, 'item_corte_materia_periodo_id' => $item, 'nota_obtenida' => $nota]);

        return $item;
    }

    private function asistir(int $alumno, int $horario, bool $asistio): void
    {
        $reporte = ReporteAsistenciaClase::query()->insertGetId(['horario_materia_periodo_id' => $horario]);
        ReporteAsistenciaAlumnos::query()->insert(['user_id' => $alumno, 'reporte_asistencia_clase_id' => $reporte, 'asistio' => $asistio]);
    }
}
