<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; // Asegúrate de importar el Facade DB
use Carbon\Carbon; // Opcional, útil si necesitas manipular fechas

class CalificacionesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Opcional: Si quieres vaciar la tabla antes de llenarla
        // Ten cuidado, esto borrará todos los datos existentes en la tabla 'calificaciones'
        // DB::table('calificaciones')->truncate();

        $calificaciones = [
            [
                'id' => 3,
                'nombre' => 'Insuficiente',
                'nota_minima' => 0.0,
                'nota_maxima' => 40.0,
                'visible_en_filtro' => false,
                'sistema_calificacion_id' => 1,
                'created_at' => '2016-01-01 00:00:00',
                'aprobado' => false,
                'updated_at' => null,
            ],
            [
                'id' => 5,
                'nombre' => 'No aprobó ',
                'nota_minima' => 0.0,
                'nota_maxima' => 69.9,
                'visible_en_filtro' => false,
                'sistema_calificacion_id' => 2,
                'created_at' => '2016-01-01 00:00:00',
                'aprobado' => false,
                'updated_at' => null,
            ],
            [
                'id' => 4,
                'nombre' => 'Aprobó ',
                'nota_minima' => 70.0,
                'nota_maxima' => 100.0,
                'visible_en_filtro' => false,
                'sistema_calificacion_id' => 2,
                'created_at' => '2016-01-01 00:00:00',
                'aprobado' => true,
                'updated_at' => null,
            ],
            [
                'id' => 2,
                'nombre' => 'Aceptable',
                'nota_minima' => 10.1,
                'nota_maxima' => 80.0,
                'visible_en_filtro' => false,
                'sistema_calificacion_id' => 1,
                'created_at' => '2016-01-01 00:00:00',
                'aprobado' => true,
                'updated_at' => null,
            ],
            [
                'id' => 1,
                'nombre' => 'Excelente',
                'nota_minima' => 80.1,
                'nota_maxima' => 100.0,
                'visible_en_filtro' => false,
                'sistema_calificacion_id' => 1,
                'created_at' => '2016-01-01 00:00:00',
                'aprobado' => true,
                'updated_at' => null,
            ],
            [
                'id' => 6,
                'nombre' => 'No Aprobó',
                'nota_minima' => 0.0,
                'nota_maxima' => 2.9,
                'visible_en_filtro' => false,
                'sistema_calificacion_id' => 3,
                'created_at' => '2013-09-26 22:03:37',
                'aprobado' => false,
                'updated_at' => null,
            ],
            [
                'id' => 7,
                'nombre' => 'Aprobó',
                'nota_minima' => 3.0,
                'nota_maxima' => 5.0,
                'visible_en_filtro' => false,
                'sistema_calificacion_id' => 3,
                'created_at' => '2013-09-26 22:03:37',
                'aprobado' => true,
                'updated_at' => '2018-10-18 22:34:37',
            ],
        ];

        foreach ($calificaciones as $calificacion) {
            DB::table('calificaciones')->updateOrInsert(
                ['id' => $calificacion['id']],
                $calificacion
            );
        }
    }
}
