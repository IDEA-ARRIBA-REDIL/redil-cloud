<?php

namespace Database\Seeders;

use App\Models\TareaConsolidacionUsuario;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TareaConsolidacionUsuarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TareaConsolidacionUsuario::firstOrCreate([
          'tarea_consolidacion_id' => 1,
          'user_id' => 11,
          'estado_tarea_consolidacion_id' => 3,
          'fecha' => '2025-09-01',
        ]);

        TareaConsolidacionUsuario::firstOrCreate([
          'tarea_consolidacion_id' => 2,
          'user_id' => 11,
          'estado_tarea_consolidacion_id' => 2,
          'fecha' => '2025-09-10',
        ]);

        TareaConsolidacionUsuario::firstOrCreate([
          'tarea_consolidacion_id' => 3,
          'user_id' => 11,
          'estado_tarea_consolidacion_id' => 1,
          'fecha' => '2025-09-15',
        ]);
    }
}
