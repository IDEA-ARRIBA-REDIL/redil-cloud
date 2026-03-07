<?php

namespace Database\Seeders;

use App\Models\ClasificacionAsistente;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClasificacionAsistenteSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    //1
    ClasificacionAsistente::firstOrCreate([
      'nombre' => 'Adultos que llegaron por grupo',
      'tiene_sumatoria_adicional' => true,
      'orden' => 1
    ]);

    //2
    ClasificacionAsistente::firstOrCreate([
      'nombre' => 'Adultos no creados',
      'tiene_sumatoria_adicional' => true,
      'orden' => 2
    ]);

    //3
    ClasificacionAsistente::firstOrCreate([
      'nombre' => 'Niños que llegaron por grupo',
      'tiene_sumatoria_adicional' => true,
      'orden' => 3
    ]);

    //4
    ClasificacionAsistente::firstOrCreate([
      'nombre' => 'Niños no creados',
      'tiene_sumatoria_adicional' => true,
      'orden' => 5
    ]);

    //5
    ClasificacionAsistente::firstOrCreate([
      'nombre' => 'Conversiones',
      'tiene_sumatoria_adicional' => true,
      'orden' => 4
    ]);

    // 6
    ClasificacionAsistente::firstOrCreate([
      'nombre' => 'Invitados Hombres',
      'tiene_sumatoria_adicional' => true,
      'sumar_asistencias_encargados' => true,
      'genero' => 0,
      'todos_los_asistentes' => true,
      'sumar_al_total_de_asistencias' => false,
      'clasificacion_encargado_por_clasificacion_individual' => false,
      'cargar_por_default_en_informes' => false,
    ]);

    // 7
    ClasificacionAsistente::firstOrCreate([
      'nombre' => 'Invitadas Mujeres',
      'tiene_sumatoria_adicional' => true,
      'sumar_asistencias_encargados' => true,
      'genero' => 1,
      'todos_los_asistentes' => true,
      'sumar_al_total_de_asistencias' => false,
      'clasificacion_encargado_por_clasificacion_individual' => false,
      'cargar_por_default_en_informes' => false,
    ]);

    // 8
    $niños = ClasificacionAsistente::firstOrCreate([
      'nombre' => 'Niños',
      'tiene_sumatoria_adicional' => false,
      'sumar_asistencias_encargados' => true,
      'genero' => null,
      'todos_los_asistentes' => true,
      'sumar_al_total_de_asistencias' => false,
      'clasificacion_encargado_por_clasificacion_individual' => false,
      'cargar_por_default_en_informes' => false,
    ]);

    $niños->tipoUsuarios()->attach(2, [
      'edad_minima' => 0,
      'edad_maxima' => 17
    ]);

    $niños->tipoUsuarios()->attach(3, [
      'edad_minima' => 0,
      'edad_maxima' => 17
    ]);

    $niños->tipoUsuarios()->attach(4, [
      'edad_minima' => 0,
      'edad_maxima' => 17
    ]);

    // 9
    $adultos = ClasificacionAsistente::firstOrCreate([
      'nombre' => 'Adultos',
      'tiene_sumatoria_adicional' => false,
      'sumar_asistencias_encargados' => true,
      'genero' => null,
      'todos_los_asistentes' => true,
      'sumar_al_total_de_asistencias' => false,
      'clasificacion_encargado_por_clasificacion_individual' => false,
      'cargar_por_default_en_informes' => false,
    ]);

    $adultos->tipoUsuarios()->attach(2, [
      'edad_minima' => 18,
      'edad_maxima' => 50
    ]);

    $adultos->tipoUsuarios()->attach(3, [
      'edad_minima' => 18,
      'edad_maxima' => 50
    ]);

    $adultos->tipoUsuarios()->attach(4, [
      'edad_minima' => 18,
      'edad_maxima' => 50
    ]);

    $adultos->tipoUsuarios()->attach(6, [
      'edad_minima' => 18,
      'edad_maxima' => 50
    ]);

    // 10
    $ancianos = ClasificacionAsistente::firstOrCreate([
      'nombre' => 'Ancianos',
      'tiene_sumatoria_adicional' => false,
      'sumar_asistencias_encargados' => true,
      'genero' => null,
      'todos_los_asistentes' => true,
      'sumar_al_total_de_asistencias' => false,
      'clasificacion_encargado_por_clasificacion_individual' => false,
      'cargar_por_default_en_informes' => false,
    ]);

    // Lider 2, Oveja 3, Nuevo 4.
    $ancianos->tipoUsuarios()->attach(2, [
      'edad_minima' => 51,
      'edad_maxima' => 200
    ]);

    $ancianos->tipoUsuarios()->attach(3, [
      'edad_minima' => 51,
      'edad_maxima' => 200
    ]);

    $ancianos->tipoUsuarios()->attach(4, [
      'edad_minima' => 51,
      'edad_maxima' => 200
    ]);

    // 11
    ClasificacionAsistente::firstOrCreate([
      'nombre' => 'General',
      'tiene_sumatoria_adicional' => true,
      'orden' => 1
    ]);

    $bautisadosHombres = ClasificacionAsistente::firstOrCreate([
      'nombre' => 'Mujeres Nuevas',
      'tiene_sumatoria_adicional' => false,
      'sumar_asistencias_encargados' => false,
      'genero' => 1,
      'todos_los_asistentes' => false,
      'sumar_al_total_de_asistencias' => false,
      'clasificacion_encargado_por_clasificacion_individual' => false,
      'cargar_por_default_en_informes' => false,
      'orden' => 1
    ]);

    $bautisadosHombres->tipoUsuarios()->attach(2, [
      'edad_minima' => 0,
      'fecha_ingreso_igual_fecha_reporte' => true,
      'edad_maxima' => 200
    ]);

    $bautisadosHombres->tipoUsuarios()->attach(3, [
      'edad_minima' => 0,
      'fecha_ingreso_igual_fecha_reporte' => true,
      'edad_maxima' => 200
    ]);

    $bautisadosHombres->tipoUsuarios()->attach(4, [
      'edad_minima' => 0,
      'fecha_ingreso_igual_fecha_reporte' => true,
      'edad_maxima' => 200
    ]);

    $bautisadosHombres->tipoUsuarios()->attach(6, [
      'edad_minima' => 0,
      'fecha_ingreso_igual_fecha_reporte' => true,
      'edad_maxima' => 200
    ]);
  }
}
