<?php

namespace Database\Seeders;

use App\Models\MotivoNoReporteGrupo;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MotivoNoReporteGrupoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        MotivoNoReporteGrupo::firstOrCreate([
          'nombre'=>'Compromiso'
        ]);

        MotivoNoReporteGrupo::firstOrCreate([
          'nombre'=>'Enfermedad'
        ]);

        MotivoNoReporteGrupo::firstOrCreate([
          'nombre'=>'Asunto familiar'
        ]);

        MotivoNoReporteGrupo::firstOrCreate([
          'nombre'=>'Otro',
          'descripcion_adicional'=>true
        ]);

    }
}
