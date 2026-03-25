<?php

namespace Database\Seeders;

use App\Models\TipoServicioGrupo;
use Illuminate\Database\Seeder;

class TipoServicioGrupoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TipoServicioGrupo::firstOrCreate([
            'nombre' => 'Anfitrion',
            'descripcion' => '',
        ]);

        TipoServicioGrupo::firstOrCreate([
            'nombre' => 'Tesorero',
            'descripcion' => '',
        ]);

        TipoServicioGrupo::firstOrCreate([
            'nombre' => 'Timoteo',
            'descripcion' => '',
        ]);
    }
}
