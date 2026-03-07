<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Illuminate\Support\Facades\DB;

class PasoCrecimientoTipoConsejeriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {


        DB::table('paso_crecimiento_tipo_consejeria')->updateOrInsert([
            'paso_crecimiento_id' => 5,
            'tipo_consejeria_id' => 4,
        ]);

        DB::table('paso_crecimiento_tipo_consejeria')->updateOrInsert([
            'paso_crecimiento_id' => 6,
            'tipo_consejeria_id' => 4,
        ]);


    }
}
