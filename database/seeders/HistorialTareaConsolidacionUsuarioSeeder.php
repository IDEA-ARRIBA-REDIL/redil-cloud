<?php

namespace Database\Seeders;

use App\Models\HistorialTareaConsolidacionUsuario;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HistorialTareaConsolidacionUsuarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        HistorialTareaConsolidacionUsuario::firstOrCreate([
          'fecha' => '2025-09-01',
          'detalle' => 'Creación de la tarea en estado finalizado',
          'tarea_consolidacion_usuario_id' => 1,
          'usuario_creacion_id' => 1
        ]);

        HistorialTareaConsolidacionUsuario::firstOrCreate([
          'fecha' => '2025-09-03',
          'detalle' => 'Se llamó y fue todo un éxito, la persona fue muy receptiva',
          'tarea_consolidacion_usuario_id' => 1,
          'usuario_creacion_id' => 1
        ]);
    }
}
