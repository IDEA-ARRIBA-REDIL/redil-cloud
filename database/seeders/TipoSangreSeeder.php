<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\TipoSangre;

class TipoSangreSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $tiposDeSangres = '[
      {"id":"1","nombre":"A+"},
      {"id":"2","nombre":"A-"},
      {"id":"3","nombre":"AB+"},
      {"id":"4","nombre":"AB-"},
      {"id":"5","nombre":"B+"},
      {"id":"6","nombre":"B-"},
      {"id":"7","nombre":"O+"},
      {"id":"8","nombre":"O-"}
    ]';

    $items = json_decode($tiposDeSangres);
    foreach ($items as $item) {
      TipoSangre::firstOrCreate([
        'nombre' => $item->nombre,
      ]);
    }
  }
}
