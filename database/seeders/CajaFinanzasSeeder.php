<?php

namespace Database\Seeders;

use App\Models\CajaFinanzas;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CajaFinanzasSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // CAJAS DE FINANZAS
    $caja1 = CajaFinanzas::firstOrCreate([
      'nombre' => 'Caja General',
      'descripcion' => 'Caja central de la organización'
    ]);

    $caja2 = CajaFinanzas::firstOrCreate([
      'nombre' => 'Caja Menor',
      'descripcion' => 'Caja menor para insertidumbres'
    ]);

    $caja3 = CajaFinanzas::firstOrCreate([
      'nombre' => 'Caja Auxiliar',
      'descripcion' => 'Caja auxiliar para eventos'
    ]);
  }
}
