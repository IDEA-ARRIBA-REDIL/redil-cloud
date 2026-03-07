<?php

namespace Database\Seeders;

use App\Models\CampoInformeExcel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CampoInformeExcelSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $path = base_path('storage/app/archivos_desarrollador/campos_informe_excel.sql');
    DB::unprepared(file_get_contents($path));


    $x = CampoInformeExcel::select('id')->orderBy('id', 'desc')->first();


    CampoInformeExcel::firstOrCreate([
      'id'=> $x->id+1,
      'nombre_campo_bd'=> 'fecha_baja',
      'nombre_campo_informe' => 'fecha_baja',
      'selector_id' => 5,
      'tabla' => 'grupos.',
      'raw_sql' => 1,
      'eloquent_sql'=> 0,
      'orden' => 63
    ]);

    CampoInformeExcel::firstOrCreate([
      'id'=> $x->id+2,
      'nombre_campo_bd'=> 'motivo_baja',
      'nombre_campo_informe' => 'motivo_baja',
      'selector_id' => 5,
      'tabla' => 'grupos.',
      'raw_sql' => 1,
      'eloquent_sql'=> 0,
      'orden' => 64
    ]);

    CampoInformeExcel::firstOrCreate([
      'id'=> $x->id+3,
      'nombre_campo_bd'=> 'fecha_alta',
      'nombre_campo_informe' => 'fecha_alta',
      'selector_id' => 5,
      'tabla' => 'grupos.',
      'raw_sql' => 1,
      'eloquent_sql'=> 0,
      'orden' => 65
    ]);

    CampoInformeExcel::firstOrCreate([
      'id'=> $x->id+4,
      'nombre_campo_bd'=> 'motivo_alta',
      'nombre_campo_informe' => 'motivo_alta',
      'selector_id' => 5,
      'tabla' => 'grupos.',
      'raw_sql' => 1,
      'eloquent_sql'=> 0,
      'orden' => 66
    ]);
  }
}
