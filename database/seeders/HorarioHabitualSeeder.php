<?php

namespace Database\Seeders;

use App\Models\HorarioHabitual;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HorarioHabitualSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       // Limpiar datos anteriores solo para este consejero
        HorarioHabitual::where('consejero_id', 1)->delete();

        // Crear nuevos datos de ejemplo para el Consejero con ID 1

        // Lunes: 14:00 - 18:00
        HorarioHabitual::firstOrCreate([
            'consejero_id' => 1,
            'dia_semana' => 1, // 1 = Lunes
            'hora_inicio' => '14:00:00',
            'hora_fin' => '18:00:00',
        ]);

        // Miércoles (Turno partido - mañana)
        HorarioHabitual::firstOrCreate([
            'consejero_id' => 1,
            'dia_semana' => 3, // 3 = Miércoles
            'hora_inicio' => '09:00:00',
            'hora_fin' => '12:00:00',
        ]);

        // Miércoles (Turno partido - tarde)
        HorarioHabitual::firstOrCreate([
            'consejero_id' => 1,
            'dia_semana' => 3, // 3 = Miércoles
            'hora_inicio' => '14:00:00',
            'hora_fin' => '16:00:00',
        ]);

        // Viernes: 08:00 - 12:00
        HorarioHabitual::firstOrCreate([
            'consejero_id' => 1,
            'dia_semana' => 5, // 5 = Viernes
            'hora_inicio' => '08:00:00',
            'hora_fin' => '12:00:00',
        ]);
    }

}
