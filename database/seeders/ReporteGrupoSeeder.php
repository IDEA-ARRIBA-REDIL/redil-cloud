<?php

namespace Database\Seeders;

use App\Models\Ofrenda;
use App\Models\ReporteGrupo;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;


class ReporteGrupoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

      // grupo 4
      $reporte = ReporteGrupo::firstOrCreate([
        'grupo_id' => 4,
        'fecha' => '2025-12-06',
      ], [
        'cantidad_asistencias' => rand(5, 15),
        'cantidad_inasistencias' => rand(5, 15),
        'tema' => 'El evangelio 2',
        'informacion_encargado_grupo' => [["id"=>6,"nombre"=> "Juan Carlos Vel","genero"=>0,"asistio"=>true]],
        'ids_grupos_ascendentes' => [1,2,4],
        'finalizado' => true,
        'sede_id' => 1
      ]);
      $reporte->usuarios()->syncWithoutDetaching([9 => ['asistio' => true]]);


      $reporte = ReporteGrupo::firstOrCreate([
        'grupo_id' => 4,
        'fecha' => '2025-12-12',
      ], [
        'cantidad_asistencias' => rand(5, 15),
        'cantidad_inasistencias' => rand(5, 15),
        'tema' => 'El evangelio 3',
        'informacion_encargado_grupo' => [["id"=>6,"nombre"=> "Juan Carlos Vel","genero"=>0,"asistio"=>true]],
        'ids_grupos_ascendentes' => [1,2,4],
        'finalizado' => false,
        'sede_id' => 1
      ]);
      $reporte->usuarios()->syncWithoutDetaching([9 => ['asistio' => true]]);

      $reporte = ReporteGrupo::firstOrCreate([
        'grupo_id' => 4,
        'fecha' => '2026-01-07',
      ], [
        'cantidad_asistencias' => rand(5, 15),
        'cantidad_inasistencias' => rand(5, 15),
        'tema' => 'El evangelio 2',
        'informacion_encargado_grupo' => [["id"=>6,"nombre"=> "Juan Carlos Vel","genero"=>0,"asistio"=>true]],
        'ids_grupos_ascendentes' => [1,2,4],
        'finalizado' => true,
        'sede_id' => 1
      ]);
      $reporte->usuarios()->syncWithoutDetaching([9 => ['asistio' => true]]);

      $reporte = ReporteGrupo::firstOrCreate([
        'grupo_id' => 4,
        'fecha' => '2026-01-14',
      ], [
        'cantidad_asistencias' => rand(5, 15),
        'cantidad_inasistencias' => rand(5, 15),
        'tema' => 'El evangelio 2',
        'informacion_encargado_grupo' => [["id"=>6,"nombre"=> "Juan Carlos Vel","genero"=>0,"asistio"=>true]],
        'ids_grupos_ascendentes' => [1,2,4],
        'finalizado' => true,
        'sede_id' => 1
      ]);
      $reporte->usuarios()->syncWithoutDetaching([9 => ['asistio' => true]]);

      $reporte = ReporteGrupo::firstOrCreate([
        'grupo_id' => 4,
        'fecha' => '2026-01-21',
      ], [
        'cantidad_asistencias' => rand(5, 15),
        'cantidad_inasistencias' => rand(5, 15),
        'tema' => 'El evangelio 2',
        'informacion_encargado_grupo' => [["id"=>6,"nombre"=> "Juan Carlos Vel","genero"=>0,"asistio"=>true]],
        'ids_grupos_ascendentes' => [1,2,4],
        'finalizado' => true,
        'sede_id' => 1
      ]);
      $reporte->usuarios()->syncWithoutDetaching([9 => ['asistio' => true]]);

      $reporte = ReporteGrupo::firstOrCreate([
        'grupo_id' => 4,
        'fecha' => '2026-01-28',
      ], [
        'tema' => '',
        'informacion_encargado_grupo' => [["id"=>6,"nombre"=> "Juan Carlos Vel","genero"=>0,"asistio"=>true]],
        'ids_grupos_ascendentes' => [1,2,4],
        'finalizado' => true,        
        'no_reporte' => true,
        'aprobado' => true,
        'motivo_no_reporte_grupo_id' => 1,
        'sede_id' => 1
      ]);
      $reporte->usuarios()->syncWithoutDetaching([9 => ['asistio' => true]]);

      // grupo 2
      $reporte = ReporteGrupo::firstOrCreate([
        'grupo_id' => 2,
        'fecha' => '2026-01-30',
      ], [
        'cantidad_asistencias' => rand(5, 15),
        'cantidad_inasistencias' => rand(5, 15),
        'tema' => 'El evangelio 2',
        'informacion_encargado_grupo' => [["id"=>6,"nombre"=> "Juan Carlos Vel","genero"=>0,"asistio"=>true]],
        'ids_grupos_ascendentes' => [1,2,4],
        'finalizado' => true,
        'sede_id' => 1
      ]);
      $reporte->usuarios()->syncWithoutDetaching([9 => ['asistio' => true]]);

      $reporte = ReporteGrupo::firstOrCreate([
        'grupo_id' => 2,
        'fecha' => '2025-12-12',
      ], [
        'cantidad_asistencias' => rand(5, 15),
        'cantidad_inasistencias' => rand(5, 15),
        'tema' => 'El evangelio 2',
        'informacion_encargado_grupo' => [["id"=>6,"nombre"=> "Juan Carlos Vel","genero"=>0,"asistio"=>true]],
        'ids_grupos_ascendentes' => [1,2,4],
        'finalizado' => false,
        'sede_id' => 1
      ]);
      $reporte->usuarios()->syncWithoutDetaching([9 => ['asistio' => true]]);

      $reporte = ReporteGrupo::firstOrCreate([
        'grupo_id' => 2,
        'fecha' => '2026-01-14',
      ], [
        'cantidad_asistencias' => rand(5, 15),
        'cantidad_inasistencias' => rand(5, 15),
        'tema' => 'El evangelio 2',
        'informacion_encargado_grupo' => [["id"=>6,"nombre"=> "Juan Carlos Vel","genero"=>0,"asistio"=>true]],
        'ids_grupos_ascendentes' => [1,2,4],
        'finalizado' => true,
        'sede_id' => 1
      ]);
      $reporte->usuarios()->syncWithoutDetaching([9 => ['asistio' => true]]);

      $reporte = ReporteGrupo::firstOrCreate([
        'grupo_id' => 2,
        'fecha' => '2026-01-21',
      ], [
        'cantidad_asistencias' => rand(5, 15),
        'cantidad_inasistencias' => rand(5, 15),
        'tema' => 'El evangelio 2',
        'informacion_encargado_grupo' => [["id"=>6,"nombre"=> "Juan Carlos Vel","genero"=>0,"asistio"=>true]],
        'ids_grupos_ascendentes' => [1,2,4],
        'finalizado' => true,
        'sede_id' => 1
      ]);
      $reporte->usuarios()->syncWithoutDetaching([9 => ['asistio' => true]]);

      $reporte = ReporteGrupo::firstOrCreate([
        'grupo_id' => 2,
        'fecha' => '2026-01-28',
      ], [
        'cantidad_asistencias' => rand(5, 15),
        'cantidad_inasistencias' => rand(5, 15),
        'tema' => 'El evangelio 2',
        'informacion_encargado_grupo' => [["id"=>6,"nombre"=> "Juan Carlos Vel","genero"=>0,"asistio"=>true]],
        'ids_grupos_ascendentes' => [1,2,4],
        'finalizado' => true,
        'sede_id' => 1
      ]);
      $reporte->usuarios()->syncWithoutDetaching([9 => ['asistio' => true]]);

      // grupo 8 
      $reporte = ReporteGrupo::firstOrCreate([
        'grupo_id' => 8,
        'fecha' => '2025-12-12',
      ], [
        'cantidad_asistencias' => 20,
        'cantidad_inasistencias' => 5,
        'tema' => 'El evangelio 2',
        'informacion_encargado_grupo' => [["id"=>6,"nombre"=> "Juan Carlos Vel","genero"=>0,"asistio"=>true]],
        'ids_grupos_ascendentes' => [1,2,8],
        'finalizado' => false,
        'sede_id' => 1
      ]);









      /*$reporte = ReporteGrupo::firstOrCreate([
        'grupo_id' => 4,
        'fecha' => '2025-07-07',
        'cantidad_asistencias' => 0,
        'cantidad_inasistencias' => 0,
        'tema' => 'El evangelio 2',
        'informacion_encargado_grupo' => [["id"=>6,"nombre"=> "Juan Carlos Vel","genero"=>0,"asistio"=>true]],
        'ids_grupos_ascendentes' => [1,2,4],
        'finalizado' => true
      ]);
      $reporte->usuarios()->attach(9, ['asistio' => true]);
      $reporte->ofrendas()->attach(1);

      $reporte = ReporteGrupo::firstOrCreate([
        'grupo_id' => 4,
        'fecha' => '2025-07-08',
        'cantidad_asistencias' => 0,
        'cantidad_inasistencias' => 0,
        'tema' => 'El evangelio 3',
        'informacion_encargado_grupo' => [["id"=>6,"nombre"=> "Juan Carlos Vel","genero"=>0,"asistio"=>true]],
        'ids_grupos_ascendentes' => [1,2,4],
        'finalizado' => true
      ]);
      $reporte->usuarios()->attach(9, ['asistio' => true]);
      $reporte->ofrendas()->attach(1);


      $reporte = ReporteGrupo::firstOrCreate([
        'grupo_id' => 4,
        'fecha' => '2025-06-30',
        'cantidad_asistencias' => 7,
        'cantidad_inasistencias' => 3,
        'tema' => 'El evangelio 1',
        'informacion_encargado_grupo' => [["id"=>6,"nombre"=> "Juan Carlos Vel","genero"=>0,"asistio"=>true]],
        'ids_grupos_ascendentes' => [1,2,4],
        'finalizado' => true
      ]);
      $reporte->usuarios()->attach(9, ['asistio' => true]);
      $reporte->ofrendas()->attach(1);*/




      // Lleno la clasificacion
     // $reporte->clasificaciones()->attach(1, ['cantidad' => 5]);
      //$reporte->clasificaciones()->attach(2, ['cantidad' => 6]);

    /*  $reporte = ReporteGrupo::firstOrCreate([
        'grupo_id' => 4,
        'fecha' => '2024-01-07',
        'cantidad_asistencias' => rand(5, 15),
        'tema' => 'El evangelio 1',
        'ids' => [5, 7, 24, 8]
      ]);
      $reporte->usuarios()->attach(9, ['asistio' => true]);

      // Lleno la clasificacion
      $reporte->clasificaciones()->attach(1, ['cantidad' => 4]);
      $reporte->clasificaciones()->attach(2, ['cantidad' => 7]);

      $reporte = ReporteGrupo::firstOrCreate([
        'grupo_id' => 4,
        'fecha' => '2024-01-14',
        'cantidad_asistencias' => rand(5, 15),
        'tema' => 'El evangelio 2',
        'ids' => [3, 63, 18, 8]
      ]);
      $reporte->usuarios()->attach(9, ['asistio' => true]);

      // Lleno la clasificacion
      $reporte->clasificaciones()->attach(1, ['cantidad' => 3]);
      $reporte->clasificaciones()->attach(2, ['cantidad' => 3]);

      $reporte = ReporteGrupo::firstOrCreate([
        'grupo_id' => 4,
        'fecha' => '2024-01-21',
        'cantidad_asistencias' => rand(5, 15),
        'tema' => 'El evangelio 3',
        'ids' => [19, 35, 6, 8]
      ]);
      $reporte->usuarios()->attach(9, ['asistio' => true]);

      // Lleno la clasificacion
      $reporte->clasificaciones()->attach(1, ['cantidad' => 2]);
      $reporte->clasificaciones()->attach(2, ['cantidad' => 3]);

      $reporte = ReporteGrupo::firstOrCreate([
        'grupo_id' => 4,
        'fecha' => '2024-01-28',
        'cantidad_asistencias' => rand(5, 15),
        'tema' => 'El evangelio 4',
        'ids' => []
      ]);
      $reporte->usuarios()->attach(9, ['asistio' => true]);

       // Lleno la clasificacion
       $reporte->clasificaciones()->attach(1, ['cantidad' => 5]);
       $reporte->clasificaciones()->attach(2, ['cantidad' => 1]);

        // Febrero 2024
        $reporte = ReporteGrupo::firstOrCreate([
          'grupo_id' => 4,
          'fecha' => '2024-02-01',
          'cantidad_asistencias' => rand(5, 15),
          'tema' => 'El evangelio 1',
          'ids' => []
        ]);
        $reporte->usuarios()->attach(9, ['asistio' => true]);

        $reporte = ReporteGrupo::firstOrCreate([
          'grupo_id' => 4,
          'fecha' => '2024-02-07',
          'cantidad_asistencias' => rand(5, 15),
          'tema' => 'El evangelio 1',
          'ids' => []
        ]);
        $reporte->usuarios()->attach(9, ['asistio' => true]);

        // Lleno la clasificacion
        $reporte->clasificaciones()->attach(1, ['cantidad' => 3]);
        $reporte->clasificaciones()->attach(2, ['cantidad' => 4]);

        $reporte = ReporteGrupo::firstOrCreate([
          'grupo_id' => 4,
          'fecha' => '2024-02-14',
          'cantidad_asistencias' => rand(5, 15),
          'tema' => 'El evangelio 2',
          'ids' => []
        ]);
        $reporte->usuarios()->attach(9, ['asistio' => true]);

        // Lleno la clasificacion
        $reporte->clasificaciones()->attach(1, ['cantidad' => 1]);
        $reporte->clasificaciones()->attach(2, ['cantidad' => 2]);

        $reporte = ReporteGrupo::firstOrCreate([
          'grupo_id' => 4,
          'fecha' => '2024-02-26',
          'cantidad_asistencias' => rand(5, 15),
          'tema' => 'El evangelio 3',
          'ids' => []
        ]);
        $reporte->usuarios()->attach(9, ['asistio' => true]);

        // Lleno la clasificacion
        $reporte->clasificaciones()->attach(1, ['cantidad' => 3]);
        $reporte->clasificaciones()->attach(2, ['cantidad' => 1]);

        $reporte = ReporteGrupo::firstOrCreate([
          'grupo_id' => 4,
          'fecha' => '2024-02-28',
          'cantidad_asistencias' => rand(5, 15),
          'tema' => 'El evangelio 4',
          'ids' => []
        ]);
        $reporte->usuarios()->attach(9, ['asistio' => true]);

        // Lleno la clasificacion
        $reporte->clasificaciones()->attach(1, ['cantidad' => 4]);
        $reporte->clasificaciones()->attach(2, ['cantidad' => 2]);

        // Marzo 2024
        $reporte = ReporteGrupo::firstOrCreate([
          'grupo_id' => 4,
          'fecha' => '2024-02-14',
          'cantidad_asistencias' => rand(5, 15),
          'tema' => 'El evangelio 2',
          'ids' => []
        ]);
        $reporte->usuarios()->attach(9, ['asistio' => false]);

        // Lleno la clasificacion
        $reporte->clasificaciones()->attach(1, ['cantidad' => 3]);
        $reporte->clasificaciones()->attach(2, ['cantidad' => 1]);

        $reporte = ReporteGrupo::firstOrCreate([
          'grupo_id' => 4,
          'fecha' => '2024-02-21',
          'cantidad_asistencias' => rand(5, 15),
          'tema' => 'El evangelio 3',
          'ids' => []
        ]);
        $reporte->usuarios()->attach(9, ['asistio' => true]);

        // Lleno la clasificacion
        $reporte->clasificaciones()->attach(1, ['cantidad' => 2]);
        $reporte->clasificaciones()->attach(2, ['cantidad' => 0]);

        // Abril 2024
        $reporte = ReporteGrupo::firstOrCreate([
          'grupo_id' => 4,
          'fecha' => '2024-04-01',
          'cantidad_asistencias' => rand(5, 15),
          'tema' => 'El retorno del Rey',
          'ids' => []
        ]);
        $reporte->usuarios()->attach(9, ['asistio' => true]);

        // Lleno la clasificacion
        $reporte->clasificaciones()->attach(1, ['cantidad' => 4]);
        $reporte->clasificaciones()->attach(2, ['cantidad' => 2]);

        // Junio 2024
        $reporte = ReporteGrupo::firstOrCreate([
          'grupo_id' => 4,
          'fecha' => '2024-06-13',
          'cantidad_asistencias' => rand(5, 15),
          'tema' => 'El retorno del Rey',
          'ids' => []
        ]);

        // Lleno la clasificacion
        $reporte->clasificaciones()->attach(1, ['cantidad' => 5]);
        $reporte->clasificaciones()->attach(2, ['cantidad' => 4]);

        $reporte = ReporteGrupo::firstOrCreate([
          'grupo_id' => 4,
          'fecha' => '2024-10-25',
          'cantidad_asistencias' => rand(5, 15),
          'tema' => 'El penultimo tema',
          'ids' => []
        ]);

        // Lleno la clasificacion
        $reporte->clasificaciones()->attach(1, ['cantidad' => 3]);
        $reporte->clasificaciones()->attach(2, ['cantidad' => 3]);

        $reporte = ReporteGrupo::firstOrCreate([
          'grupo_id' => 4,
          'fecha' => '2024-11-01',
          'cantidad_asistencias' => rand(5, 15),
          'tema' => 'El ultimo tema',
          'reporte_a_tiempo' => true,
          'ids' => []
        ]);
        $reporte->usuarios()->attach(9, ['asistio' => true]);

        // Lleno la clasificacion
        $reporte->clasificaciones()->attach(1, ['cantidad' => 6]);
        $reporte->clasificaciones()->attach(2, ['cantidad' => 1]);

        $reporte = ReporteGrupo::firstOrCreate([
          'grupo_id' => 4,
          'fecha' => '2024-11-27',
          'cantidad_asistencias' => rand(5, 15),
          'tema' => 'LLegó la navidad',
          'reporte_a_tiempo' => true,
          'ids' => []
        ]);
        $reporte->usuarios()->attach(9, ['asistio' => true]);

        // Lleno la clasificacion
        $reporte->clasificaciones()->attach(3, ['cantidad' => 7]);
        $reporte->clasificaciones()->attach(4, ['cantidad' => 6]);*/

    }
}
