<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CarreraSeeder extends Seeder
{
    public function run(): void
    {
        $carreras = [
            [
                'nombre' => 'Teología Básica',
                'descripcion' => 'Fundamentos de la fe y doctrina cristiana.',
                'estado' => 'Activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Liderazgo Pastoral',
                'descripcion' => 'Formación para líderes de células y ministerios.',
                'estado' => 'Activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Consejería Bíblica',
                'descripcion' => 'Herramientas para el acompañamiento espiritual.',
                'estado' => 'Activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Misiones y Evangelismo',
                'descripcion' => 'Preparación para la obra misionera local y global.',
                'estado' => 'Activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('carreras')->insert($carreras);
    }
}
