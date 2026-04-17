<?php

namespace Database\Seeders;

use App\Models\TipoServicioActividad;
use App\Models\TipoServicioReporteReunion;
use Illuminate\Database\Seeder;

class TipoServiciosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Datos para Tipo Servicio Actividad
        $serviciosActividad = [
            ['id' => 1, 'nombre' => 'Ujier', 'created_at' => '2019-11-26 22:03:37', 'updated_at' => '2019-11-26 22:03:37'],
            ['id' => 2, 'nombre' => 'Predicador', 'created_at' => '2019-11-26 22:03:37', 'updated_at' => '2019-11-26 22:03:37'],
            ['id' => 3, 'nombre' => 'Cocina', 'created_at' => '2019-11-26 22:03:37', 'updated_at' => '2019-11-26 22:03:37'],
            ['id' => 4, 'nombre' => 'Bienvenida', 'created_at' => '2019-11-26 22:03:37', 'updated_at' => '2019-11-26 22:03:37'],
        ];

        foreach ($serviciosActividad as $servicio) {
            TipoServicioActividad::updateOrCreate(['id' => $servicio['id']], $servicio);
        }

        // Datos para Tipo Servicio Reporte Reunión
        $serviciosReunion = [
            ['id' => 1, 'nombre' => 'Predicador'],
            ['id' => 2, 'nombre' => 'Predicador de Ofrendas'],
            ['id' => 3, 'nombre' => 'Alabanza'],
            ['id' => 4, 'nombre' => 'Logística'],
            ['id' => 5, 'nombre' => 'Maestro de Niños'],
        ];

        foreach ($serviciosReunion as $servicio) {
            TipoServicioReporteReunion::updateOrCreate(['id' => $servicio['id']], $servicio);
        }
    }
}
