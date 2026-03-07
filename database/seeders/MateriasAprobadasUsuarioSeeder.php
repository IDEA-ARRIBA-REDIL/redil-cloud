<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MateriaAprobadaUsuario;
use App\Models\MateriaPeriodo;
// Es una buena práctica usar firstOrCreate para evitar duplicados si se corre el seeder varias veces.
use App\Models\User;

class MateriasAprobadasUsuarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buscamos el registro de MateriaPeriodo para la materia con ID 1 en el periodo con ID 3.
        $materiaPeriodo = MateriaPeriodo::where('materia_id', 1)
            ->where('periodo_id', 3)
            ->first();

        // Nos aseguramos que tanto la materia como el usuario existan.
        $user = User::find(6);

        if ($materiaPeriodo && $user) {
            // Usamos firstOrCreate para evitar crear registros duplicados si el seeder se ejecuta más de una vez.
            // Busca un registro con el mismo user_id y materia_periodo_id, si no lo encuentra, lo crea con todos los datos.
            MateriaAprobadaUsuario::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'materia_periodo_id' => $materiaPeriodo->id,
                ],
                [
                    'materia_id' => $materiaPeriodo->materia_id,
                    'periodo_id' => $materiaPeriodo->periodo_id, // <-- NUEVO: Añadimos el ID del periodo.
                    'aprobado' => true,
                    'nota_final' => 4.50, // <-- NUEVO: Añadimos una nota final de ejemplo.
                    'total_asistencias' => 9, // <-- NUEVO: Añadimos un total de asistencias de ejemplo.
                    'motivo_reprobacion' => null, // <-- NUEVO: Es null porque el estado es 'aprobado'.
                ]
            );

            $this->command->info('Registro de materia aprobada creado/verificado para el usuario de prueba.');
        } else {
            $this->command->warn('No se encontró la MateriaPeriodo (materia_id: 1, periodo_id: 3) o el Usuario (user_id: 6). No se crearon datos.');
        }
    }
}
