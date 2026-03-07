<?php

namespace Database\Seeders;


use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Facades\DB;



class IntegrantesGrupoSeeder extends Seeder
{
    /**
     * Ruta del archivo JSON dentro de la carpeta storage/app.
     * @var string
     */
    protected $filePath = 'seeders/integrantes_grupo_202507282019.json';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Verificamos que el archivo JSON exista
        if (!file_exists(base_path('storage/app/' . $this->filePath))) {
            $this->command->error('¡Archivo JSON de integrantes_grupo no encontrado!');
            return;
        }

        $this->command->info('✅ Archivo JSON de integrantes encontrado. Vaciando tabla e iniciando la importación...');

        // 2. Vaciamos la tabla para una importación limpia

        // 3. Leemos y decodificamos el contenido del archivo JSON
        $jsonContent = file_get_contents(base_path('storage/app/' . $this->filePath));
        $data = json_decode($jsonContent, true);

        // Apuntamos al array de registros dentro de la clave "RECORDS"
        $integrantesData = $data['integrantes_grupo'] ?? [];

        if (empty($integrantesData)) {
            $this->command->error('El archivo JSON de integrantes está vacío o no contiene registros válidos.');
            return;
        }

        // 4. Preparamos los datos para la inserción masiva, mapeando los campos
        $integrantesParaInsertar = [];
        foreach ($integrantesData as $integrante) {
            // Saltamos cualquier registro que no tenga un id
            if (!isset($integrante['id'])) {
                continue;
            }

            $integrantesParaInsertar[] = [
                'id' => $integrante['id'],
                'grupo_id' => $integrante['grupo_id'],
                'user_id' => $integrante['user_id'], // Mapeo de asistente_id -> user_id
                'created_at' => $integrante['created_at'],
                'updated_at' => $integrante['updated_at'],
            ];
        }

        // 5. Insertamos todos los registros en una sola consulta para mayor eficiencia
        if (!empty($integrantesParaInsertar)) {
            // Dividimos la inserción en trozos (chunks) para manejar archivos muy grandes
            // Dividimos la inserción en trozos (chunks) para manejar archivos muy grandes
            foreach ($integrantesParaInsertar as $integrante) {
                DB::table('integrantes_grupo')->updateOrInsert(
                    ['id' => $integrante['id']],
                    $integrante
                );
            }
        }

        $this->command->info('✔️  ¡Proceso finalizado! Se han importado ' . count($integrantesParaInsertar) . ' integrantes de grupo.');
    }
}
