<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\EstadoNivelAcademico;

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
