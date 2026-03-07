<?php

namespace Database\Seeders;

use App\Models\Compra;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CompraSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    //
    $compra1 = Compra::firstOrCreate([
      'user_id' => 6,
      'moneda_id' => 1,
      'fecha' => '2024-03-02',
      'valor' => '50000',
      'estado' => 3,
      'metodo_pago_id' => 1,
      'nombre_completo_comprador' => 'darwin castaño',
      'identificacion_comprador' => '1121212',
      'email_comprador' => 'darwin@gmail.com',
      'telefono_comprador' => '2332423',
      'actividad_id' => 4
    ]);
  }
}
