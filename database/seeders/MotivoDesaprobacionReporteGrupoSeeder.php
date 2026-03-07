<?php

namespace Database\Seeders;

use App\Models\MotivoDesaprobacionReporteGrupo;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MotivoDesaprobacionReporteGrupoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

      MotivoDesaprobacionReporteGrupo::firstOrCreate([
        'nombre'=>'Excedente en el sobre'
      ]);

      MotivoDesaprobacionReporteGrupo::firstOrCreate([
        'nombre'=>'Faltante en el sobre'
      ]);

      MotivoDesaprobacionReporteGrupo::firstOrCreate([
        'nombre'=>'Otro'
      ]);

    }
}
