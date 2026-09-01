<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AsistentesEscuelaSeeder extends Seeder
{
    /**
     * Rutas de los archivos JSON de migración desde la BD Vision.
     */
    protected string $asistentesPath = 'seeders/ASISTENTES MIGRACION ESCUELAS.json';

    protected string $usuariosPath = 'seeders/USUARIOS MIGRACION ESCUELAS.json';

    protected string $tipoUsuariosPath = 'seeders/USUARIO TIPO USUARIOS MIGRACION ESCUELAS.json';

    protected string $tipoAsistentesPath = 'seeders/tipo_asistentes.json';

    protected string $todosTipoUsuariosPath = 'seeders/todos_tipo_usuarios.json';

    protected string $crecimientoAsistentesPath = 'seeders/crecimiento_asistentes.json';

    public function run(): void
    {
        // Obtener un tipo_usuario válido para cumplir con el NOT NULL (ej: el primero de la BD)
        $defaultTipoUsuario = \App\Models\TipoUsuario::first();
        $defaultTipoUsuarioId = $defaultTipoUsuario ? $defaultTipoUsuario->id : 1;

        $asistentes = $this->loadJson($this->asistentesPath, 'asistentes');
        $usuarios = $this->loadJson($this->usuariosPath, 'users');
        $tipoUsuarios = $this->loadJson($this->tipoUsuariosPath, 'usuario_tipo_usuario');
        $todosTipoUsuarios = $this->loadJson($this->todosTipoUsuariosPath);

        // Cargar tipo_asistentes.json (o fallback) para relacionar tipo_asistente_id con nombre
        $tipoAsistentesJson = $this->loadJson($this->tipoAsistentesPath);
        if (empty($tipoAsistentesJson)) {
            $tipoAsistentesJson = $this->loadJson('seeders/tipo_asistentes_202507301621.json');
        }

        $tipoAsistenteIdNombreMap = [];
        if (! empty($tipoAsistentesJson)) {
            foreach ($tipoAsistentesJson as $item) {
                $id = $item['id'] ?? null;
                $nombre = trim($item['nombre'] ?? $item['name'] ?? '');
                if ($id !== null && ! empty($nombre)) {
                    $tipoAsistenteIdNombreMap[$id] = $nombre;
                }
            }
        }

        // Cargar registros actuales de tipo_usuarios en la BD por nombre
        $tiposUsuariosDBMap = \App\Models\TipoUsuario::all()->keyBy(fn ($item) => trim($item->nombre));

        // Construir diccionario dinámico: ID tipo_usuario viejo => Nombre del Rol en Spatie
        $tipoUsuarioNombreMap = [];
        if (! empty($todosTipoUsuarios)) {
            foreach ($todosTipoUsuarios as $item) {
                $id = $item['id'] ?? null;
                $nombre = trim($item['nombre'] ?? $item['name'] ?? '');
                if ($id !== null && ! empty($nombre)) {
                    $tipoUsuarioNombreMap[$id] = $nombre;
                }
            }
        }

        if ($asistentes === null || $usuarios === null || $tipoUsuarios === null) {
            return;
        }

        $asistentesMap = collect($asistentes)->keyBy('id');
        $usuariosPorAsistenteId = collect($usuarios)->keyBy('asistente_id');
        $tipoUsuariosPorUsuarioId = collect($tipoUsuarios)->groupBy('usuario_id');

        if ($this->command) {
            $this->command->info('📊 Datos cargados:');
            $this->command->info("   Asistentes: {$asistentesMap->count()}");
            $this->command->info("   Usuarios (con credenciales): {$usuariosPorAsistenteId->count()}");
            $this->command->info('   Asignaciones tipo_usuario: '.collect($tipoUsuarios)->count());
            $this->command->info('   Map Mapeo Roles Dinámicos: '.count($tipoUsuarioNombreMap));
            $this->command->info('   Map Tipo Asistentes Dinámicos: '.count($tipoAsistenteIdNombreMap));
            $this->command->info('----------------------------------');
        }

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
                    if ($this->command) {
                        $this->command->warn("🟡 OMITIDO (identificación ya existe): {$identificacion}");
                    }
                    $skippedCount++;

                    continue;
                }

                if ($email && User::where('email', $email)->exists()) {
                    if ($this->command) {
                        $this->command->warn("🟡 OMITIDO (email ya existe): {$email}");
                    }
                    $skippedCount++;

                    continue;
                }

                // 1. Resolver el tipo_usuario_id legítimo para el User
                $oldTipoAsistenteId = $asistente['tipo_asistente_id'] ?? $asistente['tipo_asistente'] ?? null;
                $assignedTipoUsuarioId = $defaultTipoUsuarioId;

                if ($oldTipoAsistenteId && isset($tipoAsistenteIdNombreMap[$oldTipoAsistenteId])) {
                    $nombreTipoAsistente = $tipoAsistenteIdNombreMap[$oldTipoAsistenteId];
                    $tipoUsuarioModel = $tiposUsuariosDBMap->get(trim($nombreTipoAsistente));

                    if ($tipoUsuarioModel) {
                        $assignedTipoUsuarioId = $tipoUsuarioModel->id;
                    }
                }

                // 2. Crear el User
                $user = User::create([
                    'asistente_id' => $asistenteId,
                    'email' => $email,
                    'email_verified_at' => now(),
                    'password' => Hash::make($identificacion),
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
                    'foto' => $asistente['genero'] == 1 ? 'default-f.png' : 'default-m.png',
                    'tipo_usuario_id' => $assignedTipoUsuarioId,
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

                // Asignar Roles de Spatie
                $tiposDelUsuario = $tipoUsuariosPorUsuarioId->get($usuarioVision['id'], collect());

                $rolesAsignar = [];
                foreach ($tiposDelUsuario as $tipoAsignado) {
                    $tipoUsuarioIdViejo = $tipoAsignado['tipo_usuario_id'] ?? null;

                    if ($tipoUsuarioIdViejo && isset($tipoUsuarioNombreMap[$tipoUsuarioIdViejo])) {
                        $rolNombre = $tipoUsuarioNombreMap[$tipoUsuarioIdViejo];
                        if (! in_array($rolNombre, $rolesAsignar)) {
                            $rolesAsignar[] = $rolNombre;
                        }
                    }
                }

                $rolesAttachCount = 0;
                foreach ($rolesAsignar as $rolNombre) {
                    $roleModel = \Spatie\Permission\Models\Role::where('name', $rolNombre)->first();
                    if ($roleModel) {
                        $isActivo = ($rolesAttachCount === 0) ? 1 : 0;
                        $isDependiente = $roleModel->dependiente ? 1 : 0;

                        if (! $user->roles()->where('role_id', $roleModel->id)->exists()) {
                            $user->roles()->attach($roleModel->id, [
                                'activo' => $isActivo,
                                'dependiente' => $isDependiente,
                                'model_type' => 'App\Models\User',
                            ]);
                            $rolesAttachCount++;
                        }
                    }
                }

                if ($rolesAttachCount === 0) {
                    $roleModel = \Spatie\Permission\Models\Role::where('name', 'Oveja')->first()
                        ?? \Spatie\Permission\Models\Role::where('name', 'Nuevo')->first()
                        ?? \Spatie\Permission\Models\Role::where('dependiente', true)->first();

                    if ($roleModel && ! $user->roles()->where('role_id', $roleModel->id)->exists()) {
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
                if ($this->command) {
                    $this->command->error("🔴 Error para asistente_id={$asistenteId} ({$nombre}): ".$e->getMessage());
                }
                Log::error("AsistentesEscuelaSeeder - Error asistente_id={$asistenteId}: ".$e->getMessage());
                $errorCount++;
            }
        }

        // 3. Procesar migración de Crecimiento de Asistentes (crecimiento_asistentes.json)
        $crecimientoAsistentes = $this->loadJson($this->crecimientoAsistentesPath, 'crecimiento_asistentes');

        $crecimientoCreadoCount = 0;
        $skippedPasoInexistenteCount = 0;
        $skippedUserInexistenteCount = 0;

        if (! empty($crecimientoAsistentes)) {
            $pasosValidosMap = \App\Models\PasoCrecimiento::pluck('id', 'id')->toArray();
            $usersMap = User::whereNotNull('asistente_id')->pluck('id', 'asistente_id')->toArray();

            \App\Models\CrecimientoUsuario::withoutEvents(function () use (
                $crecimientoAsistentes,
                $pasosValidosMap,
                $usersMap,
                &$crecimientoCreadoCount,
                &$skippedPasoInexistenteCount,
                &$skippedUserInexistenteCount
            ) {
                foreach ($crecimientoAsistentes as $item) {
                    $pasoCrecimientoId = $item['paso_crecimiento_id'] ?? null;
                    $asistenteId = $item['asistente_id'] ?? null;

                    if (! $pasoCrecimientoId || ! isset($pasosValidosMap[$pasoCrecimientoId])) {
                        $skippedPasoInexistenteCount++;

                        continue;
                    }

                    if (! $asistenteId || ! isset($usersMap[$asistenteId])) {
                        $skippedUserInexistenteCount++;

                        continue;
                    }

                    $userId = $usersMap[$asistenteId];
                    $estadoId = (int) ($item['estado'] ?? 1);
                    $fecha = $this->parseDate($item['fecha'] ?? null);
                    $detalle = trim($item['detalle'] ?? '');

                    $registro = \App\Models\CrecimientoUsuario::firstOrCreate(
                        [
                            'paso_crecimiento_id' => $pasoCrecimientoId,
                            'user_id' => $userId,
                        ],
                        [
                            'estado_id' => $estadoId,
                            'fecha' => $fecha,
                            'detalle' => $detalle,
                            'created_at' => $this->parseDate($item['created_at'] ?? null) ?? now(),
                            'updated_at' => $this->parseDate($item['updated_at'] ?? null) ?? now(),
                        ]
                    );

                    if ($registro->wasRecentlyCreated) {
                        $crecimientoCreadoCount++;
                    }
                }
            });
        }

        if ($this->command) {
            $this->command->info('----------------------------------');
            $this->command->info('📊 REPORTE FINAL:');
            $this->command->info("✔️  Usuarios creados y roles asignados: {$createdCount}");
            $this->command->info("🟡 Registros saltados (sin credenciales o duplicados): {$skippedCount}");
            $this->command->info("🔴 Errores de usuario encontrados: {$errorCount}");
            if (! empty($crecimientoAsistentes)) {
                $this->command->info("✔️  Crecimientos asignados a asistentes: {$crecimientoCreadoCount}");
                $this->command->info("🟡 Crecimientos omitidos por paso no existente en v12: {$skippedPasoInexistenteCount}");
                $this->command->info("🟡 Crecimientos omitidos por usuario no encontrado: {$skippedUserInexistenteCount}");
            }
            $this->command->info('----------------------------------');
        }
    }

    private function loadJson(string $path, ?string $rootKey = null): ?array
    {
        $fullPath = base_path('storage/app/'.$path);

        if (! file_exists($fullPath)) {
            if ($this->command) {
                $this->command->error("❌ Archivo no encontrado: {$path}");
            }

            return null;
        }

        $json = json_decode(file_get_contents($fullPath), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            if ($this->command) {
                $this->command->error("❌ Error al decodificar JSON ({$path}): ".json_last_error_msg());
            }

            return null;
        }

        if ($rootKey !== null) {
            if (! isset($json[$rootKey]) || ! is_array($json[$rootKey])) {
                if ($this->command) {
                    $this->command->error("❌ Clave raíz '{$rootKey}' no encontrada en: {$path}");
                }

                return null;
            }

            return $json[$rootKey];
        }

        if (is_array($json)) {
            if (array_is_list($json)) {
                return $json;
            }

            foreach ($json as $value) {
                if (is_array($value)) {
                    return $value;
                }
            }
        }

        return null;
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
