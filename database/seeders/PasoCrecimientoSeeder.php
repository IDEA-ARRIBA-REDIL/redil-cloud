<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\PasoCrecimiento;

class PasoCrecimientoSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $paso1 = PasoCrecimiento::firstOrCreate([
      'nombre' => 'Ingreso a la iglesia',
      'orden' => 1,
      'seccion_paso_crecimiento_id' => 1
    ]);

    $paso2 = PasoCrecimiento::firstOrCreate([
      'nombre' => 'Bautismo',
      'orden' => 2,
      'seccion_paso_crecimiento_id' => 1
    ]);

    $paso3 = PasoCrecimiento::firstOrCreate([
      'nombre' => 'Encuentro',
      'orden' => 1,
      'seccion_paso_crecimiento_id' => 2
    ]);

    $paso4 = PasoCrecimiento::firstOrCreate([
      'nombre' => 'Re-encuentro',
      'orden' => 2,
      'seccion_paso_crecimiento_id' => 2
    ]);

    $paso5 = PasoCrecimiento::firstOrCreate([
      'nombre' => 'Conectate 1',
      'orden' => 3,
      'seccion_paso_crecimiento_id' => 2
    ]);

    $paso6 = PasoCrecimiento::firstOrCreate([
      'nombre' => 'Conectate 2',
      'orden' => 4,
      'seccion_paso_crecimiento_id' => 2
    ]);

    // Le asigno estos pasos al rol con ID 3
    $paso1->roles()->attach(3);
    $paso3->roles()->attach(3);
  }
}
