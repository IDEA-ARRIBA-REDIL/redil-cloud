<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TipoItem; // Asegúrate que el namespace del modelo sea correcto
use Illuminate\Support\Facades\DB; // Para usar DB::table si prefieres o para transacciones

class TipoItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $this->command->info('Iniciando seeder para Tipos de Ítem...');

        // Definir los tipos de ítem a crear
        $tipos = [
            ['nombre' => 'Taller', 'descripcion' => 'Actividad práctica o ejercicio realizado por los estudiantes.'],
            ['nombre' => 'Quiz', 'descripcion' => 'Prueba corta para evaluar conocimiento sobre un tema específico.'],
            ['nombre' => 'Examen Parcial', 'descripcion' => 'Evaluación realizada durante el periodo para medir el progreso.'],
            ['nombre' => 'Examen Final', 'descripcion' => 'Evaluación completa al final del periodo académico.'],
            ['nombre' => 'Clase', 'descripcion' => 'contenido sencillo para clase sin calificación.'],
            ['nombre' => 'Exposición', 'descripcion' => 'Presentación oral realizada por el estudiante sobre un tema asignado.'],
            ['nombre' => 'Participación', 'descripcion' => 'Calificación basada en la intervención y contribución del estudiante en clase.'],
            // Puedes añadir más tipos si lo necesitas
            // ['nombre' => 'Proyecto', 'descripcion' => 'Trabajo extenso de investigación o desarrollo sobre un tema.'],
            // ['nombre' => 'Ensayo', 'descripcion' => 'Escrito donde se analiza o interpreta un tema específico.'],
        ];

        foreach ($tipos as $tipo) {
            try {
                // Usar updateOrCreate para evitar duplicados si se ejecuta el seeder múltiples veces
                // Busca por 'nombre' y actualiza o crea el registro.
                TipoItem::updateOrCreate(
                    ['nombre' => $tipo['nombre']], // Columna(s) para buscar
                    [ // Valores para crear o actualizar
                        'descripcion' => $tipo['descripcion'] ?? null, // Usar descripción si existe, sino null
                        // 'created_at' y 'updated_at' se manejan automáticamente
                    ]
                );
                $this->command->info("Tipo de Ítem '{$tipo['nombre']}' procesado/creado.");

            } catch (\Exception $e) {
                $this->command->error("Error al procesar Tipo de Ítem '{$tipo['nombre']}': " . $e->getMessage());
            }
        }

        $this->command->info('Seeder de Tipos de Ítem completado.');
    }
}
