<?php

namespace Tests\Unit;

use App\Http\Controllers\MaestroController;
use App\Http\Controllers\MateriaController;
use App\Models\AlumnoRespuestaItem;
use App\Models\CorteEscuela;
use App\Models\CortePeriodo;
use App\Models\HorarioBase;
use App\Models\HorarioMateriaPeriodo;
use App\Models\ItemCorteMateriaPeriodo;
use App\Models\Maestro;
use App\Models\MateriaPeriodo;
use App\Models\Periodo;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use ReflectionMethod;
use Tests\TestCase;

class MateriaDescripcionResumenTest extends TestCase
{
    public function test_descripcion_resumen_elimina_html_y_trunca_a_20_caracteres_con_puntos_suspensivos(): void
    {
        $materiaPeriodo = new MateriaPeriodo([
            'descripcion' => '<p>DESCRIPCION DE LA MATERIA</p>',
        ]);

        $this->assertSame('DESCRIPCION DE LA MA...', $materiaPeriodo->descripcionResumen(20));
    }

    public function test_descripcion_resumen_conserva_texto_corto_sin_puntos_suspensivos(): void
    {
        $materiaPeriodo = new MateriaPeriodo([
            'descripcion' => '<p>Curso corto</p>',
        ]);

        $this->assertSame('Curso corto', $materiaPeriodo->descripcionResumen(20));
    }

    public function test_descripcion_resumen_retorna_null_cuando_quill_esta_vacio(): void
    {
        foreach ([null, '', '<p><br></p>', '<p>&nbsp;   </p>', '<div><br></div>'] as $valorVacio) {
            $materiaPeriodo = new MateriaPeriodo([
                'descripcion' => $valorVacio,
            ]);

            $this->assertNull($materiaPeriodo->descripcionResumen(20));
        }
    }

    public function test_descripcion_resumen_decodifica_entidades_y_separa_bloques_html(): void
    {
        $materiaPeriodo = new MateriaPeriodo([
            'descripcion' => '<p>Educaci&oacute;n</p><p>b&iacute;blica avanzada</p>',
        ]);

        $this->assertSame('Educación bíblica av...', $materiaPeriodo->descripcionResumen(20));
    }

    public function test_normalizar_descripcion_html_convierte_vacio_de_quill_a_null_y_conserva_contenido(): void
    {
        $controller = new MateriaController;
        $metodo = new ReflectionMethod($controller, 'normalizarDescripcionHtml');
        $metodo->setAccessible(true);

        $this->assertNull($metodo->invoke($controller, null));
        $this->assertNull($metodo->invoke($controller, ''));
        $this->assertNull($metodo->invoke($controller, '<p><br></p>'));
        $this->assertNull($metodo->invoke($controller, '<p>&nbsp;  </p>'));

        $htmlValido = '<p><strong>Contenido</strong> de prueba</p>';
        $this->assertSame($htmlValido, $metodo->invoke($controller, $htmlValido));

        $htmlConImagen = '<p><img src="portada.png"></p>';
        $this->assertSame($htmlConImagen, $metodo->invoke($controller, $htmlConImagen));
    }

    public function test_vistas_y_modal_usan_resumen_en_listados_y_solo_nombre_en_select(): void
    {
        $misHorarios = file_get_contents(resource_path('views/contenido/paginas/escuelas/maestros/mis-horarios.blade.php'));
        $horariosMaestro = file_get_contents(resource_path('views/contenido/paginas/escuelas/maestros/horarios-maestro.blade.php'));
        $gestionarMateria = file_get_contents(resource_path('views/contenido/paginas/escuelas/materias/gestionar-materia.blade.php'));
        $asignarModal = file_get_contents(app_path('Livewire/Maestros/AsignarHorarioModal.php'));

        $this->assertStringContainsString('descripcionResumen(20)', $misHorarios);
        $this->assertStringContainsString('col-12 col-md-6 mb-4', $misHorarios);
        $this->assertStringContainsString("route('maestros.dashboardClase'", $misHorarios);
        $this->assertStringContainsString('descripcionResumen(20)', $horariosMaestro);
        $this->assertStringContainsString('col-12 col-md-6 mb-4', $horariosMaestro);
        $this->assertStringContainsString('form-eliminar-asignacion', $horariosMaestro);
        $this->assertStringContainsString('sincronizarDescripcionQuill', $gestionarMateria);
        $this->assertStringContainsString('value="{{ old(\'descripción\', $materia->descripcion) }}"', $gestionarMateria);
        $this->assertStringContainsString("'nombre_display' => \$mp->materia->nombre ?? 'N/A'", $asignarModal);
        $this->assertStringNotContainsString('$mp->descripcion', $asignarModal);
    }

