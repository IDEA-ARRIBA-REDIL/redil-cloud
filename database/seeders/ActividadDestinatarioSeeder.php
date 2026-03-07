<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ActividadDestinatario;

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
          'actividad_id'=> 1,
          'destinatario_id'=>1,
        ]);

    }
}
