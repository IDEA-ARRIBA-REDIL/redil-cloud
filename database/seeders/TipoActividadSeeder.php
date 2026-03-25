<?php

namespace Database\Seeders;

use App\Models\TipoActividad;
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
                'es_gratuita' => true,
                // 'visualizada_por_todos'=>TRUE,
                // 'tipo_evento'=>TRUE,
                // 'estado'=>1,
                // 'requiere_inscripcion'=>FALSE,
                'unica_compra' => false,
                'multiples_compras' => true,
                'permite_abonos' => false,
                'inscripcion_parientes' => false,
                // 'visible'=>TRUE,
                'tipo_escuelas' => false,
                'requiere_inicio_sesion' => false,
            ]);

        //
        TipoActividad::firstOrCreate(
            ['nombre' => 'Evento Interno con registro obligatorio con abonos (requiere inicio sesión)'],
            [
                'descripcion' => 'Este evento  es solo para miembros de la iglesia, ademas tiene restricciones que aplican solo para miembros registrados dentro de la plataforma, requiere inico sesion ',
                'color' => '#56A75d',
                // 'visualizada_por_todos'=>TRUE,
                // 'tipo_evento'=>TRUE,
                // 'estado'=>1,
                // 'requiere_inscripcion'=>TRUE,
                'unica_compra' => true,
                'multiples_compras' => false,
                'unica_inscripcion' => true,
                'permite_abonos' => true,
                'inscripcion_parientes' => true,
                'unica_compra' => true,
                // 'visible'=>TRUE,
                'tipo_escuelas' => false,
                'requiere_inicio_sesion' => true,
            ]);

        TipoActividad::firstOrCreate(
            ['nombre' => 'Evento Gratuito (No requiere inicio sesión)'],
            [
                'descripcion' => 'Este evento es solo para inscripción de los usuarios.',
                'color' => '#1F35A6',
                // 'visualizada_por_todos'=>TRUE,
                // 'tipo_evento'=>TRUE,
                // 'estado'=>1,
                'requiere_inscripcion' => true,
                'es_gratuita' => true,
                'unica_compra' => true,
                'multiples_compras' => false,
                'inscripcion_parientes' => false,
                'unica_inscripcion' => true,
                // 'visible'=>TRUE,
                'tipo_escuelas' => false,
                'requiere_inicio_sesion' => false,
            ]);

        TipoActividad::firstOrCreate(
            ['nombre' => 'Evento Escuelas (requiere incio sesion)'],
            [
                'descripcion' => 'Este evento es para conectar las compras con el módulo de escuelas',
                'color' => '#4C1F7A',
                // 'visualizada_por_todos'=>TRUE,
                // 'tipo_evento'=>TRUE,
                // 'estado'=>1,
                // 'requiere_inscripcion'=>TRUE,
                'unica_compra' => true,
                'multiples_compras' => false,
                'unica_inscripcion' => true,
                'multiples_inscripciones' => false,
                // 'visible'=>TRUE,
                'tipo_escuelas' => true,
                'requiere_inicio_sesion' => true,
            ]);

        TipoActividad::firstOrCreate(
            ['nombre' => 'Evento Interno con registro obligatorio sin abonos (requiere inicio sesión)'],
            [
                'descripcion' => 'Este evento  es solo para miembros de la iglesia, ademas tiene restricciones que aplican solo para miembros registrados dentro de la plataforma, requiere inico sesion ',
                'color' => '#56A75d',
                // 'visualizada_por_todos'=>TRUE,
                // 'tipo_evento'=>TRUE,
                // 'estado'=>1,
                'requiere_inscripcion' => true,
                'unica_compra' => true,
                'multiples_compras' => false,
                'unica_inscripcion' => true,
                'permite_abonos' => false,
                'inscripcion_parientes' => false,
                // 'visible'=>TRUE,
                'tipo_escuelas' => false,
                'requiere_inicio_sesion' => true,
            ]);
    }
}
