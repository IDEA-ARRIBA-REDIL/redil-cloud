<?php

namespace Database\Seeders;

use App\Models\Egreso;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EgresoSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    //EGRESO
    $egreso1 = Egreso::firstOrCreate([
      'fecha' => now(),
      'proveedor_id' => 1,
      'documento_equivalente_id' => 2,
      'caja_finanzas_id' => 1,
      'centro_de_costos_egresos_id' => 1,
      'tipo_egreso_id' => 2,
      'valor' => 100000.00,
      'descripcion' => 'Compra de materiales',
      'campo_adicional1' => 'Pago en efectivo',
      'anulado' => false,
      'sede_id' => 2,
      'moneda_id' => 1
    ]);
    $egreso2 = Egreso::firstOrCreate([
      'fecha' => now()->subDays(),
      'proveedor_id' => 2,
      'documento_equivalente_id' => 1,
      'caja_finanzas_id' => 2,
      'centro_de_costos_egresos_id' => 2,
      'tipo_egreso_id' => 1,
      'valor' => 150000.00,
      'descripcion' => 'Compra de comida',
      'campo_adicional1' => 'Pago con tarjeta',
      'anulado' => false,
      'sede_id' => 1,
      'moneda_id' => 1
    ]);
    $egreso3 = Egreso::firstOrCreate([
      'fecha' => now()->subDays(2),
      'proveedor_id' => 3,
      'documento_equivalente_id' => 2,
      'caja_finanzas_id' => 1,
      'centro_de_costos_egresos_id' => 2,
      'tipo_egreso_id' => 2,
      'valor' => 120000.00,
      'descripcion' => 'Compra de comida',
      'campo_adicional1' => 'Pago con tarjeta',
      'anulado' => false,
      'sede_id' => 2,
      'moneda_id' => 1
    ]);
  }
}
