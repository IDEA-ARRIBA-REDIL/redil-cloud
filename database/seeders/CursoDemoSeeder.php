<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Curso;
use App\Models\CursoModulo;
use App\Models\CursoItem;
use App\Models\CursoLeccion;
use App\Models\CursoUser;
use App\Models\User;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CursoDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // NOTA: No usamos truncate para no afectar datos de producción u otras pruebas

        $cursosData = [
            [
                'nombre' => 'Mentoreo Espiritual 1 | Introducción',
                'descripcion_corta' => 'Aprende los fundamentos del acompañamiento espiritual efectivo.',
                'descripcion_larga' => '<p>Este curso te guiará por los principios básicos del mentoreo espiritual, brindando herramientas prácticas para acompañar a otros en su crecimiento personal y espiritual.</p><p>Ideal para líderes y voluntarios enfocados en el cuidado de la congregación.</p>',
                'nivel_dificultad' => 'Principiante',
                'estado' => 'Publicado',
                'es_gratuito' => false,
                'precio' => 15000,
                'duracion_estimada_dias' => 30,
                'moneda_id' => 1,
            ],
            [
                'nombre' => 'Liderazgo Transformacional D8',
                'descripcion_corta' => 'Desarrolla habilidades de liderazgo impactantes y efectivas.',
                'descripcion_larga' => '<p>Aprende a liderar equipos con propósito y visión transformadora basado en principios bíblicos y prácticos. Exploraremos técnicas de comunicación, resolución de conflictos y formación de equipos sólidos.</p>',
                'nivel_dificultad' => 'Intermedio',
                'estado' => 'Publicado',
                'es_gratuito' => false,
                'precio' => 25000,
                'duracion_estimada_dias' => 60,
                'moneda_id' => 1,
            ],
            [
                'nombre' => 'Finanzas Bíblicas Prácticas',
                'descripcion_corta' => 'Principios divinos para el manejo del dinero personal y familiar.',
                'descripcion_larga' => '<p>Descubre cómo administrar los recursos que Dios te ha dado con sabiduría e integridad para ser de bendición a otros. Incluye plantillas y ejercicios prácticos de presupuesto.</p>',
                'nivel_dificultad' => 'Todas',
                'estado' => 'Publicado',
                'es_gratuito' => true, // Uno gratuito
                'precio' => 0,
                'duracion_estimada_dias' => 15,
                'moneda_id' => 1,
            ]
        ];

        foreach ($cursosData as $data) {
            $data['slug'] = Str::slug($data['nombre']) . '-' . rand(100, 999);

            $curso = Curso::create($data);

            // Asignar métodos de pago solicitados (ids 1 y 5)
            // Solo si no es gratuito necesita pagos lógicamente, pero se los asiganos a todos por consistencia de la orden
            $curso->tiposPago()->sync([1, 5]);

            // Crear 2 o 3 módulos por curso
            $cantidadModulos = rand(2, 4);
            for ($i = 1; $i <= $cantidadModulos; $i++) {
                $modulo = CursoModulo::create([
                    'curso_id' => $curso->id,
                    'nombre' => "Sección $i: " . ($i == 1 ? 'Introducción y Conceptos Básicos' : 'Profundización Táctica'),
                    'descripcion' => "Todo el contenido detallado correspondiente a la sección $i.",
                    'orden' => $i,
                ]);

                // Crear de 2 a 4 ítems (lecciones/videos) por módulo
                $cantidadItems = rand(2, 4);
                for ($j = 1; $j <= $cantidadItems; $j++) {

                    // Mezclar entre videos (tipo 2) y lecturas (tipo 3) basándonos en CursoItemTipoSeeder
                    $tipoId = rand(2, 3);
                    $esVideo = ($tipoId == 2);

                    $leccion = CursoLeccion::create([
                        'contenido_html' => "<p>Este es el desarrollo en texto de la lección $j. Aquí los estudiantes podrán leer y tomar notas de los conceptos enseñados.</p>",
                        'video_url' => $esVideo ? 'https://www.youtube.com/watch?v=Z6TtOACmnqg' : null,
                        'video_plataforma' => $esVideo ? 'youtube' : null,
                    ]);

                    CursoItem::create([
                        'curso_modulo_id' => $modulo->id,
                        'curso_item_tipo_id' => $tipoId,
                        'titulo' => "Tema $j: " . ($esVideo ? 'Video Explicativo' : 'Lectura Principal'),
                        'orden' => $j,
                        'itemable_id' => $leccion->id,
                        'itemable_type' => CursoLeccion::class,
                    ]);
                }
            }

            // --- INICIO DE AGREGAR EVALUACION DEMO AI ---
            if ($data['nombre'] == 'Mentoreo Espiritual 1 | Introducción') {
                $moduloEval = CursoModulo::create([
                    'curso_id' => $curso->id,
                    'nombre' => "Evaluación Final",
                    'descripcion' => "Pon a prueba tus conocimientos bíblicos adquiridos.",
                    'orden' => $cantidadModulos + 1,
                ]);

                $evaluacion = \App\Models\CursoEvaluacion::create([
                    'minimo_aprobacion' => 70,
                    'limite_tiempo' => 30,
                ]);

                $tipoEvaluacionId = \App\Models\CursoItemTipo::where('codigo', 'quiz')->value('id') ?? 4;

                CursoItem::create([
                    'curso_modulo_id' => $moduloEval->id,
                    'curso_item_tipo_id' => $tipoEvaluacionId,
                    'titulo' => "Examen Final: Conocimientos Bíblicos",
                    'orden' => 1,
                    'itemable_id' => $evaluacion->id,
                    'itemable_type' => \App\Models\CursoEvaluacion::class,
                ]);

                $preguntas = [
                    [
                        'pregunta' => '¿Cuál es el primer libro de la Biblia?',
                        'tipo_respuesta' => 'unica',
                        'opciones' => [
                            ['opcion' => 'Génesis', 'es_correcta' => true],
                            ['opcion' => 'Éxodo', 'es_correcta' => false],
                            ['opcion' => 'Levítico', 'es_correcta' => false],
                        ]
                    ],
                    [
                        'pregunta' => 'Matusalén es el hombre más viejo mencionado en la Biblia.',
                        'tipo_respuesta' => 'verdadero_falso',
                        'opciones' => [
                            ['opcion' => 'Verdadero', 'es_correcta' => true],
                            ['opcion' => 'Falso', 'es_correcta' => false],
                        ]
                    ],
                    [
                        'pregunta' => '¿Cuáles de estos son Evangelios del Nuevo Testamento? (Selección múltiple)',
                        'tipo_respuesta' => 'multiple',
                        'opciones' => [
                            ['opcion' => 'Mateo', 'es_correcta' => true],
                            ['opcion' => 'Isaías', 'es_correcta' => false],
                            ['opcion' => 'Lucas', 'es_correcta' => true],
                            ['opcion' => 'Apocalipsis', 'es_correcta' => false],
                        ]
                    ],
                    [
                        'pregunta' => '¿Quién fue tragado por un gran pez?',
                        'tipo_respuesta' => 'unica',
                        'opciones' => [
                            ['opcion' => 'Moisés', 'es_correcta' => false],
                            ['opcion' => 'Jonás', 'es_correcta' => true],
                            ['opcion' => 'Noé', 'es_correcta' => false],
                        ]
                    ],
                    [
                        'pregunta' => '¿Cuántos días estuvo Jesús en el desierto?',
                        'tipo_respuesta' => 'unica',
                        'opciones' => [
                            ['opcion' => '12', 'es_correcta' => false],
                            ['opcion' => '40', 'es_correcta' => true],
                            ['opcion' => '3', 'es_correcta' => false],
                        ]
                    ],
                    [
                        'pregunta' => 'David venció a Goliat con una espada.',
                        'tipo_respuesta' => 'verdadero_falso',
                        'opciones' => [
                            ['opcion' => 'Verdadero', 'es_correcta' => false],
                            ['opcion' => 'Falso', 'es_correcta' => true],
                        ]
                    ],
                    [
                        'pregunta' => '¿Cuáles de los siguientes fueron discípulos de Jesús? (Selección múltiple)',
                        'tipo_respuesta' => 'multiple',
                        'opciones' => [
                            ['opcion' => 'Pedro', 'es_correcta' => true],
                            ['opcion' => 'Juan', 'es_correcta' => true],
                            ['opcion' => 'Pablo', 'es_correcta' => false],
                            ['opcion' => 'Judas Iscariote', 'es_correcta' => true],
                        ]
                    ],
                    [
                        'pregunta' => '¿Qué alimento cayó del cielo para los israelitas en el desierto?',
                        'tipo_respuesta' => 'unica',
                        'opciones' => [
                            ['opcion' => 'Pan', 'es_correcta' => false],
                            ['opcion' => 'Maná', 'es_correcta' => true],
                            ['opcion' => 'Codornices', 'es_correcta' => false],
                        ]
                    ],
                    [
                        'pregunta' => 'Pablo originalmente se llamaba Saulo.',
                        'tipo_respuesta' => 'verdadero_falso',
                        'opciones' => [
                            ['opcion' => 'Verdadero', 'es_correcta' => true],
                            ['opcion' => 'Falso', 'es_correcta' => false],
                        ]
                    ],
                    [
                        'pregunta' => '¿Cuáles son frutos del Espíritu según Gálatas 5? (Selección múltiple)',
                        'tipo_respuesta' => 'multiple',
                        'opciones' => [
                            ['opcion' => 'Amor', 'es_correcta' => true],
                            ['opcion' => 'Paciencia', 'es_correcta' => true],
                            ['opcion' => 'Venganza', 'es_correcta' => false],
                            ['opcion' => 'Paz', 'es_correcta' => true],
                        ]
                    ],
                ];

                foreach ($preguntas as $pIndex => $pData) {
                    $preguntaBD = \App\Models\CursoPregunta::create([
                        'curso_evaluacion_id' => $evaluacion->id,
                        'pregunta' => $pData['pregunta'],
                        'tipo_respuesta' => $pData['tipo_respuesta'],
                        'orden' => $pIndex + 1,
                    ]);

                    foreach ($pData['opciones'] as $oIndex => $oData) {
                        \App\Models\CursoPreguntaOpcion::create([
                            'curso_pregunta_id' => $preguntaBD->id,
                            'opcion' => $oData['opcion'],
                            'es_correcta' => $oData['es_correcta'],
                        ]);
                    }
                }
            }
            // --- FIN DE AGREGAR EVALUACION ---

            // CREAR INSCRIPCIONES DE USUARIOS (Ids entre 40 y 70)
            // Obtenemos todos los users cuyos IDs estén entre 40 y 70
            $usersTarget = User::whereBetween('id', [40, 70])->get();

            if ($usersTarget->count() > 0) {
                // Seleccionar aleatoriamente una cantidad de usuarios de ese bloque (entre 5 y 15 si hay suficientes)
                $cantidadInscribir = min($usersTarget->count(), rand(5, 15));
                $usersInscribir = $usersTarget->random($cantidadInscribir);

                foreach ($usersInscribir as $user) {
                    CursoUser::create([
                        'curso_id' => $curso->id,
                        'user_id' => $user->id,
                        'estado' => 'activo',
                        'fecha_inscripcion' => Carbon::now()->subDays(rand(1, 60)), // Inscripción en disntintas fechas
                        'porcentaje_progreso' => rand(0, 100),
                    ]);
                }

                // Asegurar que el usuario 1 sea parte del equipo (Creador / Asesor)
                $tipoCreador = \App\Models\TipoCargoCurso::firstOrCreate(['nombre' => 'Creador'], ['puede_responder_preguntas' => true, 'es_moderador' => true]);
                $tipoAsesor = \App\Models\TipoCargoCurso::firstOrCreate(['nombre' => 'Asesor'], ['puede_responder_preguntas' => true, 'es_moderador' => true]);

                $curso->equipo()->firstOrCreate([
                    'usuario_id' => 1,
                    'tipo_cargo_curso_id' => $tipoCreador->id,
                ], ['activo' => true]);

                $curso->equipo()->firstOrCreate([
                    'usuario_id' => 1,
                    'tipo_cargo_curso_id' => $tipoAsesor->id,
                ], ['activo' => true]);

                // Generar conversaciones de foro de demostración para el curso
                $curso->load('modulos.items');
                $itemsDisponibles = $curso->modulos->flatMap->items;
                $cantidadHilos = rand(3, 6);

                for ($h = 0; $h < $cantidadHilos; $h++) {
                    $alumnoDuda = $usersInscribir->random();
                    $itemAsociado = rand(0, 1) && $itemsDisponibles->count() > 0 ? $itemsDisponibles->random() : null;

                    $estados = ['pendiente', 'resuelto', 'cerrado'];
                    $estadoActual = $estados[array_rand($estados)];

                    $hilo = \App\Models\CursoForoHilo::create([
                        'curso_id' => $curso->id,
                        'curso_item_id' => $itemAsociado ? $itemAsociado->id : null,
                        'user_id' => $alumnoDuda->id,
                        'titulo' => 'Duda sobre ' . ($itemAsociado ? $itemAsociado->titulo : 'conceptos generales'),
                        'cuerpo' => 'Hola, tengo una pregunta sobre este tema en específico. He intentado aplicar esto en mi entorno pero me da un error inesperado. ¿Alguien me podría guiar en esto? Muchas gracias.',
                        'estado' => $estadoActual,
                        'created_at' => Carbon::now()->subDays(rand(2, 15)),
                    ]);

                    if ($estadoActual !== 'pendiente') {
                        // El asesor (User 1) responde oficialmente
                        \App\Models\CursoForoRespuesta::create([
                            'hilo_id' => $hilo->id,
                            'user_id' => 1,
                            'cuerpo' => '¡Hola! Excelente pregunta. Para resolver este inconveniente, asegúrate de haber seguido paso a paso la lección anterior. El error generalmente ocurre por un problema de configuración inicial. ¡Un saludo!',
                            'es_respuesta_oficial' => true,
                            'created_at' => $hilo->created_at->addHours(rand(1, 24)),
                        ]);

                        if ($estadoActual === 'cerrado') {
                            // El estudiante agradece
                            \App\Models\CursoForoRespuesta::create([
                                'hilo_id' => $hilo->id,
                                'user_id' => $alumnoDuda->id,
                                'cuerpo' => 'Efectivamente era eso. ¡Ajusté la configuración y ya no tengo problemas! Mil gracias.',
                                'es_respuesta_oficial' => false,
                                'created_at' => $hilo->created_at->addHours(rand(25, 48)),
                            ]);
                        }
                    } else {
                        // Si está pendiente, puede ser que otro alumno haya opinado
                        if (rand(0, 1)) {
                            $otroAlumno = $usersInscribir->where('id', '!=', $alumnoDuda->id)->first() ?? $alumnoDuda;
                            \App\Models\CursoForoRespuesta::create([
                                'hilo_id' => $hilo->id,
                                'user_id' => $otroAlumno->id,
                                'cuerpo' => 'A mí me pasa exactamente lo mismo. Ojalá alguien pueda darnos luz en este tema, estaré atento al hilo.',
                                'es_respuesta_oficial' => false,
                                'created_at' => $hilo->created_at->addHours(rand(1, 10)),
                            ]);
                        }
                    }
                }
            }
        }
    }
}
