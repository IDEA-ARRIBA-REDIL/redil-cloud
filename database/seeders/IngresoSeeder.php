<?php

namespace Database\Seeders;

use App\Models\Ingreso;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class IngresoSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    //INGRESO
    $ingreso = Ingreso::firstOrCreate([
      'fecha' => now(),
      'nombre' => 'Carlos Ramírez',
      'identificacion' => '456789123',
      'tipo_identificacion' => 'CC',
      'telefono' => '3104567890',
      'direccion' => 'Av. Siempre Viva 742',
      'valor' => 200000.00,
      'descripcion' => 'Ofrenda dominical',
      'tipo_ofrenda_id' => 1, // Suponiendo tipo existente
      'caja_finanzas_id' => 1,
      'centro_de_costos_ingresos_id' => 1,
      'campo_adicional1' => 'Sin observaciones',
      'campo_adicional2' => null,
      'anulado' => false,
      'usuario_anulacion' => null,
      'fecha_anulacion' => null,
      'user_id' => 1, // Suponiendo asistente existente
      'sede_id' => 1,
      'moneda_id' => 1, // Moneda existente
      'ofrenda_id' => 1, // Ofrenda existente
      // 'aprobado_reporte_grupo' => true,
      'valor_real' => 200000.00,
      'compra_id' => null,
      'ingreso_por_grupo' => true,
      'ingreso_por_reunion' => false,
      'ingreso_por_actividades' => false,
      'ingreso_por_ofrenda_online' => false,
      'ingreso_manual' => true
    ]);
    $ingreso2 = Ingreso::firstOrCreate([
      'fecha' => now()->subDay(),
      'nombre' => 'Luisa Fernández',
      'identificacion' => '789123456',
      'tipo_identificacion' => 'TI',
      'telefono' => '3119876543',
      'direccion' => 'Calle 123 #45-67',
      'valor' => 150000.00,
      'descripcion' => 'Ofrenda especial',
      'tipo_ofrenda_id' => 2,
      'caja_finanzas_id' => 2,
      'centro_de_costos_ingresos_id' => 2,
      'campo_adicional1' => 'Para ministerio de jóvenes',
      'campo_adicional2' => null,
      'anulado' => false,
      'usuario_anulacion' => null,
      'fecha_anulacion' => null,
      'user_id' => 2,
      'sede_id' => 2,
      'moneda_id' => 2,
      'ofrenda_id' => 2,
      //'aprobado_reporte_grupo' => false,
      'valor_real' => 150000.00,
      'compra_id' => null,
      'ingreso_por_grupo' => false,
      'ingreso_por_reunion' => true,
      'ingreso_por_actividades' => false,
      'ingreso_por_ofrenda_online' => false,
      'ingreso_manual' => true
    ]);

    // $ingreso3 = Ingreso::firstOrCreate([
    //   'fecha' => now()->subDays(2),
    //   'nombre' => 'Julián Castro',
    //   'identificacion' => '321654987',
    //   'tipo_identificacion' => 'CE',
    //   'telefono' => '3124567891',
    //   'direccion' => 'Carrera 8 #34-90',
    //   'valor' => 100000.00,
    //   'descripcion' => 'Diezmo mensual',
    //   'tipo_ofrenda_id' => 3,
    //   'caja_finanzas_id' => 2,
    //   'campo_adicional1' => 'Primera vez que diezma',
    //   'campo_adicional2' => null,
    //   'anulado' => false,
    //   'usuario_anulacion' => null,
    //   'fecha_anulacion' => null,
    //   'user_id' => 2,
    //   'sede_id' => 2,
    //   'moneda_id' => 2,
    //   'ofrenda_id' => 3,
    //   //'aprobado_reporte_grupo' => true,
    //   'valor_real' => 100000.00,
    //   'compra_id' => null,
    //   'ingreso_por_grupo' => false,
    //   'ingreso_por_reunion' => false,
    //   'ingreso_por_actividades' => true,
    //   'ingreso_por_ofrenda_online' => false,
    //   'ingreso_manual' => true
    // ]);
    // crear dos mas con los datos especificos de la tabla ofrenda y las ofrendas 4 y 5
  }
}
