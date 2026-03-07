<?php

namespace Database\Seeders;

use App\Models\TipoActividad;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TipoActividadSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    //
    TipoActividad::firstOrCreate(
      ['nombre' => 'Evento Abierto (no requiere inicio sesión)'],
      [
      'descripcion' => 'Este evento no genera inscripción, sirve solo para compras de entradas. no requiere inicio de sesion',
      'color' => '#684152',
      'es_gratuita' => TRUE,
      //'visualizada_por_todos'=>TRUE,
      //'tipo_evento'=>TRUE,
      //'estado'=>1,
      //'requiere_inscripcion'=>FALSE,
      'unica_compra' => FALSE,
      'multiples_compras' => TRUE,
      'permite_abonos' => FALSE,
      'inscripcion_parientes' => FALSE,
      //'visible'=>TRUE,
      'tipo_escuelas' => FALSE,
      'requiere_inicio_sesion' => FALSE
    ]);

    //
    TipoActividad::firstOrCreate(
      ['nombre' => 'Evento Interno con registro obligatorio con abonos (requiere inicio sesión)'],
      [
      'descripcion' => 'Este evento  es solo para miembros de la iglesia, ademas tiene restricciones que aplican solo para miembros registrados dentro de la plataforma, requiere inico sesion ',
      'color' => '#56A75d',
      //'visualizada_por_todos'=>TRUE,
      //'tipo_evento'=>TRUE,
      //'estado'=>1,
      //'requiere_inscripcion'=>TRUE,
      'unica_compra' => TRUE,
      'multiples_compras' => FALSE,
      'unica_inscripcion' => TRUE,
      'permite_abonos' => TRUE,
      'inscripcion_parientes' => TRUE,
      'unica_compra' => TRUE,
      //'visible'=>TRUE,
      'tipo_escuelas' => FALSE,
      'requiere_inicio_sesion' => TRUE
    ]);

    TipoActividad::firstOrCreate(
      ['nombre' => 'Evento Gratuito (No requiere inicio sesión)'],
      [
      'descripcion' => 'Este evento es solo para inscripción de los usuarios.',
      'color' => '#1F35A6',
      //'visualizada_por_todos'=>TRUE,
      //'tipo_evento'=>TRUE,
      //'estado'=>1,
      'requiere_inscripcion' => TRUE,
      'es_gratuita' => TRUE,
      'unica_compra' => TRUE,
      'multiples_compras' => FALSE,
      'inscripcion_parientes' => FALSE,
      'unica_inscripcion' => TRUE,
      //'visible'=>TRUE,
      'tipo_escuelas' => FALSE,
      'requiere_inicio_sesion' => FALSE
    ]);

    TipoActividad::firstOrCreate(
      ['nombre' => 'Evento Escuelas (requiere incio sesion)'],
      [
      'descripcion' => 'Este evento es para conectar las compras con el módulo de escuelas',
      'color' => '#4C1F7A',
      //'visualizada_por_todos'=>TRUE,
      //'tipo_evento'=>TRUE,
      //'estado'=>1,
      //'requiere_inscripcion'=>TRUE,
      'unica_compra' => TRUE,
      'multiples_compras' => FALSE,
      'unica_inscripcion' => TRUE,
      'multiples_inscripciones' => FALSE,
      //'visible'=>TRUE,
      'tipo_escuelas' => TRUE,
      'requiere_inicio_sesion' => TRUE
    ]);


    TipoActividad::firstOrCreate(
      ['nombre' => 'Evento Interno con registro obligatorio sin abonos (requiere inicio sesión)'],
      [
      'descripcion' => 'Este evento  es solo para miembros de la iglesia, ademas tiene restricciones que aplican solo para miembros registrados dentro de la plataforma, requiere inico sesion ',
      'color' => '#56A75d',
      //'visualizada_por_todos'=>TRUE,
      //'tipo_evento'=>TRUE,
      //'estado'=>1,
      'requiere_inscripcion' => TRUE,
      'unica_compra' => TRUE,
      'multiples_compras' => FALSE,
      'unica_inscripcion' => TRUE,
      'permite_abonos' => FALSE,
      'inscripcion_parientes' => FALSE,
      //'visible'=>TRUE,
      'tipo_escuelas' => FALSE,
      'requiere_inicio_sesion' => TRUE
    ]);
  }
}
