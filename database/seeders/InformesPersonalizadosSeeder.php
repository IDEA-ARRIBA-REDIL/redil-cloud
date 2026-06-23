<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InformesPersonalizadosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Sembrar el informe personalizado: Informe de Asistencia Obreros
        DB::table('informes_personalizados')->updateOrInsert(
            ['id' => 1],
            [
                'tipo_informe_id' => 1, // Tipo listado o similar
                'nombre' => 'Informe de asistencia de los encargados',
                'descripcion' => 'Exporta el listado de todos los encargados de los grupos del ministerio seleccionado y su historial de asistencia.',
                'link' => 'informes-personalizados/obreros',
                'add_id_a_la_url' => true,
                'activo' => true,
                'seleccione_dia_corte' => true,
                'clasificaciones' => true,
            ]
        );
    }
}
