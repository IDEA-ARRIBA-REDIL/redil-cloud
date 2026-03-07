<?php

namespace Database\Seeders;

use App\Models\InformeEvidenciaGrupo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class InformeEvidenciasGrupoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $grupoId = 4;
        $startDate = Carbon::now()->startOfYear();
        $endDate = Carbon::now();

        // Generate 20 records
        for ($i = 0; $i < 20; $i++) {
            $date = $endDate->copy()->subDays($i * 7); // Previous weeks

            // Only if year matches (just to be safe with "default to current year" logic testing)
            // But let's just make sure they vary.
            InformeEvidenciaGrupo::firstOrCreate(
                [
                    'grupo_id' => $grupoId,
                    'nombre' => 'Informe Semanal ' . ($i + 1),
                    'fecha' => $date->format('Y-m-d'),
                ],
                [
                    'campo1' => 'Evidencia ' . ($i + 1) . ' para campo 1',
                    'campo2' => 'Evidencia ' . ($i + 1) . ' para campo 2',
                    'campo3' => 'Evidencia ' . ($i + 1) . ' para campo 3',
                ]
            );
        }



        $grupoId = 2;
        $startDate = Carbon::now()->startOfYear();
        $endDate = Carbon::now();

        // Generate 3 records
        for ($i = 0; $i < 3; $i++) {
            $date = $endDate->copy()->subDays($i * 7); // Previous weeks

            // Only if year matches (just to be safe with "default to current year" logic testing)
            // But let's just make sure they vary.
            InformeEvidenciaGrupo::firstOrCreate(
                [
                    'grupo_id' => $grupoId,
                    'nombre' => 'Informe Semanal ' . ($i + 1),
                    'fecha' => $date->format('Y-m-d'),
                ],
                [
                    'campo1' => 'Evidencia ' . ($i + 1) . ' para campo 1',
                    'campo2' => 'Evidencia ' . ($i + 1) . ' para campo 2',
                    'campo3' => 'Evidencia ' . ($i + 1) . ' para campo 3',
                ]
            );
        }
    }
}
