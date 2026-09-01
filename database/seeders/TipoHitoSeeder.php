<?php

namespace Database\Seeders;

use App\Models\TipoHito;
use Illuminate\Database\Seeder;

class TipoHitoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tipos = [
            [
                'nombre' => 'General / Conmemorativo',
                'slug' => 'general',
                'descripcion' => 'Hito conmemorativo o evento abierto visible para la congregación según segmentación demográfica/sede.',
                'icono' => 'ti ti-calendar-event',
                'color' => '#64748b',
                'requiere_trigger' => false,
                'requiere_actividad' => false,
                'permite_fotos_usuario' => true,
                'permite_likes' => true,
                'evaluacion_dinamica' => true,
                'configuracion' => null,
                'activo' => true,
            ],
            [
                'nombre' => 'Automático / Logro Espiritual',
                'slug' => 'automatico',
                'descripcion' => 'Hito otorgado automáticamente al cumplirse condiciones en Pasos de Crecimiento, Consolidación, Escuelas o Grupos.',
                'icono' => 'ti ti-award',
                'color' => '#7c5cfc',
                'requiere_trigger' => true,
                'requiere_actividad' => false,
                'permite_fotos_usuario' => true,
                'permite_likes' => true,
                'evaluacion_dinamica' => true,
                'configuracion' => null,
                'activo' => true,
            ],
            [
                'nombre' => 'Actividad',
                'slug' => 'actividad',
                'descripcion' => 'Hito vinculado a un evento del módulo Actividades, con opción de exigir confirmación de asistencia.',
                'icono' => 'ti ti-ticket',
                'color' => '#0ea5e9',
                'requiere_trigger' => false,
                'requiere_actividad' => true,
                'permite_fotos_usuario' => true,
                'permite_likes' => true,
                'evaluacion_dinamica' => true,
                'configuracion' => null,
                'activo' => true,
            ],
            [
                'nombre' => 'Asignación Manual / Reconocimiento',
                'slug' => 'manual',
                'descripcion' => 'Reconocimiento o hito individual asignado directamente por administradores/pastores a usuarios específicos.',
                'icono' => 'ti ti-star',
                'color' => '#f59e0b',
                'requiere_trigger' => false,
                'requiere_actividad' => false,
                'permite_fotos_usuario' => true,
                'permite_likes' => true,
                'evaluacion_dinamica' => false,
                'configuracion' => null,
                'activo' => true,
            ],
        ];

        foreach ($tipos as $tipo) {
            TipoHito::updateOrCreate(
                ['slug' => $tipo['slug']],
                $tipo
            );
        }

        $this->command->info('Tipos de Hito sembrados correctamente ('.count($tipos).' tipos).');
    }
}
