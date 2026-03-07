<?php

namespace Database\Seeders;

use App\Models\TipoAsignacion;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TipoAsignacionSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    TipoAsignacion::firstOrCreate([
      'nombre' => 'No diligenciado',
      'default' => TRUE,
      'para_asignar_lideres' => FALSE,
      'para_asignar_asistentes' => FALSE,
      'para_desvincular_asistentes' => FALSE,
      'para_desvincular_lideres' => FALSE,
    ]);

    TipoAsignacion::firstOrCreate([
      'nombre' => 'Otro',
      'default' => FALSE,
      'para_asignar_lideres' => TRUE,
      'para_asignar_asistentes' => TRUE,
      'para_desvincular_asistentes' => TRUE,
      'para_desvincular_lideres' => TRUE,
    ]);

    TipoAsignacion::firstOrCreate([
      'nombre' => 'Equivocación',
      'default' => FALSE,
      'para_asignar_lideres' => TRUE,
      'para_asignar_asistentes' => TRUE,
      'para_desvincular_asistentes' => TRUE,
      'para_desvincular_lideres' => TRUE,
    ]);

    TipoAsignacion::firstOrCreate([
      'nombre' => 'Nuevo asistente',
      'default' => FALSE,
      'para_asignar_lideres' => FALSE,
      'para_asignar_asistentes' => TRUE,
      'para_desvincular_asistentes' => FALSE,
      'para_desvincular_lideres' => FALSE,
    ]);

    TipoAsignacion::firstOrCreate([
      'nombre' => 'No desea volver',
      'default' => FALSE,
      'para_asignar_lideres' => FALSE,
      'para_asignar_asistentes' => FALSE,
      'para_desvincular_asistentes' => TRUE,
      'para_desvincular_lideres' => FALSE,
    ]);

    TipoAsignacion::firstOrCreate([
      'nombre' => 'Translado',
      'default' => FALSE,
      'para_asignar_lideres' => TRUE,
      'para_asignar_asistentes' => TRUE,
      'para_desvincular_asistentes' => FALSE,
      'para_desvincular_lideres' => FALSE,
    ]);
  }
}
