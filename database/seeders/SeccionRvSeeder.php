<?php

namespace Database\Seeders;

use App\Models\SeccionRv;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SeccionRvSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //

        SeccionRv::firstOrCreate([
            'titulo_barra' => 'Rueda de la vida',
            'tipo_seccion_id' => 1,
            'icono' => 'ti ti-cloud-heart',
            'orden' => 1,
            'titulo_steper' => 'calcula tus habitos',
            'nombre_seccion' => 'Espíritual',
            'subtitulo_seccion' => 'Llena de cada uno de los siguientes habitos',
            'descripcion' => '',
            'label_btn_bienvenida' => '',
            'label_indice_promedio' => 'Promedio',
            'label_superior_atras' => 'Volver',
            'label_superior_adelante' => 'Salir',
            'label_btn_inferior_adelante' => 'Continuar',
            'label_btn_inferior_atras' => 'Volver',
            'color' => '#008ffb',
            'promedio_minimo' => '6'
        ]);

        SeccionRv::firstOrCreate([
            'titulo_barra' => 'Rueda de la vida',
            'tipo_seccion_id' => 1,
            'icono' => 'ti ti-stretching',
            'orden' => 2,
            'titulo_steper' => 'calcula tus habitos',
            'nombre_seccion' => 'Física',
            'subtitulo_seccion' => 'Llena de cada uno de los siguientes habitos',
            'descripcion' => '',
            'label_btn_bienvenida' => '',
            'label_indice_promedio' => 'Promedio',
            'label_superior_atras' => 'Volver',
            'label_superior_adelante' => 'Salir',
            'label_btn_inferior_adelante' => 'Continuar',
            'label_btn_inferior_atras' => 'Volver',
            'color' => '#00e396',
            'promedio_minimo' => '6'
        ]);

        SeccionRv::firstOrCreate([
            'titulo_barra' => 'Rueda de la vida',
            'tipo_seccion_id' => 1,
            'icono' => 'ti ti-presentation-analytics',
            'orden' => 3,
            'titulo_steper' => 'calcula tus habitos',
            'nombre_seccion' => 'Intelectual',
            'subtitulo_seccion' => 'Llena de cada uno de los siguientes habitos',
            'descripcion' => '',
            'label_btn_bienvenida' => '',
            'label_indice_promedio' => 'Promedio',
            'label_superior_atras' => 'Volver',
            'label_superior_adelante' => 'Salir',
            'label_btn_inferior_adelante' => 'Continuar',
            'label_btn_inferior_atras' => 'Volver',
            'color' => '#feb019',
            'promedio_minimo' => '6'

        ]);

        SeccionRv::firstOrCreate([
            'titulo_barra' => 'Rueda de la vida',
            'tipo_seccion_id' => 1,
            'icono' => 'ti ti-users-group',
            'orden' => 4,
            'titulo_steper' => 'calcula tus habitos',
            'nombre_seccion' => 'Familiar',
            'subtitulo_seccion' => 'Llena de cada uno de los siguientes habitos',
            'descripcion' => '',
            'label_btn_bienvenida' => '',
            'label_indice_promedio' => 'Promedio',
            'label_superior_atras' => 'Volver',
            'label_superior_adelante' => 'Salir',
            'label_btn_inferior_adelante' => 'Continuar',
            'label_btn_inferior_atras' => 'Volver',
            'color' => '#ff4560',
            'promedio_minimo' => '6'
        ]);

        SeccionRv::firstOrCreate([
            'titulo_barra' => 'Rueda de la vida',
            'tipo_seccion_id' => 1,
            'icono' => 'ti ti-briefcase',
            'orden' => 5,
            'titulo_steper' => 'calcula tus habitos',
            'nombre_seccion' => 'Laboral y financiero',
            'subtitulo_seccion' => 'Llena de cada uno de los siguientes habitos',
            'descripcion' => '',
            'label_btn_bienvenida' => '',
            'label_indice_promedio' => 'Promedio',
            'label_superior_atras' => 'Volver',
            'label_superior_adelante' => 'Salir',
            'label_btn_inferior_adelante' => 'Continuar',
            'label_btn_inferior_atras' => 'Volver',
            'color' => '#775dd0',
            'promedio_minimo' => '6'
        ]);

        SeccionRv::firstOrCreate([
            'titulo_barra' => 'Rueda de la vida',
            'tipo_seccion_id' => 1,
            'icono' => 'ti ti-user-heart',
            'orden' => 6,
            'titulo_steper' => 'calcula tus habitos',
            'nombre_seccion' => 'Emocional',
            'subtitulo_seccion' => 'Llena de cada uno de los siguientes habitos',
            'descripcion' => '',
            'label_btn_bienvenida' => '',
            'label_indice_promedio' => 'Promedio',
            'label_superior_atras' => 'Volver',
            'label_superior_adelante' => 'Salir',
            'label_btn_inferior_adelante' => 'Continuar',
            'label_btn_inferior_atras' => 'Volver',
            'color' => '#0a330c',
            'promedio_minimo' => '6'
        ]);

        SeccionRv::firstOrCreate([
            'titulo_barra' => 'Rueda de la vida',
            'tipo_seccion_id' => 2,
            'icono' => 'ti ti-chart-pie-4',
            'orden' => 7,
            'titulo_steper' => 'calcula tus habitos',
            'nombre_seccion' => 'Resumen promedios',
            'subtitulo_seccion' => 'Revisa el total de tus promedios para continuar con tu proceso de',
            'descripcion' => '',
            'label_btn_bienvenida' => '',
            'label_indice_promedio' => 'Promedio',
            'label_superior_atras' => 'Volver',
            'label_superior_adelante' => 'Salir',
            'label_btn_inferior_adelante' => 'Continuar',
            'label_btn_inferior_atras' => 'Volver',
            'promedio_minimo' => '6'
        ]);

        SeccionRv::firstOrCreate([
            'titulo_barra' => 'Rueda de la vida',
            'tipo_seccion_id' => 3,
            'icono' => 'ti ti-chart-pie-4',
            'orden' => 8,
            'titulo_steper' => 'nuevas metas y nuevos habitos',
            'nombre_seccion' => 'Escribe tus metas y hábitos que debes desarrollar',
            'subtitulo_seccion' => '',
            'descripcion' => '',
            'label_btn_bienvenida' => '',
            'label_indice_promedio' => 'Promedio',
            'label_superior_atras' => 'Volver',
            'label_superior_adelante' => 'Salir',
            'label_btn_inferior_adelante' => 'Continuar',
            'label_btn_inferior_atras' => 'Volver',
            'promedio_minimo' => '6'
        ]);
    }
}
