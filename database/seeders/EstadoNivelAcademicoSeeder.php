<?php

namespace Database\Seeders;

use App\Models\EstadoNivelAcademico;
use Illuminate\Database\Seeder;

class EstadoNivelAcademicoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $estado_niveles_academicos = '[
      {"id":"1","nombre":"En curso"},
      {"id":"2","nombre":"No concluido"},
      {"id":"3","nombre":"Finalizado"}
      ]';

        $items = json_decode($estado_niveles_academicos);

        foreach ($items as $item) {
            EstadoNivelAcademico::firstOrCreate([
                'nombre' => $item->nombre,
            ]);
        }
    }
}
