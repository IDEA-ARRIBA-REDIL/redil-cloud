<?php

namespace Database\Seeders;

use App\Models\TareaConsolidacion;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TareaConsolidacionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TareaConsolidacion::firstOrCreate(
          ['nombre' => 'LLamada de amor'],
          [
          'descripcion' => '',
          'orden' => 1,
          'default' => true
        ]);

        TareaConsolidacion::firstOrCreate(
          ['nombre' => 'Visita del pastor'],
          [
          'descripcion' => '',
          'orden' => 2,
          'default' => true
        ]);

        TareaConsolidacion::firstOrCreate(
          ['nombre' => 'Café con Jesús'],
          [
          'descripcion' => '',
          'orden' => 3,
          'default' => true
        ]);

        TareaConsolidacion::firstOrCreate(
          ['nombre' => 'Ofrecer los servicios'],
          [
          'descripcion' => '',
          'orden' => 3,
          'default' => false
        ]);
    }
}
