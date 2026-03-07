<?php

namespace Database\Seeders;

use App\Models\EstadoTareaConsolidacion;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EstadoTareaConsolidacionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
      EstadoTareaConsolidacion::firstOrCreate([
        'nombre' => 'No realizado',
        'color' => 'danger',
        'puntaje' => 0,
        'default' => true
      ]);

      EstadoTareaConsolidacion::firstOrCreate([
        'nombre' => 'En proceso',
        'color' => 'warning',
        'puntaje' => 1
      ]);

      EstadoTareaConsolidacion::firstOrCreate([
        'nombre' => 'Finalizado',
        'color' => 'success',
        'puntaje' => 2
      ]);
    }
}
