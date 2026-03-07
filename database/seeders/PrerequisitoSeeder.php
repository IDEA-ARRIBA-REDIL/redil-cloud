<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\NivelEscuela; // Namespace correcto
use App\Models\Materia; // Namespace correcto
class PrerequisitoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        // Prerrequisitos para materias (Escuela Dominical)
        $materiasDominical = Materia::where('escuela_id', 1)->get();
        
        foreach($materiasDominical as $index => $materia) {
            if($index > 0) {
                $materia->prerrequisitosMaterias()->attach($materiasDominical[$index - 1]->id);
            }
        }

     
    }
}
