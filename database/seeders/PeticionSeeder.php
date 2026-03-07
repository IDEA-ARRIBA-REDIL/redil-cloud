<?php

namespace Database\Seeders;

use App\Models\Peticion;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PeticionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Peticion::firstOrCreate([
          'user_id' => 9,
          'pais_id' => 54,
          'tipo_peticion_id' => 1,
          'estado' => 1,
          'descripcion' => 'Pido oración para que DIOS tenga miscericordia de mi familia. La amo y quiero que sean salvos. ',
          'fecha' => '2025-03-21',
        ]);

        Peticion::firstOrCreate([
          'user_id' => 7,
          'pais_id' => 5,
          'tipo_peticion_id' => 1,
          'estado' => 1,
          'descripcion' => 'Pido oración para que 2 ',
          'fecha' => '2025-03-21',
          'autor_creacion_id' => 1
        ]);


        Peticion::firstOrCreate([
          'user_id' => 3,
          'pais_id' => 15,
          'tipo_peticion_id' => 3,
          'estado' => 2,
          'descripcion' => 'Pido oración para que 3 ',
          'fecha' => '2025-03-21',
          'autor_creacion_id' => 1
        ]);

        Peticion::firstOrCreate([
          'user_id' => 4,
          'pais_id' => 15,
          'tipo_peticion_id' => 8,
          'estado' => 3,
          'descripcion' => 'Pido oración para que 4. ',
          'fecha' => '2025-03-21',
          'autor_creacion_id' => 1
        ]);

        Peticion::firstOrCreate([
          'user_id' => 6,
          'pais_id' => 53,
          'tipo_peticion_id' => 7,
          'estado' => 1,
          'descripcion' => 'Pido oración para que 5. ',
          'fecha' => '2025-03-21',
          'autor_creacion_id' => 1
        ]);




    }
}
