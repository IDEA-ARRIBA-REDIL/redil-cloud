<?php

namespace Database\Seeders;

use App\Models\TareaConsolidacion;
use Illuminate\Database\Seeder;

class TareaConsolidacionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tareas = [
            'Canales de captación de nuevos',
            'Llamado pastoral en cultos',
            'Bienvenida nuevos creyentes',
            'Seguimiento de cosecha',
            'Café con Jesús',
            'Visitas',
            'Proceso de mentoreo camino hacia la libertad',
            'Ceremonia de bautismo',
            'Inscripción academia de formación el camino',
        ];

        foreach ($tareas as $index => $tarea) {
            TareaConsolidacion::firstOrCreate(
                ['nombre' => $tarea],
                [
                    'descripcion' => '',
                    'orden'       => $index + 1,
                    'default'     => true,
                ]
            );
        }
    }
}
