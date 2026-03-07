<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Abono;
use Illuminate\Support\Facades\DB;

class AbonoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        Abono::firstOrCreate([
            'fecha_inicio' => '2024-10-01',
            'fecha_fin' => '2024-10-15',
        ]);

        Abono::firstOrCreate([
            'fecha_inicio' => '2024-10-16',
            'fecha_fin' => '2024-10-22',
        ]);

        Abono::firstOrCreate([
            'fecha_inicio' => '2024-10-23',
            'fecha_fin' => '2024-10-30',
        ]);

        ////ABONOS ACTIVIDAD CON ABONOS


        Abono::firstOrCreate([
            'fecha_inicio' => '2025-03-01',
            'fecha_fin' => '2025-03-06',
        ]);

        Abono::firstOrCreate([
            'fecha_inicio' => '2025-03-07',
            'fecha_fin' => '2025-03-30',
        ]);
    }
}
