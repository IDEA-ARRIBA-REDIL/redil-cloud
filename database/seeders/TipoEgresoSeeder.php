<?php

namespace Database\Seeders;

use App\Models\TipoEgreso;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TipoEgresoSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $data = [
      [
        'nombre' => 'Servicios públicos',
        'descripcion' => 'Pagos de servicios como agua, luz, gas, internet, etc.',
        'created_at' => now(),
        'updated_at' => now(),
      ],
      [
        'nombre' => 'Honorarios',
        'descripcion' => 'Pagos a terceros por servicios profesionales o técnicos.',
        'created_at' => now(),
        'updated_at' => now(),
      ],
      [
        'nombre' => 'Materiales y suministros',
        'descripcion' => 'Compra de materiales necesarios para el funcionamiento diario.',
        'created_at' => now(),
        'updated_at' => now(),
      ],
      [
        'nombre' => 'Mantenimiento',
        'descripcion' => 'Gastos en reparaciones o mantenimiento de equipos e instalaciones.',
        'created_at' => now(),
        'updated_at' => now(),
      ],
      [
        'nombre' => 'Transporte y viáticos',
        'descripcion' => 'Gastos de movilidad, pasajes, combustible o viáticos laborales.',
        'created_at' => now(),
        'updated_at' => now(),
      ],
    ];

    foreach ($data as $tipoEgreso) {
      TipoEgreso::firstOrCreate(['nombre' => $tipoEgreso['nombre']], $tipoEgreso);
    }
  }
}
