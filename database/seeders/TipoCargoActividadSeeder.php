<?php

namespace Database\Seeders;

use App\Models\TipoCargoActividad;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TipoCargoActividadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //

        TipoCargoActividad::firstOrCreate([
            'nombre' => 'Administrador',
            'descripcion' => 'Acceso total',
            'pestana_general' => true,
            'pestana_categorias' => true,
            'pestana_abonos' => true,
            'pestana_encargados' => true,
            'pestana_asistencias' => true,
            'pestana_multimedia' => true,
            'pestana_formulario' => true,
            'opcion_activar_inactivar' => true,

        ]);

        TipoCargoActividad::firstOrCreate([
            'nombre' => 'Registro asistencias',
            'descripcion' => 'solo asistencia',
            'pestana_general' => false,
            'pestana_categorias' => false,
            'pestana_abonos' => false,
            'pestana_encargados' => false,
            'pestana_asistencias' => true,
            'pestana_multimedia' => false,
            'pestana_formulario' => false

        ]);

        TipoCargoActividad::firstOrCreate([
            'nombre' => 'Multimedia',
            'descripcion' => 'cargar multimedia',
            'pestana_general' => false,
            'pestana_categorias' => false,
            'pestana_abonos' => false,
            'pestana_encargados' => false,
            'pestana_asistencias' => false,
            'pestana_multimedia' => true,
            'pestana_formulario' => false

        ]);

        TipoCargoActividad::firstOrCreate([
            'nombre' => 'Finanzas',
            'descripcion' => 'cargar multimedia',
            'pestana_general' => true,
            'pestana_categorias' => true,
            'pestana_abonos' => true,
            'pestana_encargados' => false,
            'pestana_asistencias' => false,
            'pestana_multimedia' => false,
            'pestana_formulario' => false,
            'opcion_activar_inactivar' => true,

        ]);

        TipoCargoActividad::firstOrCreate([
            'nombre' => 'Ujier',
            'descripcion' => 'servicio general',
            'pestana_general' => false,
            'pestana_categorias' => false,
            'pestana_abonos' => false,
            'pestana_encargados' => false,
            'pestana_asistencias' => false,
            'pestana_multimedia' => false,
            'pestana_formulario' => false

        ]);

        TipoCargoActividad::firstOrCreate([
            'nombre' => 'Maestro de niños',
            'descripcion' => 'servicio general',
            'pestana_general' => false,
            'pestana_categorias' => false,
            'pestana_abonos' => false,
            'pestana_encargados' => false,
            'pestana_asistencias' => false,
            'pestana_multimedia' => false,
            'pestana_formulario' => false

        ]);
    }
}
