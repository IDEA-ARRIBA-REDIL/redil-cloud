<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CursoItemTipo;

class CursoItemTipoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tipos = [
            [
                'nombre' => 'Lección',
                'codigo' => 'leccion',
                'categoria' => 'leccion',
                'icono' => 'fas fa-book', 
            ],
            [
                'nombre' => 'Video',
                'codigo' => 'video',
                'categoria' => 'leccion',
                'icono' => 'fas fa-play-circle', 
            ],
            [
                'nombre' => 'Lectura',
                'codigo' => 'lectura',
                'categoria' => 'leccion',
                'icono' => 'fas fa-align-left',
            ],
             [
                'nombre' => 'Recurso',
                'codigo' => 'recurso',
                'categoria' => 'leccion',
                'icono' => 'fas fa-file-download',
            ],
            [
                'nombre' => 'Iframe',
                'codigo' => 'iframe',
                'categoria' => 'leccion',
                'icono' => 'fas fa-code',
            ],
            [
                'nombre' => 'Quiz',
                'codigo' => 'quiz',
                'categoria' => 'evaluacion',
                'icono' => 'fas fa-question-circle',
            ],
            [
                'nombre' => 'Evaluación Final',
                'codigo' => 'evaluacion_final',
                'categoria' => 'evaluacion',
                'icono' => 'fas fa-clipboard-check',
            ],
        ];

        foreach ($tipos as $tipo) {
            \App\Models\CursoItemTipo::updateOrCreate(
                ['codigo' => $tipo['codigo']],
                $tipo
            );
        }
    }
}
