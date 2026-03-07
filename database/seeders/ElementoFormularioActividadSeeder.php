<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ElementoFormularioActividad;
use App\Models\OpcionesElementoFormularioActividad;

class ElementoFormularioActividadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        /// elementos tipo encabezado
        ElementoFormularioActividad::firstOrCreate([
            'titulo' => 'Seccion1',
            'tipo_elemento_id' => '1',
            'actividad_id' => '12',
            'required' => false,
            'visible' => true,
            'descripcion' => 'Este formulario es unico para pastores registrados en la plataforma, contesta el cuestionario para enviar información y verificarla, se te informará al momento de ser aprobada',
            'orden' => '1',

        ]);

        // elementos tipo respuesta corta 
        ElementoFormularioActividad::firstOrCreate([
            'titulo' => 'Nombre Completo',
            'tipo_elemento_id' => '2',
            'actividad_id' => '12',
            'required' => true,
            'visible' => true,
            'descripcion' => 'Nombre CompletO',
            'orden' => '2',
        ]);

        ElementoFormularioActividad::firstOrCreate([
            'titulo' => 'Iglesia ',
            'tipo_elemento_id' => '2',
            'actividad_id' => '12',
            'required' => false,
            'visible' => true,
            'descripcion' => 'aqui descripción que desee ',
            'orden' => '3',
        ]);

        ElementoFormularioActividad::firstOrCreate([
            'titulo' => 'Ciudad ',
            'tipo_elemento_id' => '2',
            'actividad_id' => '12',
            'required' => false,
            'visible' => true,
            'descripcion' => 'aqui descripción que desee ',
            'orden' => '4',
        ]);

        ElementoFormularioActividad::firstOrCreate([
            'titulo' => 'País ',
            'tipo_elemento_id' => '2',
            'actividad_id' => '12',
            'required' => false,
            'visible' => true,
            'descripcion' => 'aqui descripción que desee ',
            'orden' => '5',
        ]);

        /// elemento de tipo numerico
        ElementoFormularioActividad::firstOrCreate([
            'titulo' => 'Número de teléfono',
            'tipo_elemento_id' => '8',
            'actividad_id' => '12',
            'required' => false,
            'visible' => true,
            'descripcion' => 'solo numerico no acepta ningún otro tipo de caracter',
            'orden' => '6',
        ]);

        ElementoFormularioActividad::firstOrCreate([
            'titulo' => 'Identificación ',
            'tipo_elemento_id' => '8',
            'actividad_id' => '12',
            'required' => false,
            'visible' => true,
            'descripcion' => 'solo numerico no acepta ningún otro tipo de caracter',
            'orden' => '7',
        ]);

        ElementoFormularioActividad::firstOrCreate([
            'titulo' => 'Cantidad de invitados ',
            'tipo_elemento_id' => '8',
            'actividad_id' => '12',
            'required' => false,
            'visible' => true,
            'descripcion' => 'solo numerico no acepta ningún otro tipo de caracter',
            'orden' => '7',
        ]);

        ElementoFormularioActividad::firstOrCreate([
            'titulo' => 'Acreditación Pastoral',
            'tipo_elemento_id' => '10',
            'actividad_id' => '12',
            'required' => false,
            'visible' => true,
            'descripcion' => 'PDF de su certificado pastoral',
            'orden' => '8',
        ]);

        ElementoFormularioActividad::firstOrCreate([
            'titulo' => 'Día llegada',
            'tipo_elemento_id' => '7',
            'actividad_id' => '12',
            'required' => false,
            'visible' => true,
            'descripcion' => '',
            'orden' => '9',
        ]);

        ElementoFormularioActividad::firstOrCreate([
            'titulo' => 'Día salida',
            'tipo_elemento_id' => '7',
            'actividad_id' => '12',
            'required' => false,
            'visible' => true,
            'descripcion' => '',
            'orden' => '10',
        ]);

        ElementoFormularioActividad::firstOrCreate([
            'titulo' => 'Medio de transporte',
            'tipo_elemento_id' => '2',
            'actividad_id' => '12',
            'required' => false,
            'visible' => true,
            'descripcion' => 'PDF de su certificado pastoral',
            'orden' => '11',
        ]);

        ElementoFormularioActividad::firstOrCreate([
            'titulo' => 'Lugar de llegada',
            'tipo_elemento_id' => '7',
            'actividad_id' => '12',
            'required' => false,
            'visible' => true,
            'descripcion' => 'Según su medio de transporte indique terminal de llegada',
            'orden' => '12',
        ]);



        ElementoFormularioActividad::firstOrCreate([
            'titulo' => 'Empresa de transporte',
            'tipo_elemento_id' => '2',
            'actividad_id' => '12',
            'required' => false,
            'visible' => true,
            'descripcion' => 'Según su medio de transporte indique terminal de llegada',
            'orden' => '13',
        ]);

     

    }
}
