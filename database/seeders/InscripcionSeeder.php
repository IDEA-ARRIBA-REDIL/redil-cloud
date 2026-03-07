<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ActividadCategoria;
use App\Models\Inscripcion;
use App\Models\User;

class InscripcionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /*
        // 1. Buscamos la categoría específica de la "Actividad 2"
        // Basado en tu ActividadCategoriaSeeder, es la categoría 'Uníca' para la actividad con id = 2.
        $categoriaParaInscribir = ActividadCategoria::where('actividad_id', 2)->first();

        // Si no encontramos la categoría, detenemos el seeder para evitar errores.
        if (!$categoriaParaInscribir) {
            $this->command->error('No se encontró la categoría para la Actividad con ID 2. Asegúrate de ejecutar ActividadCategoriaSeeder primero.');
            return;
        }

        // 2. Definimos el rango de IDs de usuario que queremos inscribir.
        $userIds = range(3, 11);

        // 3. Recorremos cada ID de usuario y creamos una inscripción.
        foreach ($userIds as $userId) {
            // Verificamos si el usuario existe antes de intentar crear la inscripción.
            if (User::find($userId)) {
                Inscripcion::firstOrCreate([
                    'user_id' => $userId,
                    'actividad_categoria_id' => $categoriaParaInscribir->id,
                    'compra_id' => null, // Lo dejamos nulo por ahora, ya que no tenemos el seeder de compras.
                    'fecha' => now(), // Fecha de hoy.
                    'estado' => true, // Marcamos la inscripción como confirmada.
                    'fecha_pago' => now()->subDay(), // Simulamos que el pago se hizo ayer.
                    'json_campos_adicionales' => null,

                ]);
            } else {
                $this->command->warn("Usuario con ID {$userId} no encontrado, se omite la inscripción.");
            }
        }

        // Mensaje de éxito en la consola.
        $this->command->info('Se crearon ' . count($userIds) . ' inscripciones de prueba para la actividad "Evento Cerrado Encuentro".');
    */

    }
}
