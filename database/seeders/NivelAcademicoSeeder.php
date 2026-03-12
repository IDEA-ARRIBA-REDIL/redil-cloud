<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\NivelAcademico;

class NivelAcademicoSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  
  public function run(): void
  {
    $niveles_academicos = '[
        {"id":"1","nombre":"Pre-escolar"},
        {"id":"2","nombre":"Enseñanza básica - Primaria"},
        {"id":"3","nombre":"Enseñanza media - Secundaria"},
        {"id":"4","nombre":"Bachillerato"},
        {"id":"5","nombre":"Técnico"},
        {"id":"6","nombre":"Tecnólogo"},
        {"id":"7","nombre":"Profesional universitario"},
        {"id":"8","nombre":"Licenciatura"},
        {"id":"9","nombre":"Especialización"},
        {"id":"10","nombre":"Maestria"},
        {"id":"11","nombre":"Doctorado"},
        {"id":"12","nombre":"Post-Doctorado"},
        {"id":"13","nombre":"Terciario"},
        {"id":"14","nombre":"Auxiliar"}
        ]';

    $items = json_decode($niveles_academicos);

    foreach ($items as $item) {
      NivelAcademico::firstOrCreate(['nombre' => $item->nombre]);
    }
  }
  
}
