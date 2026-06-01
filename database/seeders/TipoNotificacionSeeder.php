<?php

namespace Database\Seeders;

use App\Models\TipoNotificacion;
use Illuminate\Database\Seeder;

class TipoNotificacionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $notificaciones = [
            // MODULO: GRUPOS
            [
                'slug' => 'grupo_creado',
                'modulo' => 'Grupos',
                'titulo' => 'Nuevo grupo registrado',
                'descripcion' => 'Notifica cuando se crea un nuevo grupo en el sistema.',
                'alcance' => TipoNotificacion::ALCANCE_ESCALA_MINISTERIAL,
                'activo' => true,
            ],
            [
                'slug' => 'grupo_reporte_creado',
                'modulo' => 'Grupos',
                'titulo' => 'Reporte de Grupo Enviado',
                'descripcion' => 'Notifica cuando un líder envía el reporte semanal de su grupo.',
                'alcance' => TipoNotificacion::ALCANCE_ESCALA_MINISTERIAL,
                'activo' => true,
            ],

            // MODULO: ASISTENTES
            [
                'slug' => 'crear_persona',
                'modulo' => 'Asistentes',
                'titulo' => 'Nueva Persona Registrada',
                'descripcion' => 'Notifica cuando un líder registra a una nueva persona en su red o sistema.',
                'alcance' => TipoNotificacion::ALCANCE_ESCALA_MINISTERIAL,
                'activo' => true,
            ],

            // MODULO: ACTIVIDADES / EVENTOS
            [
                'slug' => 'actividad_inscripcion_exitosa',
                'modulo' => 'Actividades',
                'titulo' => 'Inscripción a Actividad',
                'descripcion' => 'Confirmación de inscripción exitosa a un evento.',
                'alcance' => TipoNotificacion::ALCANCE_INDIVIDUAL,
                'activo' => true,
            ],
            [
                'slug' => 'actividad_nueva_inscripcion_red',
                'modulo' => 'Actividades',
                'titulo' => 'Nueva Inscripción en su Red',
                'descripcion' => 'Notifica a los líderes cuando alguien de su red se inscribe a un evento.',
                'alcance' => TipoNotificacion::ALCANCE_MINISTERIO_DIRECTO,
                'activo' => true,
            ],

            // MODULO: ESCUELAS
            [
                'slug' => 'escuela_matricula_confirmada',
                'modulo' => 'Escuelas',
                'titulo' => 'Matrícula Confirmada',
                'descripcion' => 'Notifica al estudiante que su proceso de matrícula fue exitoso.',
                'alcance' => TipoNotificacion::ALCANCE_INDIVIDUAL,
                'activo' => true,
            ],

            // MODULO: GLOBAL
            [
                'slug' => 'notificacion_administrativa_global',
                'modulo' => 'General',
                'titulo' => 'Aviso General de la Iglesia',
                'descripcion' => 'Notificaciones enviadas por la administración para todos los usuarios.',
                'alcance' => TipoNotificacion::ALCANCE_GLOBAL,
                'activo' => true,
            ],
        ];

        foreach ($notificaciones as $notificacion) {
            TipoNotificacion::firstOrCreate(
                ['slug' => $notificacion['slug']],
                $notificacion
            );
        }
    }
}
