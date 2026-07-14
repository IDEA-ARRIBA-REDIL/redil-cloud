<?php

namespace Database\Seeders;

use App\Models\TipoGrupo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class TipoGrupoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jsonPath = base_path('storage/app/seeders/tipos_grupo_manantial.json');

        if (! File::exists($jsonPath)) {
            $this->command->warn("El archivo {$jsonPath} no existe. Saltando seeder de tipos de grupo.");

            return;
        }

        $json = File::get($jsonPath);
        $data = json_decode($json, true);

        foreach ($data['tipo_grupos'] as $grupo) {
            $attributes = [
                'nombre' => $grupo['nombre'],
                'descripcion' => $grupo['descripcion'] ?? '',
                'seguimiento_actividad' => $grupo['seguimiento_actividad'] ?? false,
                'contiene_servidores' => $grupo['contiene_servidores'] ?? false,
                'posible_grupo_sede' => $grupo['posible_grupo_sede'] ?? false,
                'metros_cobertura' => $grupo['metros_cobertura'] ?? 0,
                'ingresos_individuales_discipulos' => $grupo['ingresos_individuales_discipulos'] ?? false,
                'ingresos_individuales_lideres' => $grupo['ingresos_individuales_lideres'] ?? false,
                'registra_datos_planeacion' => $grupo['registra_datos_planeacion'] ?? false,
                'servidores_solo_discipulos' => $grupo['servidores_solo_discipulos'] ?? false,
                'color' => $grupo['color'] ?? null,
                'visible_mapa_asignacion' => $grupo['visible_mapa_asignacion'] ?? false,
                'geo_icono' => $grupo['geo_icono'] ?? null,
                'nombre_plural' => $grupo['nombre_plural'] ?? null,
                'tipo_evangelistico' => $grupo['tipo_evangelistico'] ?? false,
                'cantidad_maxima_reportes_semana' => $grupo['cantidad_maxima_reportes_semana'] ?? 1,
                'enviar_mensaje_bienvenida' => $grupo['enviar_mensaje_bienvenida'] ?? false,
                'mensaje_bienvenida' => $grupo['mensaje_bienvenida'] ?? null,
                'orden' => $grupo['orden'] ?? 0,
                'tiempo_para_definir_inactivo_grupo' => $grupo['tiempo_para_definir_inactivo_grupo'] ?? 30,
                'inasistencia_obligatoria' => $grupo['inasistencia_obligatoria'] ?? false,
                'horas_disponiblidad_link_asistencia' => 2,
                'estado' => true,
            ];

            $tipoGrupo = TipoGrupo::find($grupo['id']);
            if (! $tipoGrupo) {
                $attributes['id'] = $grupo['id'];
                $attributes['created_at'] = now();
                $attributes['updated_at'] = now();
                \Illuminate\Support\Facades\DB::table('tipo_grupos')->insert($attributes);
                $tipoGrupo = TipoGrupo::find($grupo['id']);
            } else {
                $tipoGrupo->update($attributes);
            }

            // Automatizaciones de los pasos 1 y 2 para la Célula Liderazgo Sup Auxiliar (id 5)
            if ($grupo['id'] == 5) {
                $tipoGrupo->automatizacionesPasosCrecimiento()->syncWithoutDetaching([
                    1 => ['estado_por_defecto' => 3, 'descripcion_por_defecto' => 'Hola, es automatizo este paso.'],
                    2 => ['estado_por_defecto' => 2, 'descripcion_por_defecto' => 'Hola, es automatizo este paso.'],
                ]);
            }

            // clasificaciones asistentes y ofrendas para grupos evangelísticos (Crecimiento, Warriors, etc.)
            if (! empty($grupo['tipo_evangelistico'])) {
                $tipoGrupo->clasificacionAsistentes()->syncWithoutDetaching([1, 2, 3, 4, 5]);
                $tipoGrupo->tiposOfrendas()->syncWithoutDetaching([5, 6, 2, 4]);
            }
        }
    }
}
