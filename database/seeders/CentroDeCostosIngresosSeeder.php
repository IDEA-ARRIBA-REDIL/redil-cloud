<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class CentroDeCostosIngresosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
      $data = [
        [
          'nombre' => 'Ventas de Productos',
          'codigo' => 'VP001',
          'created_at' => now(),
          'updated_at' => now(),
        ],
        [
          'nombre' => 'Servicios Prestados',
          'codigo' => 'SP002',
          'created_at' => now(),
          'updated_at' => now(),
        ],
        [
          'nombre' => 'Inversiones',
          'codigo' => 'IV003',
          'created_at' => now(),
          'updated_at' => now(),
        ],
        [
          'nombre' => 'Donaciones',
          'codigo' => 'DN004',
          'created_at' => now(),
          'updated_at' => now(),
        ],
        [
          'nombre' => 'Otros Ingresos',
          'codigo' => 'OI005',
          'created_at' => now(),
          'updated_at' => now(),
        ],
      ];

      foreach ($data as $item) {
        \App\Models\CentroDeCostosIngresos::firstOrCreate(
          ['codigo' => $item['codigo']],
          $item
        );
      }
    }
}
