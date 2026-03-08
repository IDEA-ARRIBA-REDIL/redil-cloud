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

    CampoInformeExcel::firstOrCreate(
      ['nombre_campo_bd'=> 'fecha_baja', 'selector_id' => 5, 'tabla' => 'grupos.'],
      [
      'id'=> $x ? $x->id+1 : 1,
      'nombre_campo_informe' => 'fecha_baja',
      'raw_sql' => 1,
      'eloquent_sql'=> 0,
      'orden' => 63
    ]);

    CampoInformeExcel::firstOrCreate(
      ['nombre_campo_bd'=> 'motivo_baja', 'selector_id' => 5, 'tabla' => 'grupos.'],
      [
      'id'=> $x ? $x->id+2 : 2,
      'nombre_campo_informe' => 'motivo_baja',
      'raw_sql' => 1,
      'eloquent_sql'=> 0,
      'orden' => 64
    ]);

    CampoInformeExcel::firstOrCreate(
      ['nombre_campo_bd'=> 'fecha_alta', 'selector_id' => 5, 'tabla' => 'grupos.'],
      [
      'id'=> $x ? $x->id+3 : 3,
      'nombre_campo_informe' => 'fecha_alta',
      'raw_sql' => 1,
      'eloquent_sql'=> 0,
      'orden' => 65
    ]);

    CampoInformeExcel::firstOrCreate(
      ['nombre_campo_bd'=> 'motivo_alta', 'selector_id' => 5, 'tabla' => 'grupos.'],
      [
      'id'=> $x ? $x->id+4 : 4,
      'nombre_campo_informe' => 'motivo_alta',
      'raw_sql' => 1,
      'eloquent_sql'=> 0,
      'orden' => 66
    ]);

    // Fix postgres sequence after manual inserts
    DB::unprepared("SELECT setval('campos_informe_excel_id_seq', (SELECT MAX(id) FROM campos_informe_excel));");
  }
}
