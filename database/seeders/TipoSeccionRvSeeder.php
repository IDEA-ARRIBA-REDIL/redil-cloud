<?php

namespace Database\Seeders;

use App\Models\TipoSeccionRv;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TipoSeccionRvSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //


        TipoSeccionRv::firstOrCreate([
            'nombre' => 'contador',
            'min' => '0',
            'max' => '10',
            'validacion' => true,
            'encuesta' => false,
            'resumen' => false,
            'url_imagen' => ''

        ]);

        TipoSeccionRv::firstOrCreate([
            'nombre' => 'promedios',
            'min' => '0',
            'max' => '10',
            'validacion' => true,
            'encuesta' => false,
            'resumen' => false,
            'url_imagen' => ''

        ]);

        TipoSeccionRv::firstOrCreate([
            'nombre' => 'encuesta',
            'min' => '0',
            'max' => '10',
            'validacion' => false,
            'encuesta' => true,
            'resumen' => false,
            'url_imagen' => ''

        ]);

        TipoSeccionRv::firstOrCreate([
            'nombre' => 'bienvenida',
            'min' => '0',
            'max' => '10',
            'validacion' => false,
            'encuesta' => false,
            'resumen' => true,
            'url_imagen' => ''

        ]);
    }
}
