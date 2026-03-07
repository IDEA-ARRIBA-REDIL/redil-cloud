<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Ocupacion;

class OcupacionSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $ocupaciones = '[
      {"id":"1","nombre":"Ama de casa"},
      {"id":"2","nombre":"Desempleado"},
      {"id":"3","nombre":"Empleado"},
      {"id":"4","nombre":"Empresario"},
      {"id":"5","nombre":"Estudiante"},
      {"id":"6","nombre":"Independiente"},
      {"id":"7","nombre":"Pensionado o jubilado"}
    ]';

    $items = json_decode($ocupaciones);

    foreach ($items as $item) {
      Ocupacion::firstOrCreate([
        'nombre' => $item->nombre,
      ]);
    }
  }
}
