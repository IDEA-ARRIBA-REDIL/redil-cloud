<?php

namespace Database\Seeders;

use App\Models\Informe;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InformeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Informe::firstOrCreate([
          'nombre' => 'Informe asistencia semanal a los grupos',
          'descripcion' => 'En este informe podrás visualizar la cantidad de reportes, asistencias e inasistencias de las personas a los grupos de manera global o detallada de manera semanal.',
          'link' => 'informe.informeAsistenciaSemanalGrupos',
          'activo' => false,
          'seleccione_dia_corte' => true,
          'clasificaciones' => true,
          'visible_solo_administradores' => false,
          'informe_numerico' => false,
          'tipo_informe_id' => 1,
          'add_id_a_la_url' => true,
          'nombre_boton' => 'Ver'
        ]);

        Informe::firstOrCreate([
          'nombre' => 'Informe de grupos NO reportados / NO realizados',
          'descripcion' => 'Este informe muestra un informe de los grupos no reportados o no realizados de la semana seleccionada.',
          'link' => 'informe.informeDeGruposNoReportados',
          'activo' => false,
          'seleccione_dia_corte' => false,
          'clasificaciones' => false,
          'visible_solo_administradores' => false,
          'informe_numerico' => false,
          'tipo_informe_id' => 1,
          'add_id_a_la_url' => true,
          'nombre_boton' => 'Ver'
        ]);

        Informe::firstOrCreate([
          'nombre' => 'Informe de compras',
          'descripcion' => 'Este informe muestra un informe de las compras realizadas en la plataforma.',
          'link' => 'informes.compras',
          'activo' => false,
          'seleccione_dia_corte' => false,
          'clasificaciones' => false,
          'visible_solo_administradores' => false,
          'informe_numerico' => false,
          'tipo_informe_id' => 1,
          'add_id_a_la_url' => true,
          'nombre_boton' => 'Ver'
        ]);

        Informe::firstOrCreate([
          'nombre' => 'Informe de pagos',
          'descripcion' => 'Este informe muestra un reporte detallado de los pagos y abonos realizados.',
          'link' => 'informes.pagos',
          'activo' => false,
          'seleccione_dia_corte' => false,
          'clasificaciones' => false,
          'visible_solo_administradores' => false,
          'informe_numerico' => false,
          'tipo_informe_id' => 1,
          'add_id_a_la_url' => true,
          'nombre_boton' => 'Ver'
        ]);
    }
}
