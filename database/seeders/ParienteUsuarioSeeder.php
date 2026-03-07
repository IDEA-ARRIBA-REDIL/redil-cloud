<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ParienteUsuarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

      // Papa =  6
      // Hija = 11

      DB::table('parientes_usuarios')->updateOrInsert(
        ['user_id'=> 11, 'pariente_user_id'=> 6],
        [
            'es_el_responsable' => false,
            'tipo_pariente_id'=>2,
        ]
      );

      DB::table('parientes_usuarios')->updateOrInsert(
        ['user_id'=> 6, 'pariente_user_id'=> 11],
        [
            'es_el_responsable' => true,
            'acepto_terminos_condiciones' => true,
            'tipo_pariente_id'=> 1,
        ]
      );

    }
}
