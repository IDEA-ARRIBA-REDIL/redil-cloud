<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class AsistentesEscuelaSeeder extends Seeder
{
    /**
     * Rutas de los archivos JSON de migración desde la BD Vision.
     */
    protected string $asistentesPath = 'seeders/ASISTENTES MIGRACION ESCUELAS.json';

    protected string $usuariosPath = 'seeders/USUARIOS MIGRACION ESCUELAS.json';

    protected string $tipoUsuariosPath = 'seeders/USUARIO TIPO USUARIOS MIGRACION ESCUELAS.json';

    /**
     * Diccionario de mapeo: ID de tipo_usuario (Vision) => Nombre del Rol en Spatie (REDIL Cloud)
     * Ajusta los valores de la derecha según los roles exactos definidos en RoleSeeder.
     */
    protected array $roleMapping = [
        6 => 'Oveja',      // Conéctate
        10 => 'PDP',        // Punto de Pago (o 'Cajero PDP')
        41 => 'Nuevo',      // Nuevo y Amigo
        43 => 'Oveja',      // Hermano Mayor
        44 => 'Lider',      // Líder
        45 => 'Lider',      // Supervisor Auxiliar
        46 => 'Lider',      // Supervisor General
        48 => 'Pastor',     // Pastor Distrital
        51 => 'Oveja',      // MKids
        52 => 'Consejero',  // Asesor
        54 => 'Alumno',     // Academia
        72 => 'Maestro',    // Maestro de Niños
        83 => 'Lider',      // Figuras Ministeriales
    ];

    public function run(): void
    {
        // Obtener un tipo_usuario válido para cumplir con el NOT NULL (ej: el primero de la BD)
        $defaultTipoUsuario = \App\Models\TipoUsuario::first();
        $defaultTipoUsuarioId = $defaultTipoUsuario ? $defaultTipoUsuario->id : 1;

        $asistentes = $this->loadJson($this->asistentesPath, 'asistentes');
        $usuarios = $this->loadJson($this->usuariosPath, 'users');
        $tipoUsuarios = $this->loadJson($this->tipoUsuariosPath, 'usuario_tipo_usuario');

        if ($asistentes === null || $usuarios === null || $tipoUsuarios === null) {
            return;
        }

        $asistentesMap = collect($asistentes)->keyBy('id');
        $usuariosPorAsistenteId = collect($usuarios)->keyBy('asistente_id');
        $tipoUsuariosPorUsuarioId = collect($tipoUsuarios)->groupBy('usuario_id');

        $this->command->info('📊 Datos cargados:');
        $this->command->info("   Asistentes: {$asistentesMap->count()}");
        $this->command->info("   Usuarios (con credenciales): {$usuariosPorAsistenteId->count()}");
        $this->command->info('   Asignaciones tipo_usuario: '.collect($tipoUsuarios)->count());
        $this->command->info('----------------------------------');

        $createdCount = 0;
        $skippedCount = 0;
        $errorCount = 0;

        foreach ($asistentesMap as $asistenteId => $asistente) {
            try {
                $usuarioVision = $usuariosPorAsistenteId->get($asistenteId);

                if (! $usuarioVision) {
                    $skippedCount++;

                    continue;
                }

                $email = trim($usuarioVision['email'] ?? '');
                $identificacion = trim($asistente['identificacion'] ?? '');

                if ($identificacion && User::where('identificacion', $identificacion)->exists()) {
                    $this->command->warn("🟡 OMITIDO (identificación ya existe): {$identificacion}");
                    $skippedCount++;

                    continue;
                }

                if ($email && User::where('email', $email)->exists()) {
                    $this->command->warn("🟡 OMITIDO (email ya existe): {$email}");
                    $skippedCount++;

                    continue;
                }

                // 1. Crear el User (se ignora el tipo_asistente_id del JSON como solicitaste)
                $user = User::create([
                    'asistente_id' => $asistenteId,
                    'email' => $email,
                    'email_verified_at' => now(),
                    'password' => $usuarioVision['password'],
                    'activo' => $usuarioVision['activo'] ?? false,
                    'primer_nombre' => trim($asistente['primer_nombre'] ?? ''),
                    'segundo_nombre' => trim($asistente['segundo_nombre'] ?? ''),
                    'primer_apellido' => trim($asistente['primer_apellido'] ?? ''),
                    'segundo_apellido' => trim($asistente['segundo_apellido'] ?? ''),
                    'genero' => $asistente['genero'] ?? 0,
                    'tipo_identificacion_id' => $asistente['tipo_identificacion'] ?? null,
                    'identificacion' => $identificacion,
                    'fecha_nacimiento' => $this->parseDate($asistente['fecha_nacimiento']),
                    'direccion' => trim($asistente['direccion'] ?? ''),
                    'telefono_fijo' => trim($asistente['telefono_fijo'] ?? ''),
                    'telefono_movil' => trim($asistente['telefono_movil'] ?? '') ?: '0000000',
                    'telefono_otro' => trim($asistente['telefono_otro'] ?? ''),
                    'estado_civil_id' => $asistente['estado_civil'] ?? null,
                    'fecha_ingreso' => $this->parseDate($asistente['fecha_ingreso']),
                    'indicaciones_medicas' => trim($asistente['indicaciones_medicas'] ?? ''),
                    'foto' => $asistente['foto'] ?? 'default-m.png',
                    'tipo_usuario_id' => $defaultTipoUsuarioId, // Valor dinámico obtenido de la BD
                    'pais_id' => $asistente['pais_id'] ?? 45,
                    'sede_id' => $asistente['sede_id'] ?? 2,
                    'profesion_id' => $asistente['profesion'] ?? null,
                    'nivel_academico_id' => $asistente['nivel_academico'] ?? null,
                    'estado_nivel_academico_id' => $asistente['estado_nivel_academico'] ?? null,
                    'sector_economico_id' => $asistente['sector_economico'] ?? null,
                    'tipo_vivienda_id' => $asistente['tipo_vivienda'] ?? null,
                    'ocupacion_id' => $asistente['ocupacion'] ?? null,
                    'tipo_sangre_id' => $asistente['tipo_sangre'] ?? null,
                    'tipo_vinculacion_id' => $asistente['tipo_vinculacion_id'] ?? 1,
                    'informacion_opcional' => trim($asistente['informacion_opcional'] ?? ''),
                    'esta_aprobado' => $asistente['esta_aprobado'] ?? false,
                    'ultimo_reporte_grupo' => $this->parseDate($asistente['ultimo_reporte_grupo']) ?? '2016-01-01 05:00:01',
                    'ultimo_reporte_reunion' => $this->parseDate($asistente['ultimo_reporte_reunion']) ?? '2016-01-01 05:00:01',
                    'usuario_creacion_id' => $asistente['usuario_creacion_id'] ?? null,
                    'trasladado' => true,
                    'deleted_at' => $this->parseDate($asistente['deleted_at']),
                    'created_at' => $this->parseDate($asistente['created_at']) ?? now(),
                    'updated_at' => $this->parseDate($asistente['updated_at']) ?? now(),
                ]);

                // 2. Asignar Roles de Spatie (basados en la tabla vieja USUARIO TIPO USUARIO)
                $tiposDelUsuario = $tipoUsuariosPorUsuarioId->get($usuarioVision['id'], collect());

                $rolesAsignar = [];
                foreach ($tiposDelUsuario as $tipoAsignado) {
                    $tipoUsuarioIdViejo = $tipoAsignado['tipo_usuario_id'] ?? null;

                    if ($tipoUsuarioIdViejo && array_key_exists($tipoUsuarioIdViejo, $this->roleMapping)) {
                        $rolNombre = $this->roleMapping[$tipoUsuarioIdViejo];
                        if (! in_array($rolNombre, $rolesAsignar)) {
                            $rolesAsignar[] = $rolNombre;
                        }
                    }
                }

                // Asignar los roles (Ojo: usando Spatie Permission normal o la tabla model_has_roles con activo)
                foreach ($rolesAsignar as $index => $rolNombre) {
                    // Para REDIL Cloud, la tabla pivot usa `activo` y `dependiente`
                    $roleModel = \Spatie\Permission\Models\Role::where('name', $rolNombre)->first();
                    if ($roleModel) {
                        // El primer rol de la lista lo marcamos como activo
                        $isActivo = ($index === 0) ? 1 : 0;

                        // Validar si es rol dependiente en la BD
                        $isDependiente = $roleModel->dependiente ? 1 : 0;

                        if (! $user->roles()->where('role_id', $roleModel->id)->exists()) {
                            $user->roles()->attach($roleModel->id, [
                                'activo' => $isActivo,
                                'dependiente' => $isDependiente,
                                'model_type' => 'App\Models\User',
                            ]);
                        }
                    }
                }

                // Si por alguna razón no se asignó ningún rol, podemos asignarle 'Oveja' u otro por defecto
                if (empty($rolesAsignar)) {
                    $roleModel = \Spatie\Permission\Models\Role::where('name', 'Oveja')->first();
                    if ($roleModel) {
                        $user->roles()->attach($roleModel->id, [
                            'activo' => 1,
                            'dependiente' => $roleModel->dependiente ? 1 : 0,
                            'model_type' => 'App\Models\User',
                        ]);
                    }
                }

                $createdCount++;
            } catch (\Exception $e) {
                $nombre = ($asistente['primer_nombre'] ?? '').' '.($asistente['primer_apellido'] ?? '');
                $this->command->error("🔴 Error para asistente_id={$asistenteId} ({$nombre}): ".$e->getMessage());
                Log::error("AsistentesEscuelaSeeder - Error asistente_id={$asistenteId}: ".$e->getMessage());
                $errorCount++;
            }
        }

        $this->command->info('----------------------------------');
        $this->command->info('📊 REPORTE FINAL:');
        $this->command->info("✔️  Usuarios creados y roles asignados: {$createdCount}");
        $this->command->info("🟡 Registros saltados (sin credenciales o duplicados): {$skippedCount}");
        $this->command->info("🔴 Errores encontrados: {$errorCount}");
        $this->command->info('----------------------------------');
    }

    private function loadJson(string $path, string $rootKey): ?array
    {
        $fullPath = base_path('storage/app/'.$path);

        if (! file_exists($fullPath)) {
            $this->command->error("❌ Archivo no encontrado: {$path}");

            return null;
        }

        $json = json_decode(file_get_contents($fullPath), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->command->error("❌ Error al decodificar JSON ({$path}): ".json_last_error_msg());

            return null;
        }

        if (! isset($json[$rootKey]) || ! is_array($json[$rootKey])) {
            $this->command->error("❌ Clave raíz '{$rootKey}' no encontrada en: {$path}");

            return null;
        }

        return $json[$rootKey];
    }

    private function parseDate(?string $date): ?string
    {
        if (! $date || $date === '' || $date === '[NULL]') {
            return null;
        }

        try {
            return \Carbon\Carbon::parse($date)->format('Y-m-d H:i:s');
        } catch (\Exception) {
            return null;
        }
    }
}
