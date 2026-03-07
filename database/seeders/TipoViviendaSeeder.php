<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\TipoVivienda;

class TipoViviendaSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $tiposDeViviendas = '[
        {"id":"1","nombre":"Propia"},
        {"id":"2","nombre":"Familiar"},
        {"id":"3","nombre":"Arriendo o Alquiler"}
      ]';

    $items = json_decode($tiposDeViviendas);
    foreach ($items as $item) {
      TipoVivienda::firstOrCreate([
        'nombre' => $item->nombre,
      ]);
    }
  }
}
