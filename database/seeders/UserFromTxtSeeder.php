<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\LazyCollection;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class UserFromTxtSeeder extends Seeder
{
    protected $filePath = 'seeders/usuarios-vision.txt';

    public function run(): void
    {
        if (!Storage::exists($this->filePath)) {
            $this->command->error('¡Archivo no encontrado!');
            return;
        }

        $this->command->info('✅ Archivo encontrado. Iniciando la importación de usuarios...');

        $headers = [
            'asistente_id',
            'primer_nombre',
            'segundo_nombre',
            'primer_apellido',
            'segundo_apellido',
            'fecha_nacimiento',
            'identificacion',
            'tipo_identificacion',
            'genero',
            'pais_id',
            'telefono_movil',
            'direccion',
            'fecha_ingreso',
            'foto',
            'ultimo_reporte_grupo',
            'ultimo_reporte_grupo_auxiliar',
            'ultimo_reporte_reunion',
            'ultimo_reporte_reunion_auxiliar',
            'fecha_actualizacion',
            'created_at',
            'updated_at',
            'usuario_creacion_id',
            'asistente_de_creacion_id',
            'indice_grafico_ministerial',
            'tipo_asistente_id',
            'sede_id',
            'grupo_id',
            'user_id',
            'email',
            'password',
            'grupo',
            'matricula_id',
            'fecha_matricula'
        ];

        $createdCount = 0;
        $skippedCount = 0;
        $errorCount = 0;

        LazyCollection::make(function () {
            $file = Storage::readStream($this->filePath);
            while (($line = fgets($file)) !== false) {
                yield $line;
            }
        })
            ->skip(1)
            ->filter(fn($line) => !empty(trim($line)) && !str_starts_with(trim($line), '|--'))
            ->each(function ($line, $index) use ($headers, &$createdCount, &$skippedCount, &$errorCount) {

                $valuesRaw = explode('|', $line);
                $values = array_map('trim', array_slice($valuesRaw, 1, -1));

                if (count($headers) !== count($values)) {
                    $skippedCount++;
                    return;
                }

                try {
                    $rowData = array_combine($headers, $values);

                    // Verificación de duplicado por Identificación
                    if ($rowData['identificacion'] && User::where('identificacion', $rowData['identificacion'])->exists()) {
                        $this->command->warn("🟡 OMITIDO (identificación ya existe): " . $rowData['identificacion']);
                        $skippedCount++;
                        return;
                    }

                    // --- [NUEVA VERIFICACIÓN DE EMAIL DUPLICADO] ---
                    if ($rowData['email'] && User::where('email', $rowData['email'])->exists()) {
                        $this->command->warn("🟡 OMITIDO (email ya existe): " . $rowData['email']);
                        $skippedCount++;
                        return; // Salta al siguiente registro
                    }
                    // --- [FIN DE LA VERIFICACIÓN] ---


                    $user = User::firstOrCreate([
                        'id' => str_replace(".", "", $rowData['asistente_id']),
                        'asistente_id' => str_replace(".", "", $rowData['asistente_id']),
                        'email' => $rowData['email'],
                        'email_verified_at' => now(),
                        'password' => $rowData['password'],
                        'primer_nombre' => $rowData['primer_nombre'],
                        'primer_apellido' => $rowData['primer_apellido'],
                        'segundo_nombre' => $rowData['segundo_nombre'] ?: '',
                        'segundo_apellido' => $rowData['segundo_apellido'] ?: '',
                        'tipo_identificacion_id' => (int) $rowData['tipo_identificacion'],
                        'identificacion' => $rowData['identificacion'],
                        'telefono_movil' => $rowData['telefono_movil'] ?: '0000000',
                        'direccion' => $rowData['direccion'] ?: 'No especificada',
                        'fecha_nacimiento' => $rowData['fecha_nacimiento'],
                        'tipo_usuario_id' => (int) $rowData['tipo_asistente_id'],
                        'genero' => $rowData['genero'],
                        'foto' => $rowData['foto'],
                        'estado_civil_id' => '1',
                        'activo' => true,
                        'tipo_usuario_id' => $rowData['tipo_asistente_id'],
                        'trasladado' => true,
                        'created_at' => $rowData['created_at'] === '[NULL]' ? now() : $rowData['created_at'],
                        'updated_at' => $rowData['updated_at'] === '[NULL]' ? now() : $rowData['updated_at'],
                    ]);

                    $user->roles()->attach($user->tipoUsuario->id_rol_dependiente, ['activo' => 1, 'dependiente' => 1, 'model_type' => 'App\Models\User']);

                    $createdCount++;
                } catch (\Exception $e) {
                    $email = $rowData['email'] ?? "en línea #{$index}";
                    $this->command->error("🔴 Error para email: {$email}. Error: " . $e->getMessage());
                    Log::error("Fallo en Seeder para email: {$email}, Error: " . $e->getMessage());
                    $errorCount++;
                }
            });



        $this->command->info('----------------------------------');
        $this->command->info('📊 REPORTE FINAL:');
        $this->command->info("✔️  Usuarios creados: {$createdCount}");
        $this->command->info("🟡 Registros saltados (formato o duplicados): {$skippedCount}");
        $this->command->info("🔴 Errores encontrados: {$errorCount}");
        $this->command->info('----------------------------------');
    }
}
