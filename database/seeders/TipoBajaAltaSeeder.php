<?php

namespace Database\Seeders;

use App\Models\TipoBajaAlta;
use Illuminate\Database\Seeder;

class TipoBajaAltaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TipoBajaAlta::firstOrCreate(
            ['nombre' => 'Se fue de la ciudad'],
            [
                'dado_baja' => 1,
                'dado_alta' => 0,
            ]);

        TipoBajaAlta::firstOrCreate(
            ['nombre' => 'Cambió de iglesia'],
            [
                'dado_baja' => 1,
                'dado_alta' => 0,
            ]);

        TipoBajaAlta::firstOrCreate(
            ['nombre' => 'Regreso'],
            [
                'dado_baja' => 0,
                'dado_alta' => 1,
            ]);
    }
}
