<?php

namespace Database\Seeders;

use App\Models\Caja;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CajaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Caja::firstOrCreate([
            'nombre' => 'Caja general 1',
            'user_id' => 1,
            'punto_de_pago_id' => 1,
            'estado' => true
        ]);
    }
}
