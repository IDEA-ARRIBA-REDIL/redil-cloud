<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TipoCargoCurso;

class TipoCargoCursoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cargos = [
            [
                'nombre' => 'Creador',
                'puede_responder_preguntas' => false,
            ],
            [
                'nombre' => 'Asesor',
                'puede_responder_preguntas' => true,
            ],
        ];

        foreach ($cargos as $cargo) {
            TipoCargoCurso::firstOrCreate(
                ['nombre' => $cargo['nombre']], // Busca por nombre para no duplicarlos
                $cargo
            );
        }
    }
}
