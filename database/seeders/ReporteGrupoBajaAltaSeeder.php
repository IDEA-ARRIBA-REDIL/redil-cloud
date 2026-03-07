<?php

namespace Database\Seeders;

use App\Models\ReporteGrupoBajaAlta;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ReporteGrupoBajaAltaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
      ReporteGrupoBajaAlta::firstOrCreate([
        'motivo' => 'Es un grupo duplicado',
        'observaciones' => 'Por error se duplico el grupo',
        'fecha' => '2023-12-13',
        'grupo_id' => 4,
        'dado_baja' => 1
      ]);

      ReporteGrupoBajaAlta::firstOrCreate([
        'motivo' => 'Se dio de baja por error',
        'observaciones' => 'No era este el grupo, por eso se activa de nuevo',
        'fecha' => '2023-12-13',
        'grupo_id' => 4,
        'dado_baja' => 0
      ]);
    }
}
