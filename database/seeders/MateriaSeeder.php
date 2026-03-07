<?php

namespace Database\Seeders;

use App\Models\Materia;
use Illuminate\Database\Seeder;

class MateriaSeeder extends Seeder
{
    public function run(): void
    {
          // Escuela Dominical (4 materias independientes)
          Materia::firstOrCreate(['nombre' => 'Biblia para Niños', 'escuela_id' => 1]);
          Materia::firstOrCreate(['nombre' => 'Canto y Alabanza', 'escuela_id' => 1]);
          Materia::firstOrCreate(['nombre' => 'Historia Bíblica', 'escuela_id' => 1]);
          Materia::firstOrCreate(['nombre' => 'Introducción a la Fe', 'escuela_id' => 1, 'habilitar_asistencias'=>true, 'habilitar_calificaciones'=>true,'habilitar_inasistencias'=>true]);
  
          // Escuela de Liderazgo (3 materias por nivel)
          // Nivel Básico
          Materia::firstOrCreate(['nombre' => 'Liderazgo Cristiano', 'escuela_id' => 2, 'nivel_id' => 1]);
          Materia::firstOrCreate(['nombre' => 'Estudio Bíblico', 'escuela_id' => 2, 'nivel_id' => 1]);
          Materia::firstOrCreate(['nombre' => 'Ética Ministerial', 'escuela_id' => 2, 'nivel_id' => 1]);
          
          // Nivel Intermedio
          Materia::firstOrCreate(['nombre' => 'Administración Eclesiástica', 'escuela_id' => 2, 'nivel_id' => 2]);
          Materia::firstOrCreate(['nombre' => 'Consejería Pastoral', 'escuela_id' => 2, 'nivel_id' => 2]);
          Materia::firstOrCreate(['nombre' => 'Homilética', 'escuela_id' => 2, 'nivel_id' => 2]);
          
          // Nivel Avanzado
          Materia::firstOrCreate(['nombre' => 'Plantación de Iglesias', 'escuela_id' => 2, 'nivel_id' => 3]);
          Materia::firstOrCreate(['nombre' => 'Misiones Globales', 'escuela_id' => 2, 'nivel_id' => 3]);
          Materia::firstOrCreate(['nombre' => 'Gestión de Conflictos', 'escuela_id' => 2, 'nivel_id' => 3]);
    }
}