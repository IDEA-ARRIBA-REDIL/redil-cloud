<?php

namespace Database\Seeders;

use App\Models\TipoSede;
use Illuminate\Database\Seeder;

class TipoSedeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TipoSede::firstOrCreate([
            'nombre' => 'Sede Principal',
            'descripcion' => '',
        ]);

        TipoSede::firstOrCreate([
            'nombre' => 'Sede',
            'descripcion' => '',
        ]);

        TipoSede::firstOrCreate([
            'nombre' => 'Subsede',
            'descripcion' => '',
        ]);

        TipoSede::firstOrCreate([
            'nombre' => 'Macro Grupo',
            'descripcion' => '',
        ]);
    }
}
