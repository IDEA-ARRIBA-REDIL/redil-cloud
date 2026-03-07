<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PrerequisitoNivel;

class PrerequisitoNivelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
           // Para el nivel 2, se requiere haber aprobado el nivel 1
           PrerequisitoNivel::firstOrCreate([
            'nivel_id' => 2, // Nivel que tiene restricción
            'nivel_requerido_id' => 1, // Nivel requerido
            
        ]);

        // Para el nivel 3, se requiere haber aprobado el nivel 2
        PrerequisitoNivel::firstOrCreate([
            'nivel_id' => 3,
            'nivel_requerido_id' => 2,
        
        ]);

    }
}
