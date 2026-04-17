<?php

namespace Database\Seeders;

use App\Models\BannerGeneral;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class BannerGeneralSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Definimos los datos de los 4 banners
        $bannersData = [
            [
                'nombre' => 'Calendario Legacy 2026',
                'imagen' => 'legacy.jpeg',
                'visible' => true,
            ],
            [
                'nombre' => 'Manantial Kids 2026',
                'imagen' => 'manantialkids.jpeg',
                'visible' => true,
            ],
            [
                'nombre' => 'Legendarios 2026',
                'imagen' => 'legenderios.jpeg',
                'visible' => true,
            ],
            [
                'nombre' => 'Campaña 2026 Ora por Colombia',
                'imagen' => 'ora.jpeg',
                'visible' => true,
            ],
        ];

        // 2. Creación de registros
        foreach ($bannersData as $data) {
            // Creamos o actualizamos el registro en la base de datos
            BannerGeneral::firstOrCreate(
                ['nombre' => $data['nombre']],
                [
                    'imagen'       => $data['imagen'],
                    'fecha_inicio' => Carbon::now()->subDays(5)->format('Y-m-d'),
                    'fecha_fin'    => Carbon::now()->addDays(30)->format('Y-m-d'),
                    'visible'      => true,
                ]
            );
        }
    }
}
