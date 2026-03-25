<?php

namespace Database\Seeders;

use App\Models\TipoVivienda;
use Illuminate\Database\Seeder;

class TipoViviendaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tiposDeViviendas = '[
        {"id":"1","nombre":"Propia"},
        {"id":"2","nombre":"Familiar"},
        {"id":"3","nombre":"Arriendo o Alquiler"}
      ]';

        $items = json_decode($tiposDeViviendas);
        foreach ($items as $item) {
            TipoVivienda::firstOrCreate([
                'nombre' => $item->nombre,
            ]);
        }
    }
}
