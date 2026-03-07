<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Moneda;

class MonedaSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    //habilitada_donacion
    Moneda::firstOrCreate([
      'nombre' => 'Pesos Colombianos',
      'nombre_corto' => 'COP',
      'donacion_maxima' => '30000000',
      'habilitada_donacion' => true,
      'codigo_alpha'=>'co',
      'default' => 1
    ]);

    Moneda::firstOrCreate([
      'nombre' => 'Dólares',
      'nombre_corto' => 'USD',
      'donacion_maxima' => '10000',
      'habilitada_donacion' => true,
      'codigo_alpha'=>'us',
      'default' => 0
    ]);
  }
}
