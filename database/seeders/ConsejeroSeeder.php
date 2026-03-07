<?php

namespace Database\Seeders;

use App\Models\Consejero;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ConsejeroSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Consejero::firstOrCreate([
            'user_id' => 6,
            'activo' => true,
            'descripcion' => 'Descripción del primer consejero de prueba.',
            'atencion_presencial' => true,
            'direccion' => 'Calle 36# 36-95.',
            'atencion_virtual' => true
        ]);
        
    }
}
