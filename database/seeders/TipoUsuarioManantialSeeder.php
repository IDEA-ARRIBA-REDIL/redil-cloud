<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\LazyCollection;

class TipoUsuarioManantialSeeder extends Seeder
{
    /**
     * Ruta del archivo JSON que contiene los tipos de usuario.
     * @var string
     */
    protected $filePath = 'seeders/tipo_asistentes_202507301621.json';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Verificamos que el archivo JSON exista
        if (!file_exists(base_path('storage/app/' . $this->filePath))) {
            $this->command->error('¡Archivo JSON de tipos de asistente no encontrado en ' . base_path('storage/app/' . $this->filePath) . '!');
            return;
        }

        $this->command->info('✅ Archivo JSON de tipos de asistente encontrado. Vaciando tabla e iniciando...');

        // 2. Vaciamos la tabla para una importación limpia


        // 3. Leemos y decodificamos el contenido del archivo JSON
        $jsonContent = file_get_contents(base_path('storage/app/' . $this->filePath));
        $data = json_decode($jsonContent, true);

        // Asumimos que los datos pueden venir dentro de una clave "RECORDS"
        $tiposData = $data['tipo_asistentes'] ?? $data;

        if (empty($tiposData)) {
            $this->command->error('El archivo JSON está vacío o no contiene un array de registros válido.');
            return;
        }

        // 4. Preparamos los datos para la inserción masiva
        $tiposParaInsertar = [];
        foreach ($tiposData as $tipo) {
            // Saltamos registros mal formados que no tengan un 'id' o 'nombre'
            if (!isset($tipo['id']) || !isset($tipo['nombre'])) {
                continue;
            }

            $tiposParaInsertar[] = [
                'id' => $tipo['id'],
                'orden' => $tipo['id'],
                'nombre' => $tipo['nombre'],
                'nombre_plural' => $tipo['nombre_plural'],
                'color' => $tipo['color'],
                'icono' => $tipo['icono'],

                'id_rol_dependiente' => $tipo['id_tipo_usuario_dependiente'],

                'puntaje' => $tipo['puntaje'],
                // Laravel manejará created_at y updated_at automáticamente al usar 'insert'
            ];
        }

        // 5. Insertamos todos los registros en una sola consulta
        // 5. Insertamos o actualizamos registros uno por uno para asegurar idempotencia
        foreach ($tiposParaInsertar as $tipo) {
            \App\Models\TipoUsuario::firstOrCreate(
                ['id' => $tipo['id']], // Buscamos por ID
                $tipo
            );
        }

        $this->command->info('✔️  ¡Proceso finalizado! Se han importado ' . count($tiposParaInsertar) . ' tipos de usuario.');
    }
}
