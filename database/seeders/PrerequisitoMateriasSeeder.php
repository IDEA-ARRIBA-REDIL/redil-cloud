<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PrerequisitoMateria;

class PrerequisitoMateriasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
         // Para la materia 2, se requiere haber aprobado la materia 1
         PrerequisitoMateria::firstOrCreate([
            'materia_id' => 2, // Materia que tiene restricción
            'materia_requerida_id' => 1, // Materia requerida
       
        ]);

        // Para la materia 3, se requiere haber aprobado la materia 2
        PrerequisitoMateria::firstOrCreate([
            'materia_id' => 3,
            'materia_requerida_id' => 2,
          
        ]);

        // Para la materia 4, se requiere haber aprobado la materia 3
        PrerequisitoMateria::firstOrCreate([
            'materia_id' => 4,
            'materia_requerida_id' => 3,
      
        ]);
    }
}
