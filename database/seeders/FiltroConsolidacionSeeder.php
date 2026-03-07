<?php

namespace Database\Seeders;

use App\Models\FiltroConsolidacion;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FiltroConsolidacionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $filtro = FiltroConsolidacion::firstOrCreate(
          ['nombre' => 'Los llamados'],
          [
            'descripcion' => 'Son los que tienen la llamada de amor como finalizada',
            'orden' => 1,
            'color' => '#812121ff'
          ]
        );

        if($filtro->wasRecentlyCreated) {
            $filtro->condiciones()->attach(1, ['estado_tarea_consolidacion_id' => '3']);
        }

        $filtro2 = FiltroConsolidacion::firstOrCreate(
          ['nombre' => 'Llamadas sin gestionar'],
          [
            'descripcion' => 'Los que no tienen la llamada sin ningun registro',
            'orden' => 1,
            'color' => '#12c021ff'
          ]
        );

        if($filtro2->wasRecentlyCreated) {
            $filtro2->condiciones()->attach(1, ['estado_tarea_consolidacion_id' => '1', 'incluir' => false]);
            $filtro2->condiciones()->attach(1, ['estado_tarea_consolidacion_id' => '2', 'incluir' => false]);
            $filtro2->condiciones()->attach(1, ['estado_tarea_consolidacion_id' => '3', 'incluir' => false]);
        }


        $filtro3 = FiltroConsolidacion::firstOrCreate(
          ['nombre' => 'Llamados y visitados'],
          [
            'descripcion' => 'Son los que tiene la llamada y la visita en estado finalizado',
            'orden' => 2,
            'color' => '#c7dd00ff'
          ]
        );

        if($filtro3->wasRecentlyCreated) {
            $filtro3->condiciones()->attach(1, ['estado_tarea_consolidacion_id' => '3']);
            $filtro3->condiciones()->attach(2, ['estado_tarea_consolidacion_id' => '3']);
        }

        $filtro4 = FiltroConsolidacion::firstOrCreate(
          ['nombre' => 'Llamados y no visitados'],
          [
            'descripcion' => 'Son los que tiene la llamada finalizada y no tienen la visita en finalizada',
            'orden' => 2,
            'color' => '#610461ff'
          ]
        );

        if($filtro4->wasRecentlyCreated) {
            $filtro4->condiciones()->attach(1, ['estado_tarea_consolidacion_id' => '3']);
            $filtro4->condiciones()->attach(2, ['estado_tarea_consolidacion_id' => '3', 'incluir' => false]);
        }



    }
}
