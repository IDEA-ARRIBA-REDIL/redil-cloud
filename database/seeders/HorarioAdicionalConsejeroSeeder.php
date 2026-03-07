<?php

namespace Database\Seeders;

use App\Models\HorarioAdicionalConsejero;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HorarioAdicionalConsejeroSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         HorarioAdicionalConsejero::firstOrCreate([
            'consejero_id' => 1,
            'fecha_inicio' => '2025-11-27 12:00:00',
            'fecha_fin' => '2025-11-27 13:00:00',
            'motivo' => 'Horario extra uno'
        ]);


         HorarioAdicionalConsejero::firstOrCreate([
            'consejero_id' => 1,
            'fecha_inicio' => '2025-11-15 12:00:00',
            'fecha_fin' => '2025-11-16 15:00:00',
            'motivo' => 'Horario extra dos'
        ]);
    }
}
