<?php

namespace Database\Seeders;

use App\Models\TareaConsolidacionUsuario;
use Illuminate\Database\Seeder;

class TareaConsolidacionUsuarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        // Al usar Eloquent (firstOrCreate), se dispararán automáticamente los hooks
        // del modelo TareaConsolidacionUsuario para registrar en la nueva BitacoraTareaConsolidacion.

        /*TareaConsolidacionUsuario::firstOrCreate([
          'tarea_consolidacion_id' => 1,
          'user_id' => 11,
          'estado_tarea_consolidacion_id' => 3,
          'fecha' => now()->format('Y-m-d'),
        ]);

        TareaConsolidacionUsuario::firstOrCreate([
          'tarea_consolidacion_id' => 2,
          'user_id' => 11,
          'estado_tarea_consolidacion_id' => 2,
          'fecha' => now()->subDays(2)->format('Y-m-d'),
        ]);

        TareaConsolidacionUsuario::firstOrCreate([
          'tarea_consolidacion_id' => 3,
          'user_id' => 11,
          'estado_tarea_consolidacion_id' => 1,
          'fecha' => now()->subDays(5)->format('Y-m-d'),
        ]);*/
    }
}
