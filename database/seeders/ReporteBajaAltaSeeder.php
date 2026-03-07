<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\ReporteBajaAlta;

class ReporteBajaAltaSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    ReporteBajaAlta::firstOrCreate([
      'motivo' => 'La otra iglesia le queda más cerca.',
      'observaciones' => 'No tiene transporte, entonces le queda más cerca la otra iglesia',
      'fecha' => '2023-12-13',
      'user_id' => 7,
      'dado_baja' => 1,
      'tipo_baja_alta_id' => 2,
    ]);
  }
}
