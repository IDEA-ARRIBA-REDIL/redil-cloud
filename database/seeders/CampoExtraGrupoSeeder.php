<?php

namespace Database\Seeders;

use App\Models\CampoExtraGrupo;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Illuminate\Support\Facades\DB;

class CampoExtraGrupoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
      // Id: 1
      CampoExtraGrupo::firstOrCreate([
        'nombre' => 'Ministerio asociado',
        'tipo_de_campo' => 3,
        'required' => true,
        'class_col' => 'col-12 col-md-6',
        'class_id' => 'ministerio_asociado',
        'opciones_select' =>
          '[{"id": "1","nombre":"Solo hombre","visible":"1","value":"1"},{"id": "2","nombre":"Solo mujeres","visible":"1","value":"2"},{"id": "3","nombre":"Jovenes","visible":"1","value":"3"}]',
        'visible' => true,
      ]);

      // Id: 2
      CampoExtraGrupo::firstOrCreate([
        'nombre' => 'Número de servidores',
        'tipo_de_campo' => 1,
        'required' => false,
        'class_col' => 'col-12 col-md-6',
        'class_id' => 'numero_servidores',
        'opciones_select' => '',
        'visible' => true,
      ]);


      // relación campos extras con lo grupos
      DB::table('grupo_opcion_campo_extra')->updateOrInsert(
        ['campo_extra_grupo_id' => 1, 'grupo_id' => 4],
        ['valor' => 3]
      );

      DB::table('grupo_opcion_campo_extra')->updateOrInsert(
        ['campo_extra_grupo_id' => 2, 'grupo_id' => 4],
        ['valor' => 10]
      );



    }
}
