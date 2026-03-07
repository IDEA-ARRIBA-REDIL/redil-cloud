<?php

namespace Database\Seeders;

use App\Models\TipoInasistencia;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TipoInasistenciaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TipoInasistencia::firstOrCreate(
          ['nombre' => 'Enfermedad'],
          [
          'observacion_obligatoria' => false,
          'es_no_reporte' => false
        ]);

        TipoInasistencia::firstOrCreate(
          ['nombre' => 'Transporte'],
          [
          'observacion_obligatoria' => false,
          'es_no_reporte' => false
        ]);

        TipoInasistencia::firstOrCreate(
          ['nombre' => 'Actividad en la iglesia'],
          [
          'observacion_obligatoria' => false,
          'es_no_reporte' => false
        ]);

        TipoInasistencia::firstOrCreate(
          ['nombre' => 'Otro'],
          [
          'observacion_obligatoria' => true,
          'es_no_reporte' => false
        ]);
    }
}
