<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\PlanLectorTipoContenido;

class PlanLectorTipoContenidoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tipos = [
            [
                'nombre' => 'Reflexión / Texto',
                'slug' => 'reflexion',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Pasaje Bíblico',
                'slug' => 'pasaje',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Video',
                'slug' => 'video',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        foreach ($tipos as $tipo) {
            PlanLectorTipoContenido::updateOrCreate(
                ['slug' => $tipo['slug']],
                $tipo
            );
        }
    }
}
