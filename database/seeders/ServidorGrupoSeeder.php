<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\ServidorGrupo;

class ServidorGrupoSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    ServidorGrupo::firstOrCreate([
      'grupo_id' => 2,
      'user_id' => 6,
    ]);

    ServidorGrupo::firstOrCreate([
      'grupo_id' => 2,
      'user_id' => 3,
    ]);
  }
}
