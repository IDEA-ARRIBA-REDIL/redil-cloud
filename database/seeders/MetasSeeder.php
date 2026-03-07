<?php

namespace Database\Seeders;

use App\Models\Metas;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MetasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        Metas::firstOrCreate([
            'nombre' => 'Meta area Espíritual',
            'descripcion' => '',
            'seccion_rv_id' => '8'
        ]);

        Metas::firstOrCreate([
            'nombre' => 'Meta area  Física',
            'descripcion' => '',
            'seccion_rv_id' => '8'
        ]);

        Metas::firstOrCreate([
            'nombre' => 'Meta area  Intelectual',
            'descripcion' => '',
            'seccion_rv_id' => '8',

        ]);

        Metas::firstOrCreate([
            'nombre' => 'Meta area Familiar',
            'descripcion' => '',
            'seccion_rv_id' => '8'
        ]);

          Metas::firstOrCreate([
            'nombre' => 'Meta area Laboral y financiera',
            'descripcion' => '',
            'seccion_rv_id' => '8'
        ]);

         Metas::firstOrCreate([
            'nombre' => 'Meta area Emocional',
            'descripcion' => '',
            'seccion_rv_id' => '8'
        ]);
    }
}