    public function test_plantillas_blade_modificadas_compilan_sin_errores(): void
    {
        $blade = $this->app->make('blade.compiler');

        foreach ([
            resource_path('views/contenido/paginas/escuelas/maestros/mis-horarios.blade.php'),
            resource_path('views/contenido/paginas/escuelas/maestros/horarios-maestro.blade.php'),
            resource_path('views/contenido/paginas/escuelas/maestros/dashboard-clase.blade.php'),
            resource_path('views/contenido/paginas/escuelas/maestros/gestionar-alumno.blade.php'),
            resource_path('views/contenido/paginas/escuelas/maestros/reporte-asistencia-alumnos.blade.php'),
            resource_path('views/livewire/maestros/reporte-asistencia-alumnos.blade.php'),
            resource_path('views/contenido/paginas/escuelas/alumnos/perfil-materia.blade.php'),
            resource_path('views/livewire/alumno/calificaciones.blade.php'),
            resource_path('views/livewire/alumno/calificaciones-alumno.blade.php'),
            resource_path('views/contenido/paginas/escuelas/materias/gestionar-materia.blade.php'),
        ] as $rutaVista) {
            $compilado = $blade->compileString((string) file_get_contents($rutaVista));
            $this->assertNotEmpty($compilado);
        }

        $dashboardClase = (string) file_get_contents(resource_path('views/contenido/paginas/escuelas/maestros/dashboard-clase.blade.php'));
        $this->assertStringContainsString('.student-status .badge.bg-danger', $dashboardClase);
        $this->assertStringContainsString('color: #fff !important;', $dashboardClase);
        $this->assertStringContainsString('.student-status .badge.bg-warning', $dashboardClase);
        $this->assertStringContainsString('color: #2f2b3d !important;', $dashboardClase);

        $gestionarAlumno = (string) file_get_contents(resource_path('views/contenido/paginas/escuelas/maestros/gestionar-alumno.blade.php'));
        $this->assertStringContainsString('corte-eval-card', $gestionarAlumno);
        $this->assertStringContainsString('student-avatar-circle', $gestionarAlumno);
        $this->assertStringNotContainsString('corte_periodo_id', $gestionarAlumno);
        $this->assertStringNotContainsString('mdi mdi-', $gestionarAlumno);

        $reporteAsistenciaVista = (string) file_get_contents(resource_path('views/contenido/paginas/escuelas/maestros/reporte-asistencia-alumnos.blade.php'));
        $reporteAsistenciaLivewire = (string) file_get_contents(resource_path('views/livewire/maestros/reporte-asistencia-alumnos.blade.php'));
        $this->assertStringContainsString('period-status-card', $reporteAsistenciaVista);
        $this->assertStringNotContainsString('mdi mdi-', $reporteAsistenciaVista);
        $this->assertStringContainsString('attendance-pill', $reporteAsistenciaLivewire);
        $this->assertStringNotContainsString('mdi mdi-', $reporteAsistenciaLivewire);

        $perfilMateria = (string) file_get_contents(resource_path('views/contenido/paginas/escuelas/alumnos/perfil-materia.blade.php'));
        $calificaciones = (string) file_get_contents(resource_path('views/livewire/alumno/calificaciones.blade.php'));
        $calificacionesAlumno = (string) file_get_contents(resource_path('views/livewire/alumno/calificaciones-alumno.blade.php'));
        $this->assertStringContainsString('materia-hero-card', $perfilMateria);
        $this->assertStringNotContainsString('#2b3a67', $perfilMateria);
        $this->assertStringNotContainsString('mdi mdi-', $perfilMateria);
        $this->assertStringContainsString('col-12 col-md-6 col-xl-4', $calificaciones);
        $this->assertStringNotContainsString('mdi mdi-', $calificaciones);
        $this->assertStringContainsString('x-show="subiendo" x-cloak', $calificaciones);
        $this->assertStringContainsString('col-12 col-md-6 col-xl-4', $calificacionesAlumno);
        $this->assertStringNotContainsString('mdi mdi-', $calificacionesAlumno);
        $this->assertStringContainsString('x-show="subiendo" x-cloak', $calificacionesAlumno);
    }

