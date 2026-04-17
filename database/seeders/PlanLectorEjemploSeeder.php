<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\PlanLector;
use App\Models\PlanLectorDia;
use App\Models\PlanLectorContenido;
use App\Models\PlanLectorCategoria;
use App\Models\PlanLectorTipoContenido;
use App\Models\User;

class PlanLectorEjemploSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Traer un administrador por defecto para asignarlo como autor
        $autor = User::first(); 
        $autorId = $autor ? $autor->id : 1;

        // Categorías existentes para asignar
        $categorias = PlanLectorCategoria::take(2)->get();
        $cat1 = $categorias->first();
        $cat2 = $categorias->last();

        // ---------------------------------------------------------------------
        // PLAN 1: Un Corazón Dispuesto (3 Días) - 2 Categorías
        // ---------------------------------------------------------------------
        $plan1 = PlanLector::create([
            'titulo' => 'Un Corazón Dispuesto - 3 Días',
            'slug' => Str::slug('Un Corazón Dispuesto - 3 Días'),
            'descripcion' => 'Este es un devocional de ejemplo diseñado para probar el sistema de lectura diaria. A través de este viaje, aprenderemos la importancia de preparar nuestro corazón para escuchar y obedecer la voz de Dios.',
            'autor_id' => $autorId,
            'calificacion' => 0.00,
            'imagen_url' => null,
            'estado' => true,
            'visible_todos' => true,
            'genero' => 3
        ]);

        // Asignar 2 categorías si existen
        if ($cat1 && $cat2) {
            $plan1->categorias()->attach([$cat1->id, $cat2->id]);
        } elseif ($cat1) {
            $plan1->categorias()->attach([$cat1->id]);
        }

        // Obtener los tipos de contenido por slug para evitar IDs hardcodeados
        $tipoReflexion = PlanLectorTipoContenido::where('slug', 'reflexion')->first();
        $tipoPasaje = PlanLectorTipoContenido::where('slug', 'pasaje')->first();
        $tipoVideo = PlanLectorTipoContenido::where('slug', 'video')->first();

        $tipoContenidoTextoId = $tipoReflexion->id ?? 1;

        // DÍA 1
        $dia1 = PlanLectorDia::create(['plan_lector_id' => $plan1->id, 'dia' => 1, 'titulo' => 'Preparando el camino']);
        PlanLectorContenido::create([
            'plan_lector_dia_id' => $dia1->id,
            'plan_lector_tipo_contenido_id' => $tipoContenidoTextoId,
            'orden' => 1,
            'contenido' => '<p>Bienvenido al primer día de nuestro devocional.</p><p>A menudo, estamos tan ocupados con el ajetreo diario que nuestro corazón se vuelve terreno duro. En este día, te invitamos a pausar unos minutos y respirar la paz de Dios.</p>'
        ]);

        // DÍA 2
        $dia2 = PlanLectorDia::create(['plan_lector_id' => $plan1->id, 'dia' => 2, 'titulo' => 'Escuchando el susurro']);
        PlanLectorContenido::create([
            'plan_lector_dia_id' => $dia2->id,
            'plan_lector_tipo_contenido_id' => $tipoContenidoTextoId,
            'orden' => 1,
            'contenido' => '<p>Ayer vimos la importancia de preparar la tierra de nuestra alma.</p><p>Hoy el gran desafío es apagar el ruido. Haz silencio hoy para escuchar Sus palabras de afirmación y amor sobre tu vida.</p>'
        ]);

        // DÍA 3
        $dia3 = PlanLectorDia::create(['plan_lector_id' => $plan1->id, 'dia' => 3, 'titulo' => 'La respuesta en acción']);
        PlanLectorContenido::create([
            'plan_lector_dia_id' => $dia3->id,
            'plan_lector_tipo_contenido_id' => $tipoContenidoTextoId,
            'orden' => 1,
            'contenido' => '<p>Has llegado al último día de prueba de este devocional.</p><p>La lectura de la Palabra y la oración son esenciales, pero la meta de un corazón receptivo no es tener conocimiento bíblico escondido, sino aplicarlo a nuestra cotidianidad.</p>'
        ]);

        // ---------------------------------------------------------------------
        // PLAN 2: Caminando en Sabiduría (1 Día) - 1 Categoría
        // ---------------------------------------------------------------------
        $plan2 = PlanLector::create([
            'titulo' => 'Caminando en Sabiduría - Devocional Express',
            'slug' => Str::slug('Caminando en Sabiduría - Devocional Express'),
            'descripcion' => 'Un devocional de un solo día diseñado enfocado en encontrar sabiduría en las acciones cotidianas.',
            'autor_id' => $autorId,
            'calificacion' => 0.00,
            'imagen_url' => null,
            'estado' => true,
            'visible_todos' => true,
            'genero' => 3
        ]);

        // Asignar 1 categoría si existe
        if ($cat1) {
            $plan2->categorias()->attach([$cat1->id]);
        }

        // DÍA 1 (Único día)
        $diaPlan2 = PlanLectorDia::create(['plan_lector_id' => $plan2->id, 'dia' => 1, 'titulo' => 'Sabiduría diaria']);
        PlanLectorContenido::create([
            'plan_lector_dia_id' => $diaPlan2->id,
            'plan_lector_tipo_contenido_id' => $tipoContenidoTextoId,
            'orden' => 1,
            'contenido' => '<p>La sabiduría no consiste solo en saber mucho, sino en saber cómo actuar. Hoy pide a Dios que guíe cada uno de tus pasos.</p>'
        ]);

        // ---------------------------------------------------------------------
        // PLAN 3: Fortaleza en el Desierto (10 Días) - Para pruebas de scroll
        // ---------------------------------------------------------------------
        $plan3 = PlanLector::create([
            'titulo' => 'Fortaleza en el Desierto - 10 Días',
            'slug' => Str::slug('Fortaleza en el Desierto - 10 Días'),
            'descripcion' => 'Un plan extendido diseñado para probar la navegación horizontal y la persistencia de lectura a lo largo de 10 jornadas consecutivas.',
            'autor_id' => $autorId,
            'calificacion' => 0.00,
            'imagen_url' => null,
            'estado' => true,
            'visible_todos' => true,
            'genero' => 3
        ]);

        if ($cat2) {
            $plan3->categorias()->attach([$cat2->id]);
        }

        for ($i = 1; $i <= 20; $i++) {
            $diaTemp = PlanLectorDia::create([
                'plan_lector_id' => $plan3->id, 
                'dia' => $i, 
                'titulo' => "Día $i: Perseverancia y Fe"
            ]);

            PlanLectorContenido::create([
                'plan_lector_dia_id' => $diaTemp->id,
                'plan_lector_tipo_contenido_id' => $tipoContenidoTextoId,
                'orden' => 1,
                'contenido' => "<p>Estás en el día <strong>$i</strong> de tu viaje por el desierto.</p><p>Recuerda que cada paso cuenta y que Dios está fortaleciendo tu espíritu en este proceso de disciplina diaria.</p>"
            ]);
        }

        // ---------------------------------------------------------------------
        // 20 PLANES ADICIONALES: Para pruebas de paginación y volumen
        // ---------------------------------------------------------------------
        for ($j = 1; $j <= 20; $j++) {
            $tituloPlan = "Plan de Prueba de Volumen #" . str_pad($j, 2, '0', STR_PAD_LEFT);
            $planPrueba = PlanLector::create([
                'titulo' => $tituloPlan,
                'slug' => Str::slug($tituloPlan . '-' . uniqid()),
                'descripcion' => "Este es un plan generado automáticamente para pruebas de volumen y paginación. Número de prueba: $j.",
                'autor_id' => $autorId,
                'calificacion' => rand(30, 50) / 10, // Calificación aleatoria entre 3.0 y 5.0
                'imagen_url' => null,
                'estado' => ($j % 5 !== 0), // Algunos desactivados
                'visible_todos' => true,
                'genero' => 3
            ]);

            // Asignar una categoría aleatoria
            if ($cat1 || $cat2) {
                $catId = ($j % 2 == 0 && $cat2) ? $cat2->id : ($cat1 ? $cat1->id : $cat2->id);
                $planPrueba->categorias()->attach([$catId]);
            }

            // Crear un día único para que sea funcional
            $diaPrueba = PlanLectorDia::create([
                'plan_lector_id' => $planPrueba->id, 
                'dia' => 1, 
                'titulo' => 'Introducción a la Prueba'
            ]);

            PlanLectorContenido::create([
                'plan_lector_dia_id' => $diaPrueba->id,
                'plan_lector_tipo_contenido_id' => $tipoContenidoTextoId,
                'orden' => 1,
                'contenido' => "<p>Contenido de prueba para el plan $j.</p>"
            ]);
        }

        $this->command->info("¡Seeders de Planes Lectores (y 20 extras) creados satisfactoriamente!");
    }
}
