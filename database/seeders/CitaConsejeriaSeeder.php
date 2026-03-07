<?php

namespace Database\Seeders;

use App\Models\CitaConsejeria;
use Illuminate\Database\Seeder;

class CitaConsejeriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CitaConsejeria::firstOrCreate([
            'user_id' => 11,
            'consejero_id' => 1,
            'tipo_consejeria_id' => 4,
            'medio' => 1,
            'fecha_hora_inicio' => '2025-12-05 10:00:00',
            'fecha_hora_fin' => '2025-12-05 10:45:00',
            'notas_paciente' => 'Hola porfa necesito su ayuda',
            'concluida' => false,
        ]);

        CitaConsejeria::firstOrCreate([
            'user_id' => 11,
            'consejero_id' => 1,
            'tipo_consejeria_id' => 4,
            'medio' => 1,
            'fecha_hora_inicio' => '2025-12-06 11:00:00',
            'fecha_hora_fin' => '2025-12-06 11:45:00',
            'notas_paciente' => 'Hola porfa necesito su ayuda',
            'concluida' => false,
        ]);
    }
}
