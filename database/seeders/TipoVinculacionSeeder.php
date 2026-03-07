<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\TipoVinculacion;

class TipoVinculacionSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    TipoVinculacion::firstOrCreate([
      'nombre' => 'Internet',
    ]);

    TipoVinculacion::firstOrCreate(
      ['nombre' => 'Grupo familiar'],
      ['por_grupo' => true]
    );

    TipoVinculacion::firstOrCreate([
      'nombre' => 'Culto',
    ]);
    
    TipoVinculacion::firstOrCreate([
      'nombre' => 'Emisora u otro',
    ]);

    TipoVinculacion::firstOrCreate([
      'nombre' => 'Campaña conéctate',
    ]);
  }
}
