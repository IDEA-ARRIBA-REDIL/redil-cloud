<?php

namespace Database\Seeders;

use App\Models\Informe;
use Illuminate\Database\Seeder;

class InformeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Informes Estándar
        Informe::firstOrCreate(
            ['link' => 'informe.informeAsistenciaSemanalGrupos'],
            [
                'nombre' => 'Informe asistencia semanal a los grupos',
                'descripcion' => 'En este informe podrás visualizar la cantidad de reportes, asistencias e inasistencias de las personas a los grupos de manera global o detallada de manera semanal.',
                'link' => 'informe.informeAsistenciaSemanalGrupos',
                'activo' => true,
                'usa_plantilla' => false,
                'seleccione_dia_corte' => true,
                'clasificaciones' => true,
                'visible_solo_administradores' => false,
                'informe_numerico' => false,
                'tipo_informe_id' => 1,
                'add_id_a_la_url' => true,
                'nombre_boton' => 'Ver',
            ]
        );

        Informe::firstOrCreate(
            ['link' => 'informe.informeDeGruposNoReportados'],
            [
                'nombre' => 'Informe de grupos NO reportados / NO realizados',
                'descripcion' => 'Este informe muestra un informe de los grupos no reportados o no realizados de la semana seleccionada.',
                'link' => 'informe.informeDeGruposNoReportados',
                'activo' => true,
                'usa_plantilla' => false,
                'seleccione_dia_corte' => false,
                'clasificaciones' => false,
                'visible_solo_administradores' => false,
                'informe_numerico' => false,
                'tipo_informe_id' => 1,
                'add_id_a_la_url' => true,
                'nombre_boton' => 'Ver',
            ]
        );

        Informe::firstOrCreate(
            ['link' => 'informes.compras'],
            [
                'nombre' => 'Informe de compras',
                'descripcion' => 'Este informe muestra un informe de las compras realizadas en la plataforma.',
                'link' => 'informes.compras',
                'activo' => true,
                'usa_plantilla' => false,
                'seleccione_dia_corte' => false,
                'clasificaciones' => false,
                'visible_solo_administradores' => false,
                'informe_numerico' => false,
                'tipo_informe_id' => 3,
                'add_id_a_la_url' => true,
                'nombre_boton' => 'Ver',
            ]
        );

        Informe::firstOrCreate(
            ['link' => 'informes.pagos'],
            [
                'nombre' => 'Informe de pagos',
                'descripcion' => 'Este informe muestra un reporte detallado de los pagos y abonos realizados.',
                'link' => 'informes.pagos',
                'activo' => true,
                'usa_plantilla' => false,
                'seleccione_dia_corte' => false,
                'clasificaciones' => false,
                'visible_solo_administradores' => false,
                'informe_numerico' => false,
                'tipo_informe_id' => 3,
                'add_id_a_la_url' => true,
                'nombre_boton' => 'Ver',
            ]
        );

        Informe::firstOrCreate(
            ['link' => 'informes-personalizados.obreros.show'],
            [
                'nombre' => 'Informe obreros',
                'descripcion' => 'Este informe muestra el reporte de asistencia de encargados y obreros de grupos.',
                'link' => 'informes-personalizados.obreros.show',
                'activo' => true,
                'usa_plantilla' => false,
                'seleccione_dia_corte' => false,
                'clasificaciones' => false,
                'visible_solo_administradores' => false,
                'informe_numerico' => false,
                'tipo_informe_id' => 1,
                'add_id_a_la_url' => true,
                'nombre_boton' => 'Ver',
            ]
        );

        // Informes Dinámicos Basados en Plantilla
        Informe::firstOrCreate(
            ['link' => 'informes-personalizados.mega-informe.show'],
            [
                'nombre' => 'Megainforme General de Crecimiento',
                'descripcion' => 'Megainforme dinámico consolidado por sedes, pasos de crecimiento, consolidación y ministerios.',
                'link' => 'informes-personalizados.mega-informe.show',
                'activo' => true,
                'usa_plantilla' => true,
                'seleccione_dia_corte' => true,
                'clasificaciones' => true,
                'visible_solo_administradores' => false,
                'informe_numerico' => false,
                'tipo_informe_id' => 1, // Pertenece a la categoría Grupos
                'add_id_a_la_url' => true,
                'nombre_boton' => 'Configurar y Solicitar',
            ]
        );
    }
}
