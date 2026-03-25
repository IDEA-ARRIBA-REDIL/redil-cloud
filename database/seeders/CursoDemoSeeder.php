<?php

namespace Database\Seeders;

use App\Models\Curso;
use App\Models\CursoItem;
use App\Models\CursoItemTipo;
use App\Models\CursoLeccion;
use App\Models\CursoModulo;
use App\Models\CursoEvaluacion;
use App\Models\CursoPregunta;
use App\Models\CursoPreguntaOpcion;
use App\Models\CursoUser;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CursoDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener IDs de Carreras y Categorías para asociación dinámica
        $carrerasIds = \App\Models\Carrera::pluck('id')->toArray();
        $categoriasIds = \App\Models\CategoriaCurso::pluck('id')->toArray();

        // Si no hay carreras o categorías, avisar o usar null (pero solicitó que estén asociados)
        if (empty($carrerasIds) || empty($categoriasIds)) {
            $this->command->warn('Debes ejecutar CarreraSeeder y CategoriaCursoSeeder antes de este seeder.');
        }

        $cursosData = [
            [
                'nombre' => 'Mentoreo Espiritual 1 | Introducción',
                'descripcion_corta' => 'Aprende los fundamentos del acompañamiento espiritual efectivo.',
                'descripcion_larga' => '<p>Este curso te guiará por los principios básicos del mentoreo espiritual, brindando herramientas prácticas para acompañar a otros en su crecimiento personal y espiritual.</p>',
                'nivel_dificultad' => 'Principiante',
                'estado' => 'Publicado',
                'es_gratuito' => false,
                'precio' => 15000,
                'duracion_estimada_dias' => 30,
                'moneda_id' => 1,
                'carrera_id' => $carrerasIds[0] ?? null,
            ],
            [
                'nombre' => 'Liderazgo Transformacional D8',
                'descripcion_corta' => 'Desarrolla habilidades de liderazgo impactantes y efectivas.',
                'descripcion_larga' => '<p>Aprende a liderar equipos con propósito y visión transformadora basado en principios bíblicos y prácticos.</p>',
                'nivel_dificultad' => 'Intermedio',
                'estado' => 'Publicado',
                'es_gratuito' => false,
                'precio' => 25000,
                'duracion_estimada_dias' => 60,
                'moneda_id' => 1,
                'carrera_id' => $carrerasIds[1] ?? ($carrerasIds[0] ?? null),
            ],
            [
                'nombre' => 'Finanzas Bíblicas Prácticas',
                'descripcion_corta' => 'Principios divinos para el manejo del dinero personal y familiar.',
                'descripcion_larga' => '<p>Descubre cómo administrar los recursos que Dios te ha dado con sabiduría e integridad.</p>',
                'nivel_dificultad' => 'Todas',
                'estado' => 'Publicado',
                'es_gratuito' => true,
                'precio' => 0,
                'duracion_estimada_dias' => 15,
                'moneda_id' => 1,
                'carrera_id' => $carrerasIds[2] ?? ($carrerasIds[0] ?? null),
            ],
        ];

        foreach ($cursosData as $index => $data) {
            $data['slug'] = Str::slug($data['nombre']).'-'.rand(100, 999);

            $curso = Curso::create($data);

            // Asociar categorías (pueden ser varias por curso)
            if (! empty($categoriasIds)) {
                $curso->categorias()->sync([$categoriasIds[$index % count($categoriasIds)], $categoriasIds[($index + 1) % count($categoriasIds)]]);
            }

            // Asignar métodos de pago
            $curso->tiposPago()->sync([1, 5]);

            // Módulos y Lecciones
            $cantidadModulos = rand(2, 3);
            for ($i = 1; $i <= $cantidadModulos; $i++) {
                $modulo = CursoModulo::create([
                    'curso_id' => $curso->id,
                    'nombre' => "Módulo $i: Contenido Detallado",
                    'descripcion' => "Descripción de la sección $i.",
                    'orden' => $i,
                ]);

                for ($j = 1; $j <= 2; $j++) {
                    $tipoId = rand(2, 3);
                    $leccion = CursoLeccion::create([
                        'contenido_html' => "<p>Desarrollo de la lección $j.</p>",
                        'video_url' => ($tipoId == 2) ? 'https://www.youtube.com/watch?v=Z6TtOACmnqg' : null,
                    ]);

                    CursoItem::create([
                        'curso_modulo_id' => $modulo->id,
                        'curso_item_tipo_id' => $tipoId,
                        'titulo' => "Tema $j de la sección $i",
                        'orden' => $j,
                        'itemable_id' => $leccion->id,
                        'itemable_type' => CursoLeccion::class,
                    ]);
                }

                // Agregar Quiz de la Biblia si es el curso de Mentoreo Espiritual 1
                if ($curso->nombre === 'Mentoreo Espiritual 1 | Introducción' && $i === 1) {
                    $tipoQuiz = CursoItemTipo::where('codigo', 'quiz')->first();
                    
                    if ($tipoQuiz) {
                        $evaluacion = CursoEvaluacion::create([
                            'minimo_aprobacion' => 70,
                            'limite_tiempo' => 20,
                            'cantidad_repeticiones' => 3,
                        ]);

                        $preguntas = [
                            [
                                'pregunta' => '¿Quién construyó el arca?',
                                'tipo' => 'unica',
                                'opciones' => [
                                    ['opcion' => 'Noé', 'correcta' => true],
                                    ['opcion' => 'Moisés', 'correcta' => false],
                                    ['opcion' => 'Abraham', 'correcta' => false],
                                ]
                            ],
                            [
                                'pregunta' => '¿Cuáles son los primeros 4 libros del Nuevo Testamento?',
                                'tipo' => 'multiple',
                                'opciones' => [
                                    ['opcion' => 'Mateo', 'correcta' => true],
                                    ['opcion' => 'Marcos', 'correcta' => true],
                                    ['opcion' => 'Lucas', 'correcta' => true],
                                    ['opcion' => 'Juan', 'correcta' => true],
                                    ['opcion' => 'Hechos', 'correcta' => false],
                                ]
                            ],
                            [
                                'pregunta' => 'Jesús nació en Nazaret.',
                                'tipo' => 'verdadero_falso',
                                'opciones' => [
                                    ['opcion' => 'Verdadero', 'correcta' => false],
                                    ['opcion' => 'Falso', 'correcta' => true],
                                ]
                            ],
                            [
                                'pregunta' => '¿En cuántos días creó Dios el mundo?',
                                'tipo' => 'unica',
                                'opciones' => [
                                    ['opcion' => '6', 'correcta' => true],
                                    ['opcion' => '7', 'correcta' => false],
                                    ['opcion' => '1', 'correcta' => false],
                                ]
                            ],
                            [
                                'pregunta' => '¿Cuáles de estos fueron discípulos de Jesús?',
                                'tipo' => 'multiple',
                                'opciones' => [
                                    ['opcion' => 'Pedro', 'correcta' => true],
                                    ['opcion' => 'Andrés', 'correcta' => true],
                                    ['opcion' => 'Pablo', 'correcta' => false],
                                    ['opcion' => 'Juan', 'correcta' => true],
                                ]
                            ],
                            [
                                'pregunta' => 'El versículo más corto de la Biblia es "Jesús lloró".',
                                'tipo' => 'verdadero_falso',
                                'opciones' => [
                                    ['opcion' => 'Verdadero', 'correcta' => true],
                                    ['opcion' => 'Falso', 'correcta' => false],
                                ]
                            ],
                            [
                                'pregunta' => '¿Quién derrotó a Goliat?',
                                'tipo' => 'unica',
                                'opciones' => [
                                    ['opcion' => 'David', 'correcta' => true],
                                    ['opcion' => 'Saúl', 'correcta' => false],
                                    ['opcion' => 'Salomón', 'correcta' => false],
                                ]
                            ],
                            [
                                'pregunta' => '¿Qué usó Dios para crear a Eva?',
                                'tipo' => 'unica',
                                'opciones' => [
                                    ['opcion' => 'La costilla de Adán', 'correcta' => true],
                                    ['opcion' => 'Polvo', 'correcta' => false],
                                    ['opcion' => 'Una piedra', 'correcta' => false],
                                ]
                            ],
                            [
                                'pregunta' => '¿Cuáles de estos son libros del Pentateuco?',
                                'tipo' => 'multiple',
                                'opciones' => [
                                    ['opcion' => 'Génesis', 'correcta' => true],
                                    ['opcion' => 'Éxodo', 'correcta' => true],
                                    ['opcion' => 'Salmos', 'correcta' => false],
                                    ['opcion' => 'Levítico', 'correcta' => true],
                                ]
                            ],
                            [
                                'pregunta' => 'Pablo era un apóstol.',
                                'tipo' => 'verdadero_falso',
                                'opciones' => [
                                    ['opcion' => 'Verdadero', 'correcta' => true],
                                    ['opcion' => 'Falso', 'correcta' => false],
                                ]
                            ],
                        ];

                        foreach ($preguntas as $pIndex => $pData) {
                            $nuevaPregunta = $evaluacion->preguntas()->create([
                                'pregunta' => $pData['pregunta'],
                                'tipo_respuesta' => $pData['tipo'],
                                'orden' => $pIndex + 1,
                            ]);

                            foreach ($pData['opciones'] as $oData) {
                                $nuevaPregunta->opciones()->create([
                                    'opcion' => $oData['opcion'],
                                    'es_correcta' => $oData['correcta'],
                                ]);
                            }
                        }

                        CursoItem::create([
                            'curso_modulo_id' => $modulo->id,
                            'curso_item_tipo_id' => $tipoQuiz->id,
                            'titulo' => 'Quiz de la Biblia',
                            'orden' => $modulo->items()->count() + 1,
                            'itemable_id' => $evaluacion->id,
                            'itemable_type' => CursoEvaluacion::class,
                        ]);
                    }
                }
            }

            // CREAR INSCRIPCIONES MASIVAS Y VARIADAS
            // Seleccionar usuarios al azar para inscribirlos
            $usuariosTotal = User::count();
            if ($usuariosTotal > 10) {
                $usuarios = User::inRandomOrder()->limit(rand(10, 20))->get();

                // Asegurar que tengan género y roles variados para el dashboard
                $rolesDisponibles = ['Invitado', 'Pastor', 'Líder', 'Estudiante'];
                // Nota: Asegúrate de que estos nombres de rol existan en tu PermissionSeeder

                foreach ($usuarios as $uIndex => $user) {
                    // Variamos género de forma aleatoria (0=M, 1=F)
                    $user->update(['genero' => rand(0, 1)]);

                    // Asignar un rol si no tiene uno activo (para que salga en el gráfico)
                    if ($user->roles()->count() == 0) {
                        $rolAsignar = $rolesDisponibles[$uIndex % count($rolesDisponibles)];
                        // Solo intentamos asignar si el rol existe en la DB
                        $roleModel = \Spatie\Permission\Models\Role::where('name', $rolAsignar)->first();
                        if ($roleModel) {
                            $user->assignRole($roleModel);
                            $user->roles()->updateExistingPivot(
                                $roleModel->id,
                                ['activo' => true, 'model_type' => User::class] // Corrigiendo error de model_type común
                            );
                        }
                    }

                    // Crear la inscripción
                    CursoUser::create([
                        'curso_id' => $curso->id,
                        'user_id' => $user->id,
                        'estado' => 'activo',
                        'fecha_inscripcion' => Carbon::now()->subDays(rand(0, 90)), // Variedad en los últimos 3 meses
                        'porcentaje_progreso' => ($uIndex === 0) ? 100 : rand(0, 95), // Garantizar al menos uno al 100%
                    ]);
                }
            }

            // Equipo del curso
            $curso->equipo()->firstOrCreate(['usuario_id' => 1, 'tipo_cargo_curso_id' => 1], ['activo' => true]);
        }
    }
}
