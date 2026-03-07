<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class EncargadosGrupoSeeder extends Seeder
{
    /**
     * Ruta del archivo JSON dentro de la carpeta storage/app.
     * @var string
     */
    protected $filePath = 'seeders/encargados_grupo_202507282019.json';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Verificamos que el archivo JSON exista
        if (!file_exists(base_path('storage/app/' . $this->filePath))) {
            $this->command->error('¡Archivo JSON de encargados_grupo no encontrado!');
            return;
        }

        $this->command->info('✅ Archivo JSON de encargados encontrado. Vaciando tabla e iniciando la importación...');

        // 2. Vaciamos la tabla para una importación limpia


        // 3. Leemos y decodificamos el contenido del archivo JSON
        $jsonContent = file_get_contents(base_path('storage/app/' . $this->filePath));
        $data = json_decode($jsonContent, true);

        // Apuntamos al array de registros dentro de la clave "RECORDS"
        $encargadosData = $data['encargados_grupo'] ?? [];

        if (empty($encargadosData)) {
            $this->command->error('El archivo JSON de encargados está vacío o no contiene registros válidos.');
            return;
        }

        // 4. Preparamos los datos para la inserción masiva, mapeando los campos
        $encargadosParaInsertar = [];
        foreach ($encargadosData as $encargado) {
            // Saltamos cualquier registro que no tenga un id para evitar errores
            if (!isset($encargado['id'])) {
                continue;
            }

            $encargadosParaInsertar[] = [
                'id' => $encargado['id'],
                'grupo_id' => $encargado['grupo_id'],
                'user_id' => $encargado['user_id'], // Mapeo de asistente_id -> user_id
                'created_at' => $encargado['created_at'],
                'updated_at' => $encargado['updated_at'],
            ];
        }

        // 5. Insertamos todos los registros en una sola consulta para mayor eficiencia
        // 5. Insertamos o actualizamos registros uno por uno para asegurar idempotencia
        foreach ($encargadosParaInsertar as $encargado) {
            DB::table('encargados_grupo')->updateOrInsert(
                ['id' => $encargado['id']],
                $encargado
            );
        }

        $this->command->info('✔️  ¡Proceso finalizado! Se han importado ' . count($encargadosParaInsertar) . ' encargados de grupo.');
    }
}
