<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Illuminate\Support\Facades\DB;

use App\Models\Grupo;

class GrupoSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    Grupo::firstOrCreate([
      'id' => 1,
      'nombre' => 'Team Principal',
      'dado_baja' => 0,
      'tipo_grupo_id' => 1,
      'sede_id' => 2,
      'latitud' => '4.0903',
      'longitud' => '-76.2052',
      'dia' => 1,
      'hora' => '17:00:00',
      'fecha_apertura' => '2023-09-05'
    ]);

    Grupo::firstOrCreate([
      'id' => 2,
      'nombre' => 'Team A',
      'dado_baja' => 0,
      'tipo_grupo_id' => 1,
      'sede_id' => 2,
      'latitud' => '4.0962',
      'longitud' => '-76.1940',
      'dia' => 2,
      'hora' => '12:00:00',
      'fecha_apertura' => '2025-07-09'
    ]);

    Grupo::firstOrCreate([
      'id' => 3,
      'nombre' => 'Team B',
      'dado_baja' => 0,
      'tipo_grupo_id' => 1,
      'sede_id' => 2,
      'dia' => 2,
      'hora' => '09:00:00',
      'ultimo_reporte_grupo' => '2024-04-13',
      'fecha_apertura' => '2023-10-20'
    ]);

    Grupo::firstOrCreate([
      'id' => 4,
      'nombre' => 'Team A1',
      'dado_baja' => 0,
      'tipo_grupo_id' => 2,
      'sede_id' => 2,
      'usuario_creacion_id' => 1,
      'latitud' => '3.9008',
      'longitud' => '-76.2937',
      'dia' => 2,
      'dia_planeacion' => 2,
      'rhema' => 'este es el super rhema',
      'hora' => '10:00:00',
      'hora_planeacion' => '11:00:00',
      'ultimo_reporte_grupo' => '2024-11-13',
      'fecha_apertura' => '2023-06-14',
      'tipo_vivienda_id' => 1,
      'direccion' => 'calle falsa 123',
      'telefono' => '123456789',
      'fecha_apertura' => '2024-06-20'
    ]);

    Grupo::firstOrCreate([
      'id' => 5,
      'nombre' => 'Sin lideres',
      'dado_baja' => 0,
      'tipo_grupo_id' => 2,
      'sede_id' => 2,
      'usuario_creacion_id' => 1,
      'dia' => 4,
      'hora' => '12:00:00',
      'fecha_apertura' => '2024-06-22'
    ]);

    Grupo::firstOrCreate([
      'id' => 6,
      'nombre' => 'El inagregable',
      'dado_baja' => 0,
      'tipo_grupo_id' => 3,
      'sede_id' => 2,
      'usuario_creacion_id' => 1,
      'dia' => 1,
      'hora' => '18:00:00'
    ]);

    Grupo::firstOrCreate([
      'id' => 7,
      'nombre' => 'El ineliminable',
      'dado_baja' => 0,
      'tipo_grupo_id' => 4,
      'sede_id' => 2,
      'usuario_creacion_id' => 1,
      'dia' => 7,
      'hora' => '07:00:00'
    ]);

    Grupo::firstOrCreate([
      'id' => 8,
      'nombre' => 'El dado de baja',
      'dado_baja' => 1,
      'tipo_grupo_id' => 2,
      'sede_id' => 2,
      'usuario_creacion_id' => 1,
      'dia' => 7,
      'hora' => '07:00:00',
      'fecha_apertura' => '2025-06-09'
    ]);

    Grupo::firstOrCreate([
      'id' => 9,
      'nombre' => 'El nuevo',
      'dado_baja' => 0,
      'tipo_grupo_id' => 1,
      'sede_id' => 2,
      'usuario_creacion_id' => 1,
      'dia' => 7,
      'hora' => '07:00:00',
      'fecha_apertura' => '2024-06-14'
    ]);




    /*  ESTOS YA SON MANEJADOS POR OTROS SEEDERS O NO SON NECESARIOS CON UPDATEORINSERT */
    DB::table('encargados_grupo')->updateOrInsert(
      ['grupo_id' => 1, 'user_id' => 2]
    );

    DB::table('encargados_grupo')->updateOrInsert(
      ['grupo_id' => 2, 'user_id' => 3]
    );

    DB::table('encargados_grupo')->updateOrInsert(
      ['grupo_id' => 3, 'user_id' => 4]
    );

    DB::table('encargados_grupo')->updateOrInsert(
      ['grupo_id' => 4, 'user_id' => 6]
    );

    // integrantes_grupo
    DB::table('integrantes_grupo')->updateOrInsert(
      ['grupo_id' => 1, 'user_id' => 3]
    );

    DB::table('integrantes_grupo')->updateOrInsert(
      ['grupo_id' => 1, 'user_id' => 4]
    );

    DB::table('integrantes_grupo')->updateOrInsert(
      ['grupo_id' => 2, 'user_id' => 5]
    );

    DB::table('integrantes_grupo')->updateOrInsert(
      ['grupo_id' => 3, 'user_id' => 5]
    );

    DB::table('integrantes_grupo')->updateOrInsert(
      ['grupo_id' => 2, 'user_id' => 6]
    );

    DB::table('integrantes_grupo')->updateOrInsert(
      ['grupo_id' => 4, 'user_id' => 7]
    );

    DB::table('integrantes_grupo')->updateOrInsert(
      ['grupo_id' => 4, 'user_id' => 9]
    );

    DB::table('integrantes_grupo')->updateOrInsert(
      ['grupo_id' => 4, 'user_id' => 11]
    );
  }
}
