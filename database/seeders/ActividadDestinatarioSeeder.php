<?php

namespace Database\Seeders;

use App\Models\ActividadDestinatario;
use Illuminate\Database\Seeder;

class ActividadDestinatarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        // Id: 1
        ActividadDestinatario::firstOrCreate([
            'actividad_id' => 1,
            'destinatario_id' => 1,
        ]);

    }
}
