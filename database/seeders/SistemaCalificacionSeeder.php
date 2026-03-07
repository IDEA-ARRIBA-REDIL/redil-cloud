<?php

namespace Database\Seeders;
use App\Models\SistemaCalificacion;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SistemaCalificacionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //

        SistemaCalificacion::firstOrCreate([
            'nombre' => 'E,S,A,I,D,',
            'es_numerico'=>true
            
          ]);

          SistemaCalificacion::firstOrCreate([
            'nombre' => 'De 0 a 100',
            'es_numerico'=>true
            
          ]);

          SistemaCalificacion::firstOrCreate([
            'nombre' => 'De 0 a 5',
            'es_numerico'=>true            
          ]);
      
    }
}
