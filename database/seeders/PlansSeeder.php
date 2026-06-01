<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlansSeeder extends Seeder
{
    public function run(): void
    {
        $planes = [
            [
                'nombre' => 'Básico 350',
                'slug' => 'basico-350',
                'max_miembros' => 350,
                'incluye_logo' => false,
                'incluye_marca_blanca' => false,
                'activo' => true,
            ],
            [
                'nombre' => 'Básico 700',
                'slug' => 'basico-700',
                'max_miembros' => 700,
                'incluye_logo' => false,
                'incluye_marca_blanca' => false,
                'activo' => true,
            ],
            [
                'nombre' => 'Estándar',
                'slug' => 'estandar',
                'max_miembros' => 1500,
                'incluye_logo' => true,
                'incluye_marca_blanca' => false,
                'activo' => true,
            ],
            [
                'nombre' => 'Premium',
                'slug' => 'premium',
                'max_miembros' => null,
                'incluye_logo' => true,
                'incluye_marca_blanca' => true,
                'activo' => true,
            ],
        ];

        foreach ($planes as $plan) {
            Plan::updateOrCreate(
                ['slug' => $plan['slug']],
                $plan
            );
        }

        $this->command->info('Planes creados: '.Plan::count());
    }
}
