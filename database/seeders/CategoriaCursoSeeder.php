<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriaCursoSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = [
            [
                'nombre' => 'Biblia',
                'descripcion' => 'Estudios profundos de libros bíblicos.',
                'color' => '#4CAF50', // Green
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Vida Espiritual',
                'descripcion' => 'Crecimiento personal y devoción.',
                'color' => '#2196F3', // Blue
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Familia',
                'descripcion' => 'Cursos para matrimonios y crianza.',
                'color' => '#E91E63', // Pink
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Finanzas',
                'descripcion' => 'Mayordomía y gestión de recursos.',
                'color' => '#FF9800', // Orange
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Ministerio',
                'descripcion' => 'Capacitación técnica para servir.',
                'color' => '#9C27B0', // Purple
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('categorias_cursos')->insert($categorias);
    }
}
