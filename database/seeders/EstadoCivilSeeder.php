<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\EstadoCivil;

class EstadoCivilSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    EstadoCivil::firstOrCreate([
      'nombre' => 'Soltero',
    ]);

    EstadoCivil::firstOrCreate([
      'nombre' => 'Casado por lo civil',
    ]);

    EstadoCivil::firstOrCreate([
      'nombre' => 'Casado por la iglesia',
    ]);

    EstadoCivil::firstOrCreate([
      'nombre' => 'Unión libre',
      'es_union_libre' => true,
    ]);

    EstadoCivil::firstOrCreate([
      'nombre' => 'Divorciado',
    ]);

    EstadoCivil::firstOrCreate([
      'nombre' => 'Viudo',
    ]);

    EstadoCivil::firstOrCreate([
      'nombre' => 'Separado',
    ]);

    EstadoCivil::firstOrCreate([
      'nombre' => 'Casado por ambas',
    ]);
  }
}
