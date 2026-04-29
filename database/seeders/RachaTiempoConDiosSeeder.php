<?php

namespace Database\Seeders;

use App\Models\TiempoConDios;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class RachaTiempoConDiosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Iniciando Seeder de Rachas para Tiempo con Dios...');

        // Racha de 5 días para el usuario 1 (Hoy + 4 días atrás)
        for ($i = 0; $i < 5; $i++) {
            $fecha = Carbon::today()->subDays($i)->format('Y-m-d');
            TiempoConDios::firstOrCreate([
                'user_id' => 1,
                'fecha' => $fecha,
            ]);
        }
        $this->command->info('Racha de 5 días creada/verificada para el Usuario 1.');

        // 1 día para el usuario 6 (Hoy)
        TiempoConDios::firstOrCreate([
            'user_id' => 6,
            'fecha' => Carbon::today()->format('Y-m-d'),
        ]);
        $this->command->info('Registro de hoy creado/verificado para el Usuario 6.');

        $this->command->info('Seeder de Rachas completado con éxito.');
    }
}
