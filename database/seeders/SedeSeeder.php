<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Facades\DB;
use App\Models\Sede;

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
      'continente_id' => 2,
      'foto' => 'default.png',
      'pais_id' => 45,
      'default' => TRUE
    ]);

    Sede::firstOrCreate([
      'id' => '2',
      'nombre' => 'Sede Secundaria',
      'grupo_id' => 1,
      'tipo_sede_id' => 1,
      'continente_id' => 2,
      'foto' => 'default.png',
      'pais_id' => 45,
      'default' => FALSE
    ]);

    Sede::firstOrCreate([
      'id' => '33',
      'nombre' => 'Sede Adicional',
      'grupo_id' => 1,
      'tipo_sede_id' => 1,
      'continente_id' => 2,
      'foto' => 'default.png',
      'pais_id' => 45,
      'default' => FALSE
    ]);

    if (!file_exists(base_path('storage/app/' . $this->filePath))) {
      $this->command->warn('¡Archivo JSON de sedes no encontrado! Saltando importación de JSON.');
      return;
    }

    $this->command->info('✅ Archivo JSON de sedes encontrado. Vaciando tabla e iniciando...');

    // Schema::disableForeignKeyConstraints();
    // DB::table('sedes')->truncate();
    // Schema::enableForeignKeyConstraints();

    $jsonContent = file_get_contents(base_path('storage/app/' . $this->filePath));
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
      if (!isset($sede['id'])) {
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

        'barrio_id' => $sede['barrio_id'],
        'barrio_auxiliar' => $sede['barrio_auxiliar'],
        'descripcion' => $sede['descripcion'],
        'fecha_creacion' => $sede['fecha_creacion'],
        'foto' => $sede['foto'],
        'departamento_id' => $sede['departamento_id'],
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

    $this->command->info('✔️  ¡Proceso finalizado! Se han importado ' . count($sedesParaInsertar) . ' sedes.');
  }
}
