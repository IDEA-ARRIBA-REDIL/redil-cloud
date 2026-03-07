<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Escuela;       // Importar modelo Escuela
use App\Models\CorteEscuela;  // Importar modelo CorteEscuela
use Illuminate\Support\Facades\DB;

class CortesEscuelaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener todas las escuelas existentes
        $escuelas = Escuela::all();

        // Definir los cortes por defecto con porcentajes que sumen 100
        // Ejemplo para 3 cortes: 34%, 33%, 33%
        $cortesPorDefecto = [
            ['nombre' => 'Corte 1', 'orden' => 1, 'porcentaje' => 34],
            ['nombre' => 'Corte 2', 'orden' => 2, 'porcentaje' => 33],
            ['nombre' => 'Corte 3', 'orden' => 3, 'porcentaje' => 33],
            // Ajusta esto si necesitas un número diferente de cortes por defecto
            // Ejemplo para 4 cortes: 25%, 25%, 25%, 25%
            // $cortesPorDefecto = [
            //     ['nombre' => 'Corte 1', 'orden' => 1, 'porcentaje' => 25],
            //     ['nombre' => 'Corte 2', 'orden' => 2, 'porcentaje' => 25],
            //     ['nombre' => 'Corte 3', 'orden' => 3, 'porcentaje' => 25],
            //     ['nombre' => 'Corte 4', 'orden' => 4, 'porcentaje' => 25],
            // ];
        ];

        // Verificar que los porcentajes por defecto sumen 100
        $sumaPorcentajes = array_sum(array_column($cortesPorDefecto, 'porcentaje'));
        if ($sumaPorcentajes !== 100) {
            $this->command->error("Error en el Seeder: Los porcentajes por defecto definidos no suman 100 (Suma actual: {$sumaPorcentajes}). Ajusta el array \$cortesPorDefecto.");
            return; // Detener el seeder si la suma no es 100
        }


        $this->command->info('Iniciando seeder de Cortes por Escuela con porcentajes...');

        foreach ($escuelas as $escuela) {
            $this->command->info("Procesando Escuela ID: {$escuela->id} - {$escuela->nombre}");

            // Verificar si la escuela ya tiene cortes para evitar duplicados
            if ($escuela->cortesEscuela()->count() > 0) {
                $this->command->warn("-> La escuela ya tiene cortes definidos. Saltando...");
                continue; // Pasar a la siguiente escuela
            }

            // Crear los cortes por defecto para esta escuela
            $this->command->info("-> Creando cortes por defecto con porcentajes...");
            foreach ($cortesPorDefecto as $corteData) {
                try {
                    CorteEscuela::firstOrCreate([
                        'escuela_id' => $escuela->id,
                        'nombre' => $corteData['nombre'],
                        'orden' => $corteData['orden'],
                        'porcentaje' => $corteData['porcentaje'], // Añadir el porcentaje
                    ]);
                    $this->command->info("   - Corte '{$corteData['nombre']}' (Orden: {$corteData['orden']}, Porcentaje: {$corteData['porcentaje']}%) creado.");
                } catch (\Exception $e) {
                     $this->command->error("   - Error al crear corte '{$corteData['nombre']}': " . $e->getMessage());
                }
            }
        }

        $this->command->info('Seeder de Cortes por Escuela con porcentajes completado.');
    }
}
