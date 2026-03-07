<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\SectorEconomico;

class SectorEconomicoSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $sectoresEconomicos = '[
      {"id":"1","nombre":"Administración"},
      {"id":"2","nombre":"Comercial"},
      {"id":"3","nombre":"Construcción"},
      {"id":"4","nombre":"Educativo"},
      {"id":"5","nombre":"Energético"},
      {"id":"6","nombre":"Forestal"},
      {"id":"7","nombre":"Financiero"},
      {"id":"8","nombre":"Ganadero"},
      {"id":"9","nombre":"Industrial"},
      {"id":"10","nombre":"Minero"},
      {"id":"11","nombre":"Pesquero"},
      {"id":"12","nombre":"Religioso"},
      {"id":"13","nombre":"Servicios"},
      {"id":"14","nombre":"Sanitario"},
      {"id":"15","nombre":"TIC"},
      {"id":"16","nombre":"Transporte"},
      {"id":"17","nombre":"Turistico"},
      {"id":"18","nombre":"Público"},
      {"id":"19","nombre":"Agricultura"}
    ]';

    $items = json_decode($sectoresEconomicos);
    foreach ($items as $item) {
      SectorEconomico::firstOrCreate([
        'nombre' => $item->nombre,
      ]);
    }
  }
}
