<?php

namespace Database\Seeders;

use App\Models\CampoTiempoConDios;
use Illuminate\Database\Seeder;

class CampoTiempoConDiosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // campos de la seccion 1
        CampoTiempoConDios::firstOrCreate([
            'seccion_tiempo_con_dios_id' => '1',
            'tipo_campo_tiempo_con_dios_id' => '3',
            'nombre' => 'Imagen 1',
            'url_imagen' => 'Adoracion---Mi-tiempo-con-Dios-.png',
            'class' => 'mb-3 col-12 col-lg-6 text-center d-none d-sm-block',
            'orden' => 1,
        ]);

        CampoTiempoConDios::firstOrCreate([
            'seccion_tiempo_con_dios_id' => '1',
            'tipo_campo_tiempo_con_dios_id' => '4',
            'nombre' => 'Reproductor',
            'class' => 'mb-3 col-12 col-lg-6',
            'orden' => 2,
        ]);

        // campos de la seccion 2
        CampoTiempoConDios::firstOrCreate([
            'seccion_tiempo_con_dios_id' => '2',
            'tipo_campo_tiempo_con_dios_id' => '5',
            'nombre' => 'Biblia',
            'class' => 'col-12 col-lg-12',
            'name_id' => 'biblia_input',
            'orden' => 1,
        ]);

        /*CampoTiempoConDios::firstOrCreate([
            'seccion_tiempo_con_dios_id' => '2',
            'tipo_campo_tiempo_con_dios_id' => '2',
            'nombre' => 'Subtitulo 1',
            'class' => 'mt-3 col-12',
            'html' => '<hr>
            <p class="text-black my-6 fs-6">Diligencia la siguiente información</p>',
            'orden' => 2,
        ]);*/

        // campos de la seccion 3 "PAMPE"

        CampoTiempoConDios::firstOrCreate([
            'seccion_tiempo_con_dios_id' => '3',
            'tipo_campo_tiempo_con_dios_id' => '3',
            'nombre' => 'Imagen Pecados',
            'url_imagen' => 'P.png',
            'class' => 'mb-3 col-12 col-lg-5 text-center',
            'orden' => 1,
        ]);

        CampoTiempoConDios::firstOrCreate([
            'seccion_tiempo_con_dios_id' => '3',
            'tipo_campo_tiempo_con_dios_id' => '1',
            'nombre' => 'Pecados',
            'titulo' => '',
            'html' => '<h5 class="mb-1"><span class="my-auto fw-normal ms-3">Pecados y errores que debo confesar</span></h5>',
            'name_id' => 'pecados',
            'placeholder' => 'Ingrese la información',
            'requerido' => true,
            'class' => 'mb-3 col-12 col-md-7 d-flex justify-content-center flex-column align-items-start',
            'informacion_de_apoyo' => '',
            'orden' => 2,
        ]);

        // campos de la seccion 4 "PAMPE"

        CampoTiempoConDios::firstOrCreate([
            'seccion_tiempo_con_dios_id' => '4',
            'tipo_campo_tiempo_con_dios_id' => '3',
            'nombre' => 'Imagen PAMPE Actitudes',
            'url_imagen' => 'A.png',
            'class' => 'mb-3 col-12 col-lg-5 text-center',
            'orden' => 1,
        ]);

        CampoTiempoConDios::firstOrCreate([
            'seccion_tiempo_con_dios_id' => '4',
            'tipo_campo_tiempo_con_dios_id' => '1',
            'nombre' => 'Actitudes',
            'titulo' => '',
            'html' => '<h5 class="mb-1"><span class="my-auto fw-normal ms-3">Actitudes que debo adoptar</span></h5>',
            'name_id' => 'actitudes',
            'placeholder' => 'Ingrese la información',
            'requerido' => true,
            'class' => 'mb-3 col-12 col-md-7 d-flex justify-content-center flex-column align-items-start',
            'informacion_de_apoyo' => '',
            'orden' => 2,
        ]);

        // campos de la seccion 5 "PAMPE"

        CampoTiempoConDios::firstOrCreate([
            'seccion_tiempo_con_dios_id' => '5',
            'tipo_campo_tiempo_con_dios_id' => '3',
            'nombre' => 'Imagen PAMPE Mandamientos',
            'url_imagen' => 'M.png',
            'class' => 'mb-3 col-12 col-lg-5 text-center',
            'orden' => 1,
        ]);        

        CampoTiempoConDios::firstOrCreate([
            'seccion_tiempo_con_dios_id' => '5',
            'tipo_campo_tiempo_con_dios_id' => '1',
            'nombre' => 'Mandamientos',
            'titulo' => '',
            'html' => '<h5 class="mb-1"><span class="my-auto fw-normal ms-3">Mandamientos que debo obedecer</span></h5>',
            'name_id' => 'mandamientos',
            'placeholder' => 'Ingrese la información',
            'requerido' => true,
            'class' => 'mb-3 col-12 col-md-7 d-flex justify-content-center flex-column align-items-start',
            'informacion_de_apoyo' => '',
            'orden' =>  2,
        ]);

        // campos de la seccion 6 "PAMPE"

        CampoTiempoConDios::firstOrCreate([
            'seccion_tiempo_con_dios_id' => '6',
            'tipo_campo_tiempo_con_dios_id' => '3',
            'nombre' => 'Imagen PAMPE Promesas',
            'url_imagen' => 'P_1.png',
            'class' => 'mb-3 col-12 col-lg-5 text-center',
            'orden' => 1,
        ]);
        

        CampoTiempoConDios::firstOrCreate([
            'seccion_tiempo_con_dios_id' => '6',
            'tipo_campo_tiempo_con_dios_id' => '1',
            'nombre' => 'Promesas',
            'titulo' => '',
            'html' => '<h5 class="mb-1"><span class="my-auto fw-normal ms-3">Promesas que debo declarar</span></h5>',
            'name_id' => 'promesas',
            'placeholder' => 'Ingrese la información',
            'requerido' => true,
            'class' => 'mb-3 col-12 col-md-7 d-flex justify-content-center flex-column align-items-start',
            'informacion_de_apoyo' => '',
            'orden' => 2,
        ]);

        // campos de la seccion 7 "PAMPE"

        CampoTiempoConDios::firstOrCreate([
            'seccion_tiempo_con_dios_id' => '7',
            'tipo_campo_tiempo_con_dios_id' => '3',
            'nombre' => 'Imagen PAMPE Promesas',
            'url_imagen' => 'E.png',
            'class' => 'mb-3 col-12 col-lg-5 text-center',
            'orden' => 1,
        ]);

        CampoTiempoConDios::firstOrCreate([
            'seccion_tiempo_con_dios_id' => '7',
            'tipo_campo_tiempo_con_dios_id' => '1',
            'nombre' => 'Ejemplos',
            'titulo' => '',
            'html' => '<h5 class="mb-1"><span class="my-auto fw-normal ms-3">Ejemplos que quiero seguir o imitar</span></h5>',
            'name_id' => 'ejemplos',
            'placeholder' => 'Ingrese la información',
            'requerido' => true,
            'class' => 'mb-3 col-12 col-md-7 d-flex justify-content-center flex-column align-items-start',
            'informacion_de_apoyo' => '',
            'orden' => 2,
        ]);

        // campos de la seccion 8
        CampoTiempoConDios::firstOrCreate([
            'seccion_tiempo_con_dios_id' => '8',
            'tipo_campo_tiempo_con_dios_id' => '2',
            'nombre' => 'Pasos para hablar con Dios',
            'class' => 'mb-3 mt-3 col-12 col-md-12',
            'html' => '
             <h5 class=""><span style="color:#421EB7" class="text-secundary fw-bold fs-4">1.</span>  <span class="my-auto fw-normal ms-3">Pide perdón por tus pecados</span></h5>
            <h5 class=""><span style="color:#421EB7" class="text-secundary fw-bold fs-4">2.</span>  <span class="my-auto fw-normal ms-3">Confiesa cada uno de tus pecados</span></h5>
            <h5 class=""><span style="color:#421EB7" class="text-secundary fw-bold fs-4">3.</span>  <span class="my-auto fw-normal ms-3">Arrepientete y recibe perdón</span></h5>',
            'orden' => 1,
        ]);

        CampoTiempoConDios::firstOrCreate([
            'seccion_tiempo_con_dios_id' => '8',
            'tipo_campo_tiempo_con_dios_id' => '3',
            'nombre' => 'Imagen 1',
            'url_imagen' => 'Oracion---Mi-tiempo-con-Dios-.png',
            'class' => 'mb-3 col-12 col-md-12',
            'orden' => 2,
        ]);

        // campos de la seccion 9
        CampoTiempoConDios::firstOrCreate([
            'seccion_tiempo_con_dios_id' => '9',
            'tipo_campo_tiempo_con_dios_id' => '1',
            'nombre' => '¿Qué te habló Dios?',
            'html' => '<h5 class="mb-1"><span class="my-auto fw-normal">¿Qué te habló Dios?</span></h5>',
            'name_id' => 'queTeHabloDios',
            'placeholder' => 'Escribe aquí',
            'requerido' => true,
            'class' => 'mb-3 col-12 col-md-12',
            'informacion_de_apoyo' => '',
            'orden' => 1,
        ]);

        CampoTiempoConDios::firstOrCreate([
            'seccion_tiempo_con_dios_id' => '9',
            'tipo_campo_tiempo_con_dios_id' => '1',
            'nombre' => '¿Cómo cambia esto tus perspectivas?',
            'html' => '<h5 class="mb-1"><span class="my-auto fw-normal">¿Cómo cambia esto tus perspectivas?</span></h5>',
            'name_id' => 'comoCambiaEstoTusPerspectivas',
            'placeholder' => 'Escribe aquí',
            'requerido' => true,
            'class' => 'mb-3 col-12 col-md-12',
            'informacion_de_apoyo' => '',
            'orden' => 2,
        ]);

        CampoTiempoConDios::firstOrCreate([
            'seccion_tiempo_con_dios_id' => '9',
            'tipo_campo_tiempo_con_dios_id' => '1',
            'nombre' => '¿Cómo aplicas esto a tu vida diaria?',
            'html' => '<h5 class="mb-1"><span class="my-auto fw-normal">¿Cómo aplicas esto a tu vida diaria?</span></h5>',
            'name_id' => 'comoAplicasEstoATuVidaDiaria',
            'placeholder' => 'Escribe aquí',
            'requerido' => true,
            'class' => 'mb-3 col-12 col-md-12',
            'informacion_de_apoyo' => '',
            'orden' => 3,
        ]);

    }
}
