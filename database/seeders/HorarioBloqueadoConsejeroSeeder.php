<?php

namespace Database\Seeders;

use App\Models\HorarioBloqueadoConsejero;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HorarioBloqueadoConsejeroSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        HorarioBloqueadoConsejero::firstOrCreate([
            'consejero_id' => 1,
            'fecha_inicio' => '2025-11-05 12:00:00',
            'fecha_fin' => '2025-11-05 13:00:00',
            'motivo' => 'No hay atención por evento en la iglesia'
        ]);

        HorarioBloqueadoConsejero::firstOrCreate([
            'consejero_id' => 1,
            'fecha_inicio' => '2025-11-15 12:00:00',
            'fecha_fin' => '2025-11-18 12:00:00',
            'motivo' => 'Bonus extra'
        ]);
    }
}
