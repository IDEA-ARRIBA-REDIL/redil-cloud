<?php

namespace Database\Seeders;

use App\Models\PuntoDePago;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PuntoDePagoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PuntoDePago::firstOrCreate([
          'nombre' => 'Punto de pago auditorio principal',
          'sede_id' => 1
        ]);

        PuntoDePago::firstOrCreate([
          'nombre' => 'Punto de pago sede Suba',
          'sede_id' => 1
        ]);
    }
}