    public function test_agrupar_items_evaluacion_por_corte_usa_numero_de_corte_y_no_id(): void
    {
        $corteEscuela1 = new CorteEscuela(['nombre' => 'Corte 1', 'orden' => 1]);
        $corteEscuela2 = new CorteEscuela(['nombre' => 'Corte 2', 'orden' => 2]);
        $corteEscuela3 = new CorteEscuela(['nombre' => 'Corte 3', 'orden' => 3]);

        $cp1 = new CortePeriodo(['porcentaje' => 35]);
        $cp1->id = 47;
        $cp1->setRelation('corteEscuela', $corteEscuela1);

        $cp2 = new CortePeriodo(['porcentaje' => 35]);
        $cp2->id = 52;
        $cp2->setRelation('corteEscuela', $corteEscuela2);

        $cp3 = new CortePeriodo(['porcentaje' => 30]);
        $cp3->id = 89;
        $cp3->setRelation('corteEscuela', $corteEscuela3);

        $itemCorte3 = new ItemCorteMateriaPeriodo(['corte_periodo_id' => 89, 'nombre' => 'Examen Final', 'porcentaje' => 100]);
        $itemCorte3->setRelation('cortePeriodo', $cp3);
        $itemCorte3->setRelation('respuestas', new EloquentCollection);

        $respuesta1 = new AlumnoRespuestaItem(['nota_obtenida' => 4.0]);
        $itemCorte1A = new ItemCorteMateriaPeriodo(['corte_periodo_id' => 47, 'nombre' => 'Taller 1', 'porcentaje' => 50]);
        $itemCorte1A->setRelation('cortePeriodo', $cp1);
        $itemCorte1A->setRelation('respuestas', new EloquentCollection([$respuesta1]));

        $respuesta2 = new AlumnoRespuestaItem(['nota_obtenida' => 5.0]);
        $itemCorte1B = new ItemCorteMateriaPeriodo(['corte_periodo_id' => 47, 'nombre' => 'Quiz 1', 'porcentaje' => 50]);
        $itemCorte1B->setRelation('cortePeriodo', $cp1);
        $itemCorte1B->setRelation('respuestas', new EloquentCollection([$respuesta2]));

        $controller = new MaestroController;
        $metodo = new ReflectionMethod($controller, 'agruparItemsEvaluacionPorCorte');
        $metodo->setAccessible(true);

        $resultado = $metodo->invoke(
            $controller,
            collect([$itemCorte3, $itemCorte1A, $itemCorte1B]),
            collect([$cp1, $cp2, $cp3])
        );

        $this->assertCount(2, $resultado);

        $this->assertSame(1, $resultado[0]['numero']);
        $this->assertSame('Corte 1', $resultado[0]['etiqueta']);
        $this->assertSame(35.0, $resultado[0]['porcentaje']);
        $this->assertSame(2, $resultado[0]['total_items']);
        $this->assertSame(2, $resultado[0]['items_calificados']);
        $this->assertSame(100.0, $resultado[0]['peso_items_total']);
        $this->assertSame(4.5, $resultado[0]['nota_corte']);

        $this->assertSame(3, $resultado[1]['numero']);
        $this->assertSame('Corte 3', $resultado[1]['etiqueta']);
        $this->assertSame(30.0, $resultado[1]['porcentaje']);
        $this->assertSame(1, $resultado[1]['total_items']);
        $this->assertSame(0, $resultado[1]['items_calificados']);
        $this->assertNull($resultado[1]['nota_corte']);
    }

    public function test_resumen_encabezado_clase_muestra_maestro_real_o_sin_asignar_y_horario(): void
    {
        $periodo = new Periodo(['nombre' => 'SEMESTRE 2 PILOTO ESPECIALIZACION']);
        $materiaPeriodo = new MateriaPeriodo;
        $materiaPeriodo->setRelation('periodo', $periodo);

        $horarioBase = new HorarioBase([
            'dia' => 4,
            'hora_inicio' => '19:00:00',
            'hora_fin' => '21:00:00',
        ]);

        $usuarioMaestro = new User([
            'primer_nombre' => 'Jenny',
            'segundo_nombre' => 'Paola',
            'primer_apellido' => 'Salcedo',
        ]);
        $maestroAsignado = new Maestro;
        $maestroAsignado->setRelation('user', $usuarioMaestro);

        $horarioConMaestro = new HorarioMateriaPeriodo;
        $horarioConMaestro->setRelation('materiaPeriodo', $materiaPeriodo);
        $horarioConMaestro->setRelation('horarioBase', $horarioBase);
        $horarioConMaestro->setRelation('maestros', new EloquentCollection([$maestroAsignado]));

        $this->assertSame(
            'Periodo: SEMESTRE 2 PILOTO ESPECIALIZACION | Maestro: Jenny Paola Salcedo | Horario: Jueves 07:00 PM - 09:00 PM',
            $horarioConMaestro->resumenEncabezadoClase()
        );

        // Sin maestro asignado: nunca debe tomar el usuario autenticado
        $usuarioAdminLogueado = new User([
            'primer_nombre' => 'Esteban',
            'primer_apellido' => 'Perdomo',
        ]);
        $this->actingAs($usuarioAdminLogueado);

        $horarioSinMaestro = new HorarioMateriaPeriodo;
        $horarioSinMaestro->setRelation('materiaPeriodo', $materiaPeriodo);
        $horarioSinMaestro->setRelation('horarioBase', $horarioBase);
        $horarioSinMaestro->setRelation('maestros', new EloquentCollection);

        $this->assertSame('Sin asignar', $horarioSinMaestro->nombreMaestroAsignado());
        $this->assertSame(
            'Periodo: SEMESTRE 2 PILOTO ESPECIALIZACION | Maestro: Sin asignar | Horario: Jueves 07:00 PM - 09:00 PM',
            $horarioSinMaestro->resumenEncabezadoClase()
        );

        $controlador = (string) file_get_contents(app_path('Http/Controllers/MaestroController.php'));
        $this->assertStringNotContainsString('| Docente:', $controlador);
    }
}
