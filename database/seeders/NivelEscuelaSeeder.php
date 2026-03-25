<?php

namespace Database\Seeders;

use App\Models\NivelEscuela;
use Illuminate\Database\Seeder; // Namespace correcto

class NivelEscuelaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //   // // Niveles para Escuela de Liderazgo
        // --- Asignación Manual (Asumiendo que Escuela ID 1 existe) ---
        $escuelaId = 2; // Cambia este ID si la escuela deseada tiene otro ID

        // --- Creación de Niveles ---
        NivelEscuela::firstOrCreate([
            'nombre' => 'Primer Grado',
            'descripcion' => 'Nivel inicial de educación básica.',
            'escuela_id' => $escuelaId,
            // Agrega otros campos necesarios aquí si tu modelo los requiere
        ]);

        NivelEscuela::firstOrCreate([
            'nombre' => 'Segundo Grado',
            'descripcion' => 'Continuación de la educación básica.',
            'escuela_id' => $escuelaId,
        ]);

        NivelEscuela::firstOrCreate([
            'nombre' => 'Tercer Grado',
            'descripcion' => 'Nivel intermedio de educación básica.',
            'escuela_id' => $escuelaId,
        ]);
    }
}
