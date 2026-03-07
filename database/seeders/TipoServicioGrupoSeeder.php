<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\TipoServicioGrupo;

class TipoServicioGrupoSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    TipoServicioGrupo::firstOrCreate([
      'nombre' => 'Anfitrion',
      'descripcion' => '',
    ]);

    TipoServicioGrupo::firstOrCreate([
      'nombre' => 'Tesorero',
      'descripcion' => '',
    ]);

    TipoServicioGrupo::firstOrCreate([
      'nombre' => 'Timoteo',
      'descripcion' => '',
    ]);
  }
}
