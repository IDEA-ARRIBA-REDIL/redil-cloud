<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Illuminate\Support\Facades\DB;

class ServicioServidorGrupoSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    DB::table('servicios_servidores_grupo')->updateOrInsert(
      ['servidores_grupo_id' => 1, 'tipo_servicio_grupos_id' => 1]
    );

    /*DB::table('servicios_servidores_grupo')->insert([
      'servidores_grupo_id' => 2,
      'tipo_servicio_grupos_id' => 1,
    ]);*/
  }
}
