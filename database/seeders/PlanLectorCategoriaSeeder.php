<?php

namespace Database\Seeders;

use App\Models\PlanLectorCategoria;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PlanLectorCategoriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categorias = [
            'Vida devocional',
            'Matrimonio y familia',
            'Finanzas',
            'Liderazgo',
            'Discipulado',
            'Evangelismo',
            'Sanidad',
            'Adoración',
            'Nuevo Testamento',
            'Antiguo Testamento',
            'Proverbios',
            'Salmos',
            'Evangelios',
            'Hechos',
            'Apocalipsis'
        ];

        foreach ($categorias as $categoria) {
            PlanLectorCategoria::firstOrCreate([
                'slug' => Str::slug($categoria),
            ], [
                'nombre' => $categoria,
            ]);
        }
    }
}
