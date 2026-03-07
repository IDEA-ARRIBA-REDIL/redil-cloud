<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\TipoSede;

class TipoSedeSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    TipoSede::firstOrCreate([
      'nombre' => 'Sede Principal',
      'descripcion' => '',
    ]);

    TipoSede::firstOrCreate([
      'nombre' => 'Sede',
      'descripcion' => '',
    ]);

    TipoSede::firstOrCreate([
      'nombre' => 'Subsede',
      'descripcion' => '',
    ]);

    TipoSede::firstOrCreate([
      'nombre' => 'Macro Grupo',
      'descripcion' => '',
    ]);
  }
}
