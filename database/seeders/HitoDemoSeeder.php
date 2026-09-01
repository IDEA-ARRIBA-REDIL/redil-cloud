<?php

namespace Database\Seeders;

use App\Models\Hito;
use App\Models\TipoHito;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class HitoDemoSeeder extends Seeder
{
    /**
     * Crea 20 hitos de prueba desde el 2020 hasta el 2026 sin restricciones
     * para probar inmediatamente la experiencia del Muro / Túnel del Tiempo 3D.
     */
    public function run(): void
    {
        // Obtener usuario administrador por defecto
        $user = User::first();
        $userId = $user ? $user->id : 1;

        // Asegurar que existan tipos de hito
        $tipoGeneral = TipoHito::where('slug', 'general')->first() ?? TipoHito::first();
        $tipoAutomatico = TipoHito::where('slug', 'automatico')->first() ?? $tipoGeneral;
        $tipoActividad = TipoHito::where('slug', 'actividad')->first() ?? $tipoGeneral;
        $tipoManual = TipoHito::where('slug', 'manual')->first() ?? $tipoGeneral;

        $hitosData = [
            [
                'titulo' => 'Hoy: seguimos caminando en fe',
                'tipo_id' => $tipoGeneral->id,
                'fecha' => '2026-06-15',
                'descripcion' => 'Una nueva temporada de fe, esperanza y visión para toda nuestra comunidad.',
                'mensaje_usuario' => '¡Felicidades por ser parte de este tiempo especial! Dios tiene grandes planes para tu vida.',
            ],
            [
                'titulo' => 'Retiro Espiritual Jóvenes 2026: Renuevo',
                'tipo_id' => $tipoActividad->id,
                'fecha' => '2026-03-20',
                'descripcion' => 'Tres días de búsqueda profunda, adoración y transformación en la presencia de Dios.',
                'mensaje_usuario' => 'Un encuentro que marcó un antes y un después en tu caminar espiritual.',
            ],
            [
                'titulo' => 'Graduación Escuela de Líderes 2025',
                'tipo_id' => $tipoAutomatico->id,
                'fecha' => '2025-11-28',
                'descripcion' => 'Culminación exitosa del programa de discipulado y liderazgo cristiano nivel avanzado.',
                'mensaje_usuario' => 'Has demostrado compromiso y perseverancia. Que tu fruto permanezca.',
            ],
            [
                'titulo' => 'Congreso Nacional de Familias 2025',
                'tipo_id' => $tipoGeneral->id,
                'fecha' => '2025-08-14',
                'descripcion' => 'Fortaleciendo los hogares bajo principios bíblicos y amor incondicional.',
                'mensaje_usuario' => 'Tu hogar es un testimonio del poder restaurador de Dios.',
            ],
            [
                'titulo' => 'Bautismos en Agua: Nueva Vida 2025',
                'tipo_id' => $tipoAutomatico->id,
                'fecha' => '2025-05-10',
                'descripcion' => 'Paso público de fe y consagración entregando la vida a Jesucristo.',
                'mensaje_usuario' => 'De modo que si alguno está en Cristo, nueva criatura es: las cosas viejas pasaron.',
            ],
            [
                'titulo' => 'Apertura de Nuevo Grupo de Conexión',
                'tipo_id' => $tipoManual->id,
                'fecha' => '2025-02-18',
                'descripcion' => 'Inicio de un nuevo grupo celular para compartir la palabra y edificar a otros.',
                'mensaje_usuario' => 'Gracias por abrir tu corazón y servir a tu prójimo con amor.',
            ],
            [
                'titulo' => 'Noche de Milagros y Alabanza 2024',
                'tipo_id' => $tipoGeneral->id,
                'fecha' => '2024-12-05',
                'descripcion' => 'Tiempo glorioso de sanidad, adoración continua e intercesión profética.',
                'mensaje_usuario' => 'Dios escuchó tu oración y renueva tus fuerzas día con día.',
            ],
            [
                'titulo' => 'Culminación Nivel Fundamentos de la Fe',
                'tipo_id' => $tipoAutomatico->id,
                'fecha' => '2024-09-12',
                'descripcion' => 'Aprobación de la primera etapa del proceso de crecimiento y formación bíblica.',
                'mensaje_usuario' => 'Pusiste un fundamento sólido sobre la Roca que es Cristo.',
            ],
            [
                'titulo' => 'Jornada Misionera y Solidaria 2024',
                'tipo_id' => $tipoActividad->id,
                'fecha' => '2024-06-22',
                'descripcion' => 'Llevando alimento, bendición y el mensaje de salvación a comunidades vulnerables.',
                'mensaje_usuario' => 'Tus manos y tu servicio reflejaron el amor de Jesús.',
            ],
            [
                'titulo' => 'Aniversario Congregacional: 10 Años',
                'tipo_id' => $tipoGeneral->id,
                'fecha' => '2024-03-15',
                'descripcion' => 'Celebrando una década de fidelidad divina, milagros y expansión.',
                'mensaje_usuario' => 'Hasta aquí nos ayudó el Señor. Eres parte fundamental de esta historia.',
            ],
            [
                'titulo' => 'Inicio en el Ministerio de Servicio',
                'tipo_id' => $tipoManual->id,
                'fecha' => '2023-11-04',
                'descripcion' => 'Integración formal a los equipos de bienvenida, logística y alabanza.',
                'mensaje_usuario' => 'El que sirve con alegría glorifica al Padre en los cielos.',
            ],
            [
                'titulo' => 'Campamento de Parejas: Unidos por Siempre',
                'tipo_id' => $tipoActividad->id,
                'fecha' => '2023-08-19',
                'descripcion' => 'Renovación de votos matrimoniales y consolidación del amor en Dios.',
                'mensaje_usuario' => 'Cordón de tres dobleces no se rompe pronto.',
            ],
            [
                'titulo' => 'Encuentro de Transformación Espiritual 2023',
                'tipo_id' => $tipoGeneral->id,
                'fecha' => '2023-04-29',
                'descripcion' => 'Fin de semana de liberación, sanidad interior y llenura del Espíritu Santo.',
                'mensaje_usuario' => 'Fuiste libre para adorar y vivir en plenitud.',
            ],
            [
                'titulo' => 'Finalización del Plan Lector Bíblico 365',
                'tipo_id' => $tipoAutomatico->id,
                'fecha' => '2022-12-31',
                'descripcion' => 'Lectura y meditación completa de las Sagradas Escrituras durante todo el año.',
                'mensaje_usuario' => 'Lámpara es a tus pies Su palabra, y lumbrera a tu camino.',
            ],
            [
                'titulo' => 'Conferencia de Mujeres: Valientes y Fuertes',
                'tipo_id' => $tipoActividad->id,
                'fecha' => '2022-09-08',
                'descripcion' => 'Un tiempo de empoderamiento, sanidad del alma y despertar de propósitos.',
                'mensaje_usuario' => 'Mujer virtuosa, tu valor sobrepasa largamente al de las piedras preciosas.',
            ],
            [
                'titulo' => 'Paso de Crecimiento: Integración a la Familia',
                'tipo_id' => $tipoAutomatico->id,
                'fecha' => '2022-05-14',
                'descripcion' => 'Reconocimiento oficial como miembro activo de la congregación local.',
                'mensaje_usuario' => '¡Bienvenido a casa! Juntos crecemos y avanzamos.',
            ],
            [
                'titulo' => 'Vigilia de Oración y Avivamiento 2021',
                'tipo_id' => $tipoGeneral->id,
                'fecha' => '2021-10-22',
                'descripcion' => 'Clamor unánime por las familias, las naciones y el mover del Espíritu Santo.',
                'mensaje_usuario' => 'La oración eficaz del justo puede mucho.',
            ],
            [
                'titulo' => 'Primera Clase Escuela Bíblica Discipular',
                'tipo_id' => $tipoAutomatico->id,
                'fecha' => '2021-06-05',
                'descripcion' => 'El inicio del camino en el conocimiento profundo de la doctrina y la verdad.',
                'mensaje_usuario' => 'Conoceréis la verdad, y la verdad os hará libres.',
            ],
            [
                'titulo' => 'Mi Primer Día en la Casa de Dios',
                'tipo_id' => $tipoGeneral->id,
                'fecha' => '2021-01-17',
                'descripcion' => 'El día en que cruzaste las puertas y encontraste una familia espiritual.',
                'mensaje_usuario' => 'Yo me alegré con los que me decían: A la casa de Jehová iremos.',
            ],
            [
                'titulo' => 'La Decisión que Cambió mi Vida',
                'tipo_id' => $tipoManual->id,
                'fecha' => '2020-08-23',
                'descripcion' => 'Aceptar a Jesucristo como Señor y Salvador personal en el corazón.',
                'mensaje_usuario' => 'El día más importante de tu historia: tu nombre fue escrito en el Libro de la Vida.',
            ],
        ];

        foreach ($hitosData as $data) {
            Hito::updateOrCreate(
                [
                    'titulo' => $data['titulo'],
                ],
                [
                    'tipo_hito_id' => $data['tipo_id'],
                    'user_id' => $userId,
                    'descripcion' => $data['descripcion'],
                    'mensaje_usuario' => $data['mensaje_usuario'],
                    'fecha_evento' => Carbon::parse($data['fecha']),
                    'activo' => true,
                    'permite_fotos_usuario' => true,
                    'max_fotos_usuario' => 4,
                    'max_peso_kb' => 5120,
                    'requiere_sesion' => false,
                    'requiere_asistencia' => false,
                ]
            );
        }
    }
}
