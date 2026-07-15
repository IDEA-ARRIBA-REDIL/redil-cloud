<?php

namespace Database\Seeders;

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
            'tarea_consolidacion_id' => 6,
            'tipo_consejeria_id' => 4,
        ]);

    }
}
