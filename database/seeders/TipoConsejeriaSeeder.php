<?php

namespace Database\Seeders;

use App\Models\TipoConsejeria;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TipoConsejeriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TipoConsejeria::firstOrCreate([
            'nombre' => 'Consejería matrimonial',
            'descripcion' => 'Consejeria ....'
        ]);

        TipoConsejeria::firstOrCreate([
            'nombre' => 'Consejería familiar',
            'descripcion' => 'Consejeria ....'
        ]);

        TipoConsejeria::firstOrCreate([
            'nombre' => 'Consejería emocional',
            'descripcion' => 'Consejeria ....'
        ]);

        TipoConsejeria::firstOrCreate([
            'nombre' => 'Consejería conéctate',
            'descripcion' => 'Consejeria ....'
        ]);
    }
}
