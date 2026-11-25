<?php

namespace Database\Seeders;

use App\Models\Sede;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SedeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    protected $filePath = 'seeders/sedes_202507281954.json';

    public function run(): void
    {

        Sede::firstOrCreate([
            'id' => '1',
            'nombre' => 'Sede principal',
            'grupo_id' => 1,
            'tipo_sede_id' => 1,
            'foto' => 'default.png',
            'pais_id' => 45,
            'default' => true,
            'latitud' => '4.5976116',
            'longitud' => '-74.0759201',
        ]);

        Sede::firstOrCreate([
            'id' => '2',
            'nombre' => 'Conectate Internacional',
            'grupo_id' => 1,
            'tipo_sede_id' => 1,
            'foto' => 'default.png',
            'pais_id' => 45,
            'default' => false,
            'latitud' => '4.7109079',
            'longitud' => '-74.0718472',
        ]);

        Sede::firstOrCreate([
            'id' => '33',
            'nombre' => 'Contectate Nacional',
            'grupo_id' => 1,
            'tipo_sede_id' => 1,
            'foto' => 'default.png',
            'pais_id' => 45,
            'default' => false,
            'latitud' => '4.7110412',
            'longitud' => '-74.0720852',
        ]);

        if (! file_exists(base_path('storage/app/'.$this->filePath))) {
            $this->command->warn('¡Archivo JSON de sedes no encontrado! Saltando importación de JSON.');

            return;
        }

        $this->command->info('✅ Archivo JSON de sedes encontrado. Vaciando tabla e iniciando...');

        // Schema::disableForeignKeyConstraints();
        // DB::table('sedes')->truncate();
        // Schema::enableForeignKeyConstraints();

        $jsonContent = file_get_contents(base_path('storage/app/'.$this->filePath));
        $data = json_decode($jsonContent, true);

        // --- [LA CORRECCIÓN ESTÁ AQUÍ] ---
        // Verificamos si los datos están dentro de una clave principal (como "RECORDS")
        $sedesData = $data;
        if (isset($data['sedes']) && is_array($data['sedes'])) {
            $sedesData = $data['sedes'];
        }
        // --- [FIN DE LA CORRECCIÓN] ---

        if (empty($sedesData)) {
            $this->command->error('El archivo JSON de sedes está vacío o no contiene registros válidos.');

            return;
        }

        $sedesParaInsertar = [];
        foreach ($sedesData as $sede) {
            // Se añade una validación para saltar registros sin 'id'
            if (! isset($sede['id'])) {
                $this->command->warn('🟡 Registro omitido: no contiene una clave "id".');

                continue;
            }

            $sedesParaInsertar[] = [
                'id' => $sede['id'],
                'nombre' => $sede['nombre'],
                'direccion' => $sede['direccion'],
                'telefono' => $sede['telefono'],
                'tipo_sede_id' => $sede['tipo_sede_id'],
                'grupo_id' => $sede['grupo_id'],
                'pais_id' => $sede['pais_id'],
                'descripcion' => $sede['descripcion'],
                'fecha_creacion' => $sede['fecha_creacion'],
                'foto' => $sede['foto'],
                'municipio_id' => $sede['municipio_id'],
                'created_at' => $sede['created_at'],
                'updated_at' => $sede['updated_at'],
            ];
        }

        // 5. Insertamos o actualizamos registros uno por uno
        foreach ($sedesParaInsertar as $sedeData) {
            Sede::firstOrCreate(
                ['id' => $sedeData['id']],
                $sedeData
            );
        }

        $this->command->info('✔️  ¡Proceso finalizado! Se han importado '.count($sedesParaInsertar).' sedes.');
    }
}
