<?php

namespace Database\Seeders;

use App\Models\CrecimientoUsuario;
use Illuminate\Database\Seeder;

class CrecimientoUsuarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CrecimientoUsuario::firstOrCreate([
            'paso_crecimiento_id' => 1,
            'user_id' => 6,
            'estado_id' => 3,
            'fecha' => '2021-08-05',
            'detalle' => 'Hola, esto es un ejemplo.',
        ]);

        CrecimientoUsuario::firstOrCreate([
            'paso_crecimiento_id' => 1,
            'user_id' => 9,
            'estado_id' => 3,
            'fecha' => '2024-01-01',
            'detalle' => 'Hola, esto es un ejemplo2.',
        ]);

        CrecimientoUsuario::firstOrCreate([
            'paso_crecimiento_id' => 2,
            'user_id' => 6,
            'estado_id' => 3,
            'fecha' => '2022-01-01',
            'detalle' => '',
        ]);

        CrecimientoUsuario::firstOrCreate([
            'paso_crecimiento_id' => 3,
            'user_id' => 6,
            'estado_id' => 3,
            'fecha' => '2023-01-01',
            'detalle' => '',
        ]);

        CrecimientoUsuario::firstOrCreate([
            'paso_crecimiento_id' => 4,
            'user_id' => 6,
            'estado_id' => 3,
            'fecha' => '2024-01-01',
            'detalle' => '',
        ]);
    }
}
