<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TareaConsolidacionTipoConsejeriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tarea_consolidacion_tipo_consejeria')->updateOrInsert([
            'tarea_consolidacion_id' => 2,
            'tipo_consejeria_id' => 4,
        ]);

        DB::table('tarea_consolidacion_tipo_consejeria')->updateOrInsert([
            'tarea_consolidacion_id' => 3,
            'tipo_consejeria_id' => 4,
        ]);
    }
}
