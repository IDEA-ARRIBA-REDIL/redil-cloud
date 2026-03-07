<?php

namespace Database\Seeders;

use App\Models\CampoSeccionRv;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CampoSeccionRvSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        CampoSeccionRv::firstOrCreate([
            'nombre' => 'Oración',
            'abierto' => false,
            'seccion_rv_id' => 1,
            'orden' => 1,
            'color' => '#008ffb'

        ]);

        //
        CampoSeccionRv::firstOrCreate([
            'nombre' => 'Meditación biblica',
            'abierto' => false,
            'seccion_rv_id' => 1,
            'orden' => 2,
            'color' => '#00e396',

        ]);

        //
        CampoSeccionRv::firstOrCreate([
            'nombre' => 'Ayuno',
            'abierto' => false,
            'seccion_rv_id' => 1,
            'orden' => 3,
            'color' => '#feb019'

        ]);

        //
        CampoSeccionRv::firstOrCreate([
            'nombre' => 'Adoración',
            'abierto' => false,
            'seccion_rv_id' => 1,
            'orden' => 4,
            'color' => '#ff4560'

        ]);

        //
        CampoSeccionRv::firstOrCreate([
            'nombre' => 'Congregarme',
            'abierto' => false,
            'seccion_rv_id' => 1,
            'orden' => 5,
            'color' => '#775dd0'

        ]);

        //
        CampoSeccionRv::firstOrCreate([
            'nombre' => '',
            'abierto' => true,
            'seccion_rv_id' => 1,
            'orden' => 6,
            'color' => '#0a330c'

        ]);

        /// bloque dos 

        CampoSeccionRv::firstOrCreate([
            'nombre' => 'Alimentación',
            'abierto' => false,
            'seccion_rv_id' => 2,
            'orden' => 1,
            'color' => '#008ffb'
        ]);

        //
        CampoSeccionRv::firstOrCreate([
            'nombre' => 'Ejercicio',
            'abierto' => false,
            'seccion_rv_id' => 2,
            'orden' => 2,
            'color' => '#00e396',
        ]);

        //
        CampoSeccionRv::firstOrCreate([
            'nombre' => 'Revisión médica',
            'abierto' => false,
            'seccion_rv_id' => 2,
            'orden' => 3,
            'color' => '#feb019'
        ]);

        //
        CampoSeccionRv::firstOrCreate([
            'nombre' => 'Reposo',
            'abierto' => false,
            'seccion_rv_id' => 2,
            'orden' => 4,
            'color' => '#ff4560'
        ]);

        //
        CampoSeccionRv::firstOrCreate([
            'nombre' => 'Aseo e higiene',
            'abierto' => false,
            'seccion_rv_id' => 2,
            'orden' => 5,
            'color' => '#775dd0'
        ]);

        //
        CampoSeccionRv::firstOrCreate([
            'nombre' => '',
            'abierto' => true,
            'seccion_rv_id' => 2,
            'orden' => 6,
            'color' => '#0a330c'
        ]);

        //
        CampoSeccionRv::firstOrCreate([
            'nombre' => 'Lectura',
            'abierto' => false,
            'seccion_rv_id' => 3,
            'orden' => 1,
            'color' => '#008ffb'
        ]);

        //
        CampoSeccionRv::firstOrCreate([
            'nombre' => 'Formación (cursos, talleres etc)',
            'abierto' => false,
            'seccion_rv_id' => 3,
            'orden' => 2,
            'color' => '#00e396',
        ]);

        //
        CampoSeccionRv::firstOrCreate([
            'nombre' => 'Uso mis habilidades para servir',
            'abierto' => false,
            'seccion_rv_id' => 3,
            'orden' => 3,
            'color' => '#feb019'
        ]);

        //
        CampoSeccionRv::firstOrCreate([
            'nombre' => 'Asisto a actividades culturales',
            'abierto' => false,
            'seccion_rv_id' => 3,
            'orden' => 4,
            'color' => '#ff4560'
        ]);
        //
        CampoSeccionRv::firstOrCreate([
            'nombre' => 'Relaciones que ayudan a mi crecimiento personal',
            'abierto' => false,
            'seccion_rv_id' => 3,
            'orden' => 5,
            'color' => '#775dd0'
        ]);

        //
        CampoSeccionRv::firstOrCreate([
            'nombre' => '',
            'abierto' => true,
            'seccion_rv_id' => 3,
            'orden' => 6,
            'color' => '#0a330c'
        ]);

        //
        CampoSeccionRv::firstOrCreate([
            'nombre' => 'Tiempo familiar',
            'abierto' => false,
            'seccion_rv_id' => 4,
            'orden' => 1,
            'color' => '#008ffb'
        ]);

        //
        CampoSeccionRv::firstOrCreate([
            'nombre' => 'Altar familiar',
            'abierto' => false,
            'seccion_rv_id' => 4,
            'orden' => 2,
            'color' => '#00e396',
        ]);

        //
        CampoSeccionRv::firstOrCreate([
            'nombre' => 'Perdón y manejo de conflictos',
            'abierto' => false,
            'seccion_rv_id' => 4,
            'orden' => 3,
            'color' => '#feb019'
        ]);

        //
        CampoSeccionRv::firstOrCreate([
            'nombre' => 'Comuniación asertiva',
            'abierto' => false,
            'seccion_rv_id' => 4,
            'orden' => 4,
            'color' => '#ff4560'
        ]);

        //
        CampoSeccionRv::firstOrCreate([
            'nombre' => 'Relación con familia extendida',
            'abierto' => false,
            'seccion_rv_id' => 4,
            'orden' => 5,
            'color' => '#775dd0'
        ]);


        //
        CampoSeccionRv::firstOrCreate([
            'nombre' => '',
            'abierto' => true,
            'seccion_rv_id' => 4,
            'orden' => 6,
            'color' => '#0a330c'
        ]);

        //
        CampoSeccionRv::firstOrCreate([
            'nombre' => 'Presupuesto personal',
            'abierto' => false,
            'seccion_rv_id' => 5,
            'orden' => 1,
            'color' => '#008ffb'
        ]);

        //
        CampoSeccionRv::firstOrCreate([
            'nombre' => 'Ahorro',
            'abierto' => false,
            'seccion_rv_id' => 5,
            'orden' => 2,
            'color' => '#00e396',
        ]);

        //
        CampoSeccionRv::firstOrCreate([
            'nombre' => 'Inversión',
            'abierto' => false,
            'seccion_rv_id' => 5,
            'orden' => 3,
            'color' => '#feb019'
        ]);

        //
        CampoSeccionRv::firstOrCreate([
            'nombre' => 'Deudas',
            'abierto' => false,
            'seccion_rv_id' => 5,
            'orden' => 4,
            'color' => '#ff4560'
        ]);

        //
        CampoSeccionRv::firstOrCreate([
            'nombre' => 'Doy con alegría y generosidad',
            'abierto' => false,
            'seccion_rv_id' => 5,
            'orden' => 5,
            'color' => '#775dd0'
        ]);

        //
        CampoSeccionRv::firstOrCreate([
            'nombre' => '',
            'abierto' => true,
            'seccion_rv_id' => 5,
            'orden' => 6,
            'color' => '#0a330c'
        ]);

        //
        CampoSeccionRv::firstOrCreate([
            'nombre' => 'Me acepto como soy',
            'abierto' => false,
            'seccion_rv_id' => 6,
            'orden' => 1,
            'color' => '#008ffb'
        ]);

        //
        CampoSeccionRv::firstOrCreate([
            'nombre' => 'manejo de frustraciones',
            'abierto' => false,
            'seccion_rv_id' => 6,
            'orden' => 2,
            'color' => '#00e396',
        ]);

        //
        CampoSeccionRv::firstOrCreate([
            'nombre' => 'Separo las emociones de mis decisiones',
            'abierto' => false,
            'seccion_rv_id' => 6,
            'orden' => 3,
            'color' => '#feb019'
        ]);

        //
        CampoSeccionRv::firstOrCreate([
            'nombre' => 'Soy lento para la ira y rápido para perdonar',
            'abierto' => false,
            'seccion_rv_id' => 6,
            'orden' => 4,
            'color' => '#ff4560'
        ]);

        //
        CampoSeccionRv::firstOrCreate([
            'nombre' => 'Mi cáracter refleja al Espíritu Santo',
            'abierto' => false,
            'seccion_rv_id' => 6,
            'orden' => 5,
            'color' => '#775dd0'
        ]);
        //
        CampoSeccionRv::firstOrCreate([
            'nombre' => '',
            'abierto' => true,
            'seccion_rv_id' => 6,
            'orden' => 6,
            'color' => '#0a330c'
        ]);
    }
}
