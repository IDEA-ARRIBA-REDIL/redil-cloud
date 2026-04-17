<?php

namespace Database\Seeders;

use App\Models\TipoGrupo;
use Illuminate\Database\Seeder;

class TipoGrupoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tipoGrupo1 = TipoGrupo::firstOrCreate(
            ['nombre' => 'Célula de liderazgo Sup Auxiliar'],
            [
                'nombre_plural' => 'Células de liderazgo Sup Auxiliar',
                'descripcion' => '',
                'contiene_servidores' => true,
                'imagen' => '',
                'geo_icono' => 'grupo-verde.png',
                'seguimiento_actividad' => 1,
                'enviar_mensaje_bienvenida' => 1,
                'mensaje_bienvenida' => 'Ahora ya eres un líder, que bendición que puedas servir al señor desde los grupos abiertos',
                'metros_cobertura' => 5000,
                'color' => '#c12',
                'automatizacion_tipo_usuario_id' => 2,
                'horas_disponiblidad_link_asistencia' => 2,
                'estado' => true,
            ]);

        // Automatizaciones de los pasos 1 y 2
        $tipoGrupo1->automatizacionesPasosCrecimiento()->sync([
            1 => ['estado_por_defecto' => 3, 'descripcion_por_defecto' => 'Hola, es automatizo este paso.'],
            2 => ['estado_por_defecto' => 2, 'descripcion_por_defecto' => 'Hola, es automatizo este paso.'],
        ], false); // false = no detach existing, or use syncWithoutDetaching if preferred. But sync with detach=false is basically attach without duplicate if ID is same? No, attach duplicates. syncWithoutDetaching is better.
        // Actually, distinct pivot usage usually implies syncWithoutDetaching or check keys.
        // Users instructions were "firstOrCreate". For pivots, syncWithoutDetaching is safe.
        // But keeping it simple: firstOrCreate the main model.
        // For pivot: "->attach" creates duplicates if run twice. I will change to syncWithoutDetaching or check existence?
        // The user didn't explicitly ask for pivot refactor here but "seeders idempotency".
        // I will use syncWithoutDetaching for relations to be safe.

        $tipoGrupo1->automatizacionesPasosCrecimiento()->syncWithoutDetaching([
            1 => ['estado_por_defecto' => 3, 'descripcion_por_defecto' => 'Hola, es automatizo este paso.'],
            2 => ['estado_por_defecto' => 2, 'descripcion_por_defecto' => 'Hola, es automatizo este paso.'],
        ]);

        $tipoGrupo2 = TipoGrupo::firstOrCreate(
            ['nombre' => 'Grupo de crecimiento'],
            [
                'nombre_plural' => 'Grupos de crecimiento',
                'descripcion' => '',
                'contiene_servidores' => true,
                'imagen' => '',
                'geo_icono' => 'grupo-rojo.png',
                'seguimiento_actividad' => 1,
                'enviar_mensaje_bienvenida' => 1,
                'mensaje_bienvenida' => 'Ahora ya eres un líder, que bendición que puedas servir al señor desde los grupos cerrados',
                'metros_cobertura' => 1000,
                'color' => '#ed2',
                'tipo_evangelistico' => true,
                'registrar_inasistencia' => false,
                'inasistencia_obligatoria' => false,
                'ingresos_individuales_discipulos' => false,
                'ingresos_individuales_lideres' => false,
                'cantidad_maxima_reportes_semana' => 1,
                'horas_disponiblidad_link_asistencia' => 2,
                'estado' => true,
            ]);

        // clasificaciones asistentes
        $tipoGrupo2->clasificacionAsistentes()->syncWithoutDetaching([1, 2, 3, 4, 5]);

        // tipo ofrendas del grupo
        $tipoGrupo2->tiposOfrendas()->syncWithoutDetaching([5, 6, 2, 4]);

        $tipoGrupo3 = TipoGrupo::firstOrCreate(
            ['nombre' => 'Grupo Warriors'],
            [
                'nombre_plural' => 'Grupos Warriors',
                'descripcion' => 'Esta es la descripción',
                'imagen' => '',
                'geo_icono' => 'grupo-azul-claro.png',
                'seguimiento_actividad' => 0,
                'enviar_mensaje_bienvenida' => 1,
                'mensaje_bienvenida' => 'Ahora ya eres un líder, que bendición que puedas servir al señor desde los grupos inasignables',
                'metros_cobertura' => 1000,
                'color' => '#ed2',
                'tipo_evangelistico' => true,
                'registrar_inasistencia' => false,
                'inasistencia_obligatoria' => false,
                'ingresos_individuales_discipulos' => false,
                'ingresos_individuales_lideres' => false,
                'cantidad_maxima_reportes_semana' => 1,
                'horas_disponiblidad_link_asistencia' => 2,
                'estado' => true,
            ]);

        // clasificaciones asistentes
        $tipoGrupo3->clasificacionAsistentes()->syncWithoutDetaching([1, 2, 3, 4, 5]);

        // tipo ofrendas del grupo
        $tipoGrupo3->tiposOfrendas()->syncWithoutDetaching([5, 6, 2, 4]);

        $tipoGrupo4 = TipoGrupo::firstOrCreate(
            ['nombre' => 'Grupo relevo'],
            [
                'nombre_plural' => 'Grupos relevo',
                'descripcion' => 'Esta es la descripción',
                'imagen' => '',
                'geo_icono' => 'grupo-vinotinto.png',
                'enviar_mensaje_bienvenida' => 1,
                'mensaje_bienvenida' => 'Ahora ya eres un líder, que bendición que puedas servir al señor desde los grupos eliminables',
                'metros_cobertura' => 1000,
                'color' => '#ed2',
                'tipo_evangelistico' => true,
                'registrar_inasistencia' => false,
                'inasistencia_obligatoria' => false,
                'ingresos_individuales_discipulos' => false,
                'ingresos_individuales_lideres' => false,
                'cantidad_maxima_reportes_semana' => 1,
                'horas_disponiblidad_link_asistencia' => 2,
                'estado' => true,
            ]);

        // clasificaciones asistentes
        $tipoGrupo4->clasificacionAsistentes()->syncWithoutDetaching([1, 2, 3, 4, 5]);

        // tipo ofrendas del grupo
        $tipoGrupo4->tiposOfrendas()->syncWithoutDetaching([5, 6, 2, 4]);

        $tipoGrupo5 = TipoGrupo::firstOrCreate(
            ['nombre' => 'Célula de liderazgo Coord. relevo'],
            [
                'nombre_plural' => 'Células de liderazgo Coord. relevo',
                'descripcion' => 'Esta es la descripción',
                'imagen' => '',
                'geo_icono' => 'grupo-vinotinto.png',
                'enviar_mensaje_bienvenida' => 1,
                'mensaje_bienvenida' => 'Ahora ya eres un líder, que bendición que puedas servir al señor desde los grupos eliminables',
                'metros_cobertura' => 1000,
                'color' => '#ed2',
                'horas_disponiblidad_link_asistencia' => 2,
                'estado' => true,
            ]);

        $tipoGrupo6 = TipoGrupo::firstOrCreate(
            ['nombre' => 'Célula de liderazgo Sup General'],
            [
                'nombre_plural' => 'Células de liderazgo Sup General',
                'descripcion' => 'Esta es la descripción',
                'imagen' => '',
                'geo_icono' => 'grupo-vinotinto.png',
                'enviar_mensaje_bienvenida' => 1,
                'mensaje_bienvenida' => 'Ahora ya eres un líder, que bendición que puedas servir al señor desde los grupos eliminables',
                'metros_cobertura' => 1000,
                'color' => '#ed2',
                'horas_disponiblidad_link_asistencia' => 2,
                'estado' => true,
            ]);

        $tipoGrupo7 = TipoGrupo::firstOrCreate(
            ['nombre' => 'Célula de liderazgo pastor'],
            [
                'nombre_plural' => 'Células de liderazgo pastor',
                'descripcion' => 'Esta es la descripción',
                'imagen' => '',
                'geo_icono' => 'grupo-vinotinto.png',
                'enviar_mensaje_bienvenida' => 1,
                'mensaje_bienvenida' => 'Ahora ya eres un líder, que bendición que puedas servir al señor desde los grupos eliminables',
                'metros_cobertura' => 1000,
                'color' => '#ed2',
                'horas_disponiblidad_link_asistencia' => 2,
                'estado' => true,
            ]);

        $tipoGrupo8 = TipoGrupo::firstOrCreate(
            ['nombre' => 'Grupo de área'],
            [
                'nombre_plural' => 'Grupos de área',
                'descripcion' => 'Esta es la descripción',
                'imagen' => '',
                'geo_icono' => 'grupo-vinotinto.png',
                'enviar_mensaje_bienvenida' => 1,
                'mensaje_bienvenida' => 'Ahora ya eres un líder, que bendición que puedas servir al señor desde los grupos eliminables',
                'metros_cobertura' => 1000,
                'color' => '#ed2',
                'horas_disponiblidad_link_asistencia' => 2,
                'estado' => true,
            ]);

        $tipoGrupo9 = TipoGrupo::firstOrCreate(
            ['nombre' => 'Grupo de región'],
            [
                'nombre_plural' => 'Grupos de región',
                'descripcion' => 'Esta es la descripción',
                'imagen' => '',
                'geo_icono' => 'grupo-vinotinto.png',
                'enviar_mensaje_bienvenida' => 1,
                'mensaje_bienvenida' => 'Ahora ya eres un líder, que bendición que puedas servir al señor desde los grupos eliminables',
                'metros_cobertura' => 1000,
                'color' => '#ed2',
                'horas_disponiblidad_link_asistencia' => 2,
                'estado' => true,
            ]);

        $tipoGrupo10 = TipoGrupo::firstOrCreate(
            ['nombre' => 'Grupo principal'],
            [
                'nombre_plural' => 'Grupos principales',
                'descripcion' => 'Esta es la descripción',
                'imagen' => '',
                'geo_icono' => 'grupo-vinotinto.png',
                'enviar_mensaje_bienvenida' => 1,
                'mensaje_bienvenida' => 'Ahora ya eres un líder, que bendición que puedas servir al señor desde los grupos eliminables',
                'metros_cobertura' => 1000,
                'color' => '#ed2',
                'horas_disponiblidad_link_asistencia' => 2,
                'estado' => true,
            ]);

        $tipoGrupo11 = TipoGrupo::firstOrCreate(
            ['nombre' => 'Grupo de Crecimiento de deportistas'],
            [
                'nombre_plural' => 'Grupos de Crecimiento de deportistas',
                'descripcion' => 'Esta es la descripción',
                'imagen' => '',
                'geo_icono' => 'grupo-azul-claro.png',
                'seguimiento_actividad' => 0,
                'enviar_mensaje_bienvenida' => 1,
                'mensaje_bienvenida' => 'Ahora ya eres un líder, que bendición que puedas servir al señor desde los grupos inasignables',
                'metros_cobertura' => 1000,
                'color' => '#ed2',
                'tipo_evangelistico' => true,
                'registrar_inasistencia' => false,
                'inasistencia_obligatoria' => false,
                'ingresos_individuales_discipulos' => false,
                'ingresos_individuales_lideres' => false,
                'cantidad_maxima_reportes_semana' => 1,
                'horas_disponiblidad_link_asistencia' => 2,
                'estado' => true,
            ]);

        $tipoGrupo11->clasificacionAsistentes()->syncWithoutDetaching([1, 2, 3, 4, 5]);
        $tipoGrupo11->tiposOfrendas()->syncWithoutDetaching([5, 6, 2, 4]);

        $tipoGrupo12 = TipoGrupo::firstOrCreate(
            ['nombre' => 'Grupo de Crecimiento de Empresarios'],
            [
                'nombre_plural' => 'Grupos de Crecimiento de Empresarios',
                'descripcion' => 'Esta es la descripción',
                'imagen' => '',
                'geo_icono' => 'grupo-azul-claro.png',
                'seguimiento_actividad' => 0,
                'enviar_mensaje_bienvenida' => 1,
                'mensaje_bienvenida' => 'Ahora ya eres un líder, que bendición que puedas servir al señor desde los grupos inasignables',
                'metros_cobertura' => 1000,
                'color' => '#ed2',
                'tipo_evangelistico' => true,
                'registrar_inasistencia' => false,
                'inasistencia_obligatoria' => false,
                'ingresos_individuales_discipulos' => false,
                'ingresos_individuales_lideres' => false,
                'cantidad_maxima_reportes_semana' => 1,
                'horas_disponiblidad_link_asistencia' => 2,
                'estado' => true,
            ]);

        $tipoGrupo12->clasificacionAsistentes()->syncWithoutDetaching([1, 2, 3, 4, 5]);
        $tipoGrupo12->tiposOfrendas()->syncWithoutDetaching([5, 6, 2, 4]);

        $tipoGrupo13 = TipoGrupo::firstOrCreate(
            ['nombre' => 'Grupo de Crecimiento de Mujeres'],
            [
                'nombre_plural' => 'Grupos de Crecimiento de Mujeres',
                'descripcion' => 'Esta es la descripción',
                'imagen' => '',
                'geo_icono' => 'grupo-azul-claro.png',
                'seguimiento_actividad' => 0,
                'enviar_mensaje_bienvenida' => 1,
                'mensaje_bienvenida' => 'Ahora ya eres un líder, que bendición que puedas servir al señor desde los grupos inasignables',
                'metros_cobertura' => 1000,
                'color' => '#ed2',
                'tipo_evangelistico' => true,
                'registrar_inasistencia' => false,
                'inasistencia_obligatoria' => false,
                'ingresos_individuales_discipulos' => false,
                'ingresos_individuales_lideres' => false,
                'cantidad_maxima_reportes_semana' => 1,
                'horas_disponiblidad_link_asistencia' => 2,
                'estado' => true,
            ]);

        $tipoGrupo13->clasificacionAsistentes()->syncWithoutDetaching([1, 2, 3, 4, 5]);
        $tipoGrupo13->tiposOfrendas()->syncWithoutDetaching([5, 6, 2, 4]);

        $tipoGrupo14 = TipoGrupo::firstOrCreate(
            ['nombre' => 'Grupo de Crecimiento de Hombres'],
            [
                'nombre_plural' => 'Grupos de Crecimiento de Hombres',
                'descripcion' => 'Esta es la descripción',
                'imagen' => '',
                'geo_icono' => 'grupo-azul-claro.png',
                'seguimiento_actividad' => 0,
                'enviar_mensaje_bienvenida' => 1,
                'mensaje_bienvenida' => 'Ahora ya eres un líder, que bendición que puedas servir al señor desde los grupos inasignables',
                'metros_cobertura' => 1000,
                'color' => '#ed2',
                'tipo_evangelistico' => true,
                'registrar_inasistencia' => false,
                'inasistencia_obligatoria' => false,
                'ingresos_individuales_discipulos' => false,
                'ingresos_individuales_lideres' => false,
                'cantidad_maxima_reportes_semana' => 1,
                'horas_disponiblidad_link_asistencia' => 2,
                'estado' => true,
            ]);

        $tipoGrupo14->clasificacionAsistentes()->syncWithoutDetaching([1, 2, 3, 4, 5]);
        $tipoGrupo14->tiposOfrendas()->syncWithoutDetaching([5, 6, 2, 4]);

        $tipoGrupo15 = TipoGrupo::firstOrCreate(
            ['nombre' => 'Grupo de Crecimiento de Años Dorados'],
            [
                'nombre_plural' => 'Grupos de Crecimiento de Años Dorados',
                'descripcion' => 'Esta es la descripción',
                'imagen' => '',
                'geo_icono' => 'grupo-azul-claro.png',
                'seguimiento_actividad' => 0,
                'enviar_mensaje_bienvenida' => 1,
                'mensaje_bienvenida' => 'Ahora ya eres un líder, que bendición que puedas servir al señor desde los grupos inasignables',
                'metros_cobertura' => 1000,
                'color' => '#ed2',
                'tipo_evangelistico' => true,
                'registrar_inasistencia' => false,
                'inasistencia_obligatoria' => false,
                'ingresos_individuales_discipulos' => false,
                'ingresos_individuales_lideres' => false,
                'cantidad_maxima_reportes_semana' => 1,
                'horas_disponiblidad_link_asistencia' => 2,
                'estado' => true,
            ]);

        $tipoGrupo15->clasificacionAsistentes()->syncWithoutDetaching([1, 2, 3, 4, 5]);
        $tipoGrupo15->tiposOfrendas()->syncWithoutDetaching([5, 6, 2, 4]);

        $tipoGrupo16 = TipoGrupo::firstOrCreate(
            ['nombre' => 'Grupo de Crecimiento de Solteros 30 años'],
            [
                'nombre_plural' => 'Grupos de Crecimiento de Solteros 30 años',
                'descripcion' => 'Esta es la descripción',
                'imagen' => '',
                'geo_icono' => 'grupo-azul-claro.png',
                'seguimiento_actividad' => 0,
                'enviar_mensaje_bienvenida' => 1,
                'mensaje_bienvenida' => 'Ahora ya eres un líder, que bendición que puedas servir al señor desde los grupos inasignables',
                'metros_cobertura' => 1000,
                'color' => '#ed2',
                'tipo_evangelistico' => true,
                'registrar_inasistencia' => false,
                'inasistencia_obligatoria' => false,
                'ingresos_individuales_discipulos' => false,
                'ingresos_individuales_lideres' => false,
                'cantidad_maxima_reportes_semana' => 1,
                'horas_disponiblidad_link_asistencia' => 2,
                'estado' => true,
            ]);

        $tipoGrupo16->clasificacionAsistentes()->syncWithoutDetaching([1, 2, 3, 4, 5]);
        $tipoGrupo16->tiposOfrendas()->syncWithoutDetaching([5, 6, 2, 4]);

        $tipoGrupo17 = TipoGrupo::firstOrCreate(
            ['nombre' => 'Grupo de Crecimiento de Músicos'],
            [
                'nombre_plural' => 'Grupos de Crecimiento de Músicos',
                'descripcion' => 'Esta es la descripción',
                'imagen' => '',
                'geo_icono' => 'grupo-azul-claro.png',
                'seguimiento_actividad' => 0,
                'enviar_mensaje_bienvenida' => 1,
                'mensaje_bienvenida' => 'Ahora ya eres un líder, que bendición que puedas servir al señor desde los grupos inasignables',
                'metros_cobertura' => 1000,
                'color' => '#ed2',
                'tipo_evangelistico' => true,
                'registrar_inasistencia' => false,
                'inasistencia_obligatoria' => false,
                'ingresos_individuales_discipulos' => false,
                'ingresos_individuales_lideres' => false,
                'cantidad_maxima_reportes_semana' => 1,
                'horas_disponiblidad_link_asistencia' => 2,
                'estado' => true,
            ]);

        $tipoGrupo17->clasificacionAsistentes()->syncWithoutDetaching([1, 2, 3, 4, 5]);
        $tipoGrupo17->tiposOfrendas()->syncWithoutDetaching([5, 6, 2, 4]);

        $tipoGrupo18 = TipoGrupo::firstOrCreate(
            ['nombre' => 'Grupo de Crecimiento de Estudiantes'],
            [
                'nombre_plural' => 'Grupos de Crecimiento de Estudiantes',
                'descripcion' => 'Esta es la descripción',
                'imagen' => '',
                'geo_icono' => 'grupo-azul-claro.png',
                'seguimiento_actividad' => 0,
                'enviar_mensaje_bienvenida' => 1,
                'mensaje_bienvenida' => 'Ahora ya eres un líder, que bendición que puedas servir al señor desde los grupos inasignables',
                'metros_cobertura' => 1000,
                'color' => '#ed2',
                'tipo_evangelistico' => true,
                'registrar_inasistencia' => false,
                'inasistencia_obligatoria' => false,
                'ingresos_individuales_discipulos' => false,
                'ingresos_individuales_lideres' => false,
                'cantidad_maxima_reportes_semana' => 1,
                'horas_disponiblidad_link_asistencia' => 2,
                'estado' => true,
            ]);

        $tipoGrupo18->clasificacionAsistentes()->syncWithoutDetaching([1, 2, 3, 4, 5]);
        $tipoGrupo18->tiposOfrendas()->syncWithoutDetaching([5, 6, 2, 4]);

        $tipoGrupo19 = TipoGrupo::firstOrCreate(
            ['nombre' => 'Grupo de Crecimiento de Legendarios'],
            [
                'nombre_plural' => 'Grupos de Crecimiento de Legendarios',
                'descripcion' => 'Esta es la descripción',
                'imagen' => '',
                'geo_icono' => 'grupo-azul-claro.png',
                'seguimiento_actividad' => 0,
                'enviar_mensaje_bienvenida' => 1,
                'mensaje_bienvenida' => 'Ahora ya eres un líder, que bendición que puedas servir al señor desde los grupos inasignables',
                'metros_cobertura' => 1000,
                'color' => '#ed2',
                'tipo_evangelistico' => true,
                'registrar_inasistencia' => false,
                'inasistencia_obligatoria' => false,
                'ingresos_individuales_discipulos' => false,
                'ingresos_individuales_lideres' => false,
                'cantidad_maxima_reportes_semana' => 1,
                'horas_disponiblidad_link_asistencia' => 2,
                'estado' => true,
            ]);

        $tipoGrupo19->clasificacionAsistentes()->syncWithoutDetaching([1, 2, 3, 4, 5]);
        $tipoGrupo19->tiposOfrendas()->syncWithoutDetaching([5, 6, 2, 4]);

        $tipoGrupo20 = TipoGrupo::firstOrCreate(
            ['nombre' => 'Grupo de Crecimiento de Emprendedores'],
            [
                'nombre_plural' => 'Grupos de Crecimiento de Emprendedores',
                'descripcion' => 'Esta es la descripción',
                'imagen' => '',
                'geo_icono' => 'grupo-azul-claro.png',
                'seguimiento_actividad' => 0,
                'enviar_mensaje_bienvenida' => 1,
                'mensaje_bienvenida' => 'Ahora ya eres un líder, que bendición que puedas servir al señor desde los grupos inasignables',
                'metros_cobertura' => 1000,
                'color' => '#ed2',
                'tipo_evangelistico' => true,
                'registrar_inasistencia' => false,
                'inasistencia_obligatoria' => false,
                'ingresos_individuales_discipulos' => false,
                'ingresos_individuales_lideres' => false,
                'cantidad_maxima_reportes_semana' => 1,
                'horas_disponiblidad_link_asistencia' => 2,
                'estado' => true,
            ]);

        $tipoGrupo20->clasificacionAsistentes()->syncWithoutDetaching([1, 2, 3, 4, 5]);
        $tipoGrupo20->tiposOfrendas()->syncWithoutDetaching([5, 6, 2, 4]);

        $tipoGrupo21 = TipoGrupo::firstOrCreate(
            ['nombre' => 'Grupo de Crecimiento de Familias'],
            [
                'nombre_plural' => 'Grupos de Crecimiento de Familias',
                'descripcion' => 'Esta es la descripción',
                'imagen' => '',
                'geo_icono' => 'grupo-azul-claro.png',
                'seguimiento_actividad' => 0,
                'enviar_mensaje_bienvenida' => 1,
                'mensaje_bienvenida' => 'Ahora ya eres un líder, que bendición que puedas servir al señor desde los grupos inasignables',
                'metros_cobertura' => 1000,
                'color' => '#ed2',
                'tipo_evangelistico' => true,
                'registrar_inasistencia' => false,
                'inasistencia_obligatoria' => false,
                'ingresos_individuales_discipulos' => false,
                'ingresos_individuales_lideres' => false,
                'cantidad_maxima_reportes_semana' => 1,
                'horas_disponiblidad_link_asistencia' => 2,
                'estado' => true,
            ]);

        $tipoGrupo21->clasificacionAsistentes()->syncWithoutDetaching([1, 2, 3, 4, 5]);
        $tipoGrupo21->tiposOfrendas()->syncWithoutDetaching([5, 6, 2, 4]);

        $tipoGrupo22 = TipoGrupo::firstOrCreate(
            ['nombre' => 'Grupo de Crecimiento de parejas'],
            [
                'nombre_plural' => 'Grupos de Crecimiento de parejas',
                'descripcion' => 'Esta es la descripción',
                'imagen' => '',
                'geo_icono' => 'grupo-azul-claro.png',
                'seguimiento_actividad' => 0,
                'enviar_mensaje_bienvenida' => 1,
                'mensaje_bienvenida' => 'Ahora ya eres un líder, que bendición que puedas servir al señor desde los grupos inasignables',
                'metros_cobertura' => 1000,
                'color' => '#ed2',
                'tipo_evangelistico' => true,
                'registrar_inasistencia' => false,
                'inasistencia_obligatoria' => false,
                'ingresos_individuales_discipulos' => false,
                'ingresos_individuales_lideres' => false,
                'cantidad_maxima_reportes_semana' => 1,
                'horas_disponiblidad_link_asistencia' => 2,
                'estado' => true,
            ]);

        $tipoGrupo22->clasificacionAsistentes()->syncWithoutDetaching([1, 2, 3, 4, 5]);
        $tipoGrupo22->tiposOfrendas()->syncWithoutDetaching([5, 6, 2, 4]);

        $tipoGrupo23 = TipoGrupo::firstOrCreate(
            ['nombre' => 'Grupo de Crecimiento de Parejas en unión libre'],
            [
                'nombre_plural' => 'Grupos de Crecimiento de Parejas en unión libre',
                'descripcion' => 'Esta es la descripción',
                'imagen' => '',
                'geo_icono' => 'grupo-azul-claro.png',
                'seguimiento_actividad' => 0,
                'enviar_mensaje_bienvenida' => 1,
                'mensaje_bienvenida' => 'Ahora ya eres un líder, que bendición que puedas servir al señor desde los grupos inasignables',
                'metros_cobertura' => 1000,
                'color' => '#ed2',
                'tipo_evangelistico' => true,
                'registrar_inasistencia' => false,
                'inasistencia_obligatoria' => false,
                'ingresos_individuales_discipulos' => false,
                'ingresos_individuales_lideres' => false,
                'cantidad_maxima_reportes_semana' => 1,
                'horas_disponiblidad_link_asistencia' => 2,
                'estado' => true,
            ]);

        $tipoGrupo23->clasificacionAsistentes()->syncWithoutDetaching([1, 2, 3, 4, 5]);
        $tipoGrupo23->tiposOfrendas()->syncWithoutDetaching([5, 6, 2, 4]);

        $tipoGrupo24 = TipoGrupo::firstOrCreate(
            ['nombre' => 'Grupo de Crecimiento de Madres solteras'],
            [
                'nombre_plural' => 'Grupos de Crecimiento de Madres solteras',
                'descripcion' => 'Esta es la descripción',
                'imagen' => '',
                'geo_icono' => 'grupo-azul-claro.png',
                'seguimiento_actividad' => 0,
                'enviar_mensaje_bienvenida' => 1,
                'mensaje_bienvenida' => 'Ahora ya eres un líder, que bendición que puedas servir al señor desde los grupos inasignables',
                'metros_cobertura' => 1000,
                'color' => '#ed2',
                'tipo_evangelistico' => true,
                'registrar_inasistencia' => false,
                'inasistencia_obligatoria' => false,
                'ingresos_individuales_discipulos' => false,
                'ingresos_individuales_lideres' => false,
                'cantidad_maxima_reportes_semana' => 1,
                'horas_disponiblidad_link_asistencia' => 2,
                'estado' => true,
            ]);

        $tipoGrupo24->clasificacionAsistentes()->syncWithoutDetaching([1, 2, 3, 4, 5]);
        $tipoGrupo24->tiposOfrendas()->syncWithoutDetaching([5, 6, 2, 4]);
    }
}
