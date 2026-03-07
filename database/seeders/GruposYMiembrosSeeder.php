<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use App\Models\Grupo;

class GruposYMiembrosSeeder extends Seeder
{
    /**
     * Ruta del archivo JSON dentro de la carpeta storage/app.
     * @var string
     */
    protected $filePath = 'seeders/grupos_202507281947.json';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (!file_exists(base_path('storage/app/' . $this->filePath))) {
            $this->command->error('¡Archivo JSON no encontrado en ' . base_path('storage/app/' . $this->filePath) . '!');
            return;
        }

        $this->command->info('✅ Archivo JSON encontrado. Vaciando tabla e iniciando la importación...');



        $jsonContent = file_get_contents(base_path('storage/app/' . $this->filePath));
        $data = json_decode($jsonContent, true);

        // --- [LA CORRECCIÓN QUE FALTABA] ---
        // Apuntamos al array de registros dentro de la clave "RECORDS"
        $gruposData = $data['grupos'] ?? [];
        // --- [FIN DE LA CORRECCIÓN] ---

        if (empty($gruposData)) {
            $this->command->error('El archivo JSON está vacío o no contiene registros válidos dentro de la clave "RECORDS".');
            return;
        }

        $gruposParaInsertar = [];
        foreach ($gruposData as $grupo) {
            $gruposParaInsertar[] = [
                'id' => $grupo['id'],
                'nombre' => $grupo['nombre'],
                'direccion' => $grupo['direccion'],
                'telefono' => $grupo['telefono'],
                'rhema' => $grupo['rhema'] === '' ? null : $grupo['rhema'],
                'fecha_apertura' => $grupo['fecha_apertura'] === '' ? null : $grupo['fecha_apertura'],
                'dia' => $grupo['dia'],
                'hora' => $grupo['hora'],
                'nivel' => $grupo['nivel'],
                'dado_baja' => $grupo['dado_baja'],
                'tipo_grupo_id' => $grupo['tipo_grupo_id'],
                'tipo_vivienda_id' => $grupo['tipo_vivienda'],
                'barrio_id' => $grupo['barrio_id'],
                'dia_planeacion' => $grupo['dia_planeacion'],
                'codigo' => $grupo['codigo'],
                'hora_planeacion' => $grupo['hora_planeacion'],
                'barrio_auxiliar' => $grupo['barrio_auxiliar'],
                'latitud' => $grupo['latitud'],
                'longitud' => $grupo['longitud'],
                'contiene_amo' => $grupo['contiene_amo'],
                'inactivo' => $grupo['inactivo'],
                'sede_id' => $grupo['sede_id'],
                'ultimo_reporte_grupo' => $grupo['ultimo_reporte_grupo'],
                'ultimo_reporte_grupo_auxiliar' => $grupo['ultimo_reporte_grupo_auxiliar'],
                'rol_de_creacion_id' => $grupo['tipo_usuario_de_creacion_id'],
                'asistente_de_creacion_id' => $grupo['asistente_de_creacion_id'],
                'indice_grafico_ministerial' => $grupo['indice_grafico_ministerial'],
                'usuario_creacion_id' => $grupo['usuario_creacion_id'],
                'created_at' => $grupo['created_at'],
                'updated_at' => $grupo['updated_at'],
            ];
        }

        // Dividimos la inserción en trozos para manejar archivos muy grandes
        // Dividimos la inserción en trozos para manejar archivos muy grandes
        foreach ($gruposParaInsertar as $grupo) {
            Grupo::firstOrCreate(
                ['id' => $grupo['id']],
                $grupo
            );
        }

        $this->command->info('✔️  ¡Proceso finalizado! Se han importado ' . count($gruposParaInsertar) . ' grupos.');
    }
}
