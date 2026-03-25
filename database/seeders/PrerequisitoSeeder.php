<?php

namespace Database\Seeders;

use App\Models\Materia;
// Namespace correcto
use Illuminate\Database\Seeder; // Namespace correcto

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

        foreach ($materiasDominical as $index => $materia) {
            if ($index > 0) {
                $materia->prerrequisitosMaterias()->attach($materiasDominical[$index - 1]->id);
            }
        }

    }
}
