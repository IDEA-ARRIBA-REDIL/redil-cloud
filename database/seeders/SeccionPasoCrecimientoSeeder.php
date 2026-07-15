<?php

namespace Database\Seeders;

use App\Models\SeccionPasoCrecimiento;
use Illuminate\Database\Seeder;

class SeccionPasoCrecimientoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $secciones = [
            'Primeros Pasos',
            'Mi Camino Hacia La Libertad',
            'Encuentros',
            'Escuelas El Camino Warriors',
            'Escuelas El Camino',
            'Escuelas Especialización Cosmovisión Bíblica',
            'Escuelas Especialización Maestros',
            'Escuelas Especialización Interseción',
        ];

        foreach ($secciones as $index => $seccion) {
            SeccionPasoCrecimiento::firstOrCreate(
                ['nombre' => $seccion],
                ['orden'  => $index + 1]
            );
        }
    }
}
