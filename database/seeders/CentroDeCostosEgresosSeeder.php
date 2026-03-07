<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class CentroDeCostosEgresosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
      $data = [
        [
          'nombre' => 'Servicios Públicos',
          'codigo' => 'SP001',
          'created_at' => now(),
          'updated_at' => now(),
        ],
        [
          'nombre' => 'Mantenimiento',
          'codigo' => 'MT002',
          'created_at' => now(),
          'updated_at' => now(),
        ],
        [
          'nombre' => 'Papelería y Útiles',
          'codigo' => 'PU003',
          'created_at' => now(),
          'updated_at' => now(),
        ],
        [
          'nombre' => 'Seguridad',
          'codigo' => 'SG004',
          'created_at' => now(),
          'updated_at' => now(),
        ],
        [
          'nombre' => 'Otros Gastos',
          'codigo' => 'OG005',
          'created_at' => now(),
          'updated_at' => now(),
        ],
      ];

      foreach ($data as $item) {
        \App\Models\CentroDeCostosEgresos::firstOrCreate(
          ['codigo' => $item['codigo']],
          $item
        );
      }
    }
}
