<?php

namespace Database\Seeders;

use App\Models\TipoUsuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BosaDataSeeder extends Seeder
{
    /**
     * Ruta base de los archivos JSON dentro de storage/app.
     */
    protected string $basePath = 'seeders/';

    /**
     * Mapa de archivos JSON de Bosa.
     *
     * @var array<string, string>
     */
    protected array $archivos = [
        'users' => 'users_bosa.json',
        'asistentes' => 'asistentes_bosa.json',
        'grupos' => 'grupos_bosa.json',
        'encargados' => 'encargados_grupo_bosa.json',
        'integrantes' => 'integrantes_grupo_bosa.json',
        'roles' => 'usuario_tipo_usuario_bosa.json',
        'lideres' => 'lideres_bosa.json',
    ];

    /**
     * Run the database seeds.
     *
     * Orden de ejecución:
     * 1. Usuarios (fusiona asistentes_bosa + users_bosa)
     * 2. Grupos
     * 3. Encargados de grupo (pivote)
     * 4. Integrantes de grupo (pivote)
     * 5. Roles (usuario_tipo_usuario → model_has_roles)
     */
    public function run(): void
    {
        // 1. Verificar que todos los archivos existan antes de iniciar
        foreach ($this->archivos as $key => $filename) {
            if (! file_exists($this->rutaArchivo($filename))) {
                $this->command->error("❌ Archivo no encontrado: {$filename}");

                return;
            }
        }

        $this->command->info('✅ Todos los archivos de Bosa encontrados. Iniciando importación...');

        // 2. Importar en orden de dependencias
        $this->importarUsuarios();
        $this->importarLideres();
        $this->importarGrupos();
        $this->importarEncargados();
        $this->importarIntegrantes();
        $this->importarRoles();

        $this->command->info('');
        $this->command->info('🎉 ¡Importación de datos de Bosa finalizada!');
    }

    /**
     * PASO 1: Importa usuarios fusionando asistentes_bosa + users_bosa.
     *
     * - asistentes_bosa → datos personales (nombres, identificación, dirección, etc.)
     * - users_bosa → datos de login (email, password hash, activo)
     * - Se fusionan por: asistentes.id == users.asistente_id
     *
     * Usa DB::table directo para:
     * - Evitar el cast 'hashed' que re-hashearía passwords ya hasheados
     * - Evitar model events de bitácoras (TipoUsuario, EstadoCivil, Sede)
     * - Evitar restricciones de $fillable en el modelo User
     */
    private function importarUsuarios(): void
    {
        $this->command->info('');
        $this->command->info('👤 Paso 1/5: Importando usuarios...');

        $asistentes = collect($this->leerJson('asistentes'))->keyBy('id');
        $usersData = collect($this->leerJson('users'))->keyBy('asistente_id');

        $creados = 0;
        $omitidos = 0;
        $errores = 0;

        foreach ($asistentes as $asistente) {
            try {
                $asistenteId = $asistente['id'];

                // Verificar duplicado por ID
                if (DB::table('users')->where('id', $asistenteId)->exists()) {
                    $omitidos++;

                    continue;
                }

                // Obtener datos de login del user correspondiente
                $userData = $usersData->get($asistenteId);
                $email = $userData['email'] ?? "bosa.{$asistenteId}@redil-import.com";

                // Verificar duplicado por email
                if (DB::table('users')->where('email', $email)->exists()) {
                    $this->command->warn("   🟡 Email duplicado: {$email} (asistente #{$asistenteId})");
                    $omitidos++;

                    continue;
                }

                // Verificar duplicado por identificación
                $identificacion = $this->valorONulo($asistente, 'identificacion');
                if ($identificacion && DB::table('users')->where('identificacion', $identificacion)->exists()) {
                    $this->command->warn("   🟡 Identificación duplicada: {$identificacion}");
                    $omitidos++;

                    continue;
                }

                DB::table('users')->insert([
                    // --- Identificadores ---
                    'id' => $asistenteId,
                    'asistente_id' => $asistenteId,

                    // --- Login (desde users_bosa) ---
                    'email' => $email,
                    'email_verified_at' => now(),
                    'password' => $userData['password'] ?? bcrypt('12345678'),

                    // --- Datos personales (desde asistentes_bosa) ---
                    'primer_nombre' => mb_substr($asistente['primer_nombre'] ?? '', 0, 50),
                    'primer_apellido' => mb_substr($asistente['primer_apellido'] ?? '', 0, 50),
                    'segundo_nombre' => mb_substr($this->limpiar($asistente['segundo_nombre'] ?? ''), 0, 50),
                    'segundo_apellido' => mb_substr($this->limpiar($asistente['segundo_apellido'] ?? ''), 0, 50),
                    'genero' => $asistente['genero'] ?? 0,
                    'fecha_nacimiento' => $asistente['fecha_nacimiento'] ?? null,
                    'tipo_identificacion_id' => $this->valorONulo($asistente, 'tipo_identificacion'),
                    'identificacion' => $identificacion ? mb_substr($identificacion, 0, 20) : null,
                    'direccion' => mb_substr($asistente['direccion'] ?: 'No especificada', 0, 200),
                    'telefono_fijo' => mb_substr($this->limpiar($asistente['telefono_fijo'] ?? ''), 0, 20) ?: null,
                    'telefono_movil' => mb_substr($asistente['telefono_movil'] ?: '0000000', 0, 20),
                    'telefono_otro' => mb_substr($this->limpiar($asistente['telefono_otro'] ?? ''), 0, 20) ?: null,
                    'foto' => mb_substr($asistente['foto'] ?? 'default-m.png', 0, 20),
                    'fecha_ingreso' => $asistente['fecha_ingreso'] ?? null,
                    'indicaciones_medicas' => $this->vacioANulo($asistente['indicaciones_medicas'] ?? ''),

                    // --- Relaciones FK ---
                    'estado_civil_id' => $asistente['estado_civil'] ?: 1,
                    'tipo_usuario_id' => $this->valorONulo($asistente, 'tipo_asistente_id'),
                    'sede_id' => $this->valorONulo($asistente, 'sede_id'),
                    'pais_id' => $this->valorONulo($asistente, 'pais_id'),
                    'barrio_id' => $this->valorONulo($asistente, 'barrio_id'),
                    'tipo_vinculacion_id' => $asistente['tipo_vinculacion_id'] ?? 1,
                    'nivel_academico_id' => $this->valorONulo($asistente, 'nivel_academico'),
                    'estado_nivel_academico_id' => $this->valorONulo($asistente, 'estado_nivel_academico'),
                    'profesion_id' => $this->valorONulo($asistente, 'profesion'),
                    'ocupacion_id' => $this->valorONulo($asistente, 'ocupacion'),
                    'sector_economico_id' => $this->valorONulo($asistente, 'sector_economico'),
                    'tipo_vivienda_id' => $this->valorONulo($asistente, 'tipo_vivienda'),
                    'tipo_sangre_id' => $this->valorONulo($asistente, 'tipo_sangre'),

                    // --- Datos de acudiente ---
                    'nombre_acudiente' => $this->valorONulo($asistente, 'nombre_acudiente') ? mb_substr($asistente['nombre_acudiente'], 0, 200) : null,
                    'telefono_acudiente' => $this->valorONulo($asistente, 'telefono_acudiente') ? mb_substr($asistente['telefono_acudiente'], 0, 20) : null,
                    'tipo_identificacion_acudiente_id' => $this->valorONulo($asistente, 'tipo_identificacion_acudiente'),
                    'identificacion_acudiente' => $this->valorONulo($asistente, 'identificacion_acudiente') ? mb_substr($asistente['identificacion_acudiente'], 0, 20) : null,

                    // --- Archivos ---
                    'archivo_a' => $this->valorONulo($asistente, 'archivo_a'),
                    'archivo_b' => $this->valorONulo($asistente, 'archivo_b'),
                    'archivo_c' => $this->valorONulo($asistente, 'archivo_c'),
                    'archivo_d' => $this->valorONulo($asistente, 'archivo_d'),

                    // --- Seguimiento y reportes ---
                    'ultimo_reporte_grupo' => $asistente['ultimo_reporte_grupo'] ?? '2016-01-01 05:00:01',
                    'ultimo_reporte_grupo_auxiliar' => $asistente['ultimo_reporte_grupo_auxiliar'] ?? '2016-01-01 05:00:01',
                    'ultimo_reporte_reunion' => $asistente['ultimo_reporte_reunion'] ?? '2016-01-01 05:00:01',
                    'ultimo_reporte_reunion_auxiliar' => $asistente['ultimo_reporte_reunion_auxiliar'] ?? '2016-01-01 05:00:01',
                    'indice_grafico_ministerial' => $asistente['indice_grafico_ministerial'] ?? null,
                    'usuario_creacion_id' => $this->valorONulo($asistente, 'usuario_creacion_id'),

                    // --- Estado ---
                    'activo' => $userData['activo'] ?? true,
                    'trasladado' => true,
                    'creado_como_menor_edad' => $asistente['creado_como_menor_edad'] ?? false,
                    'creado_como_mayor_edad' => $asistente['creado_como_mayor_edad'] ?? true,
                    'activado_como_mayor_edad' => $asistente['activado_como_mayor_edad'] ?? false,
                    'esta_aprobado' => $asistente['esta_aprobado'] ?? true,

                    // --- Timestamps ---
                    'created_at' => $asistente['created_at'] ?? now(),
                    'updated_at' => $asistente['updated_at'] ?? now(),
                    'deleted_at' => $asistente['deleted_at'] ?? null,
                ]);

                $creados++;
            } catch (\Exception $e) {
                $id = $asistente['id'] ?? '?';
                $this->command->error("   🔴 Error usuario #{$id}: ".$e->getMessage());
                Log::error("BosaDataSeeder - Error usuario #{$id}: ".$e->getMessage());
                $errores++;
            }
        }

        $this->command->info("   ✔️ Creados: {$creados} | Omitidos: {$omitidos} | Errores: {$errores}");
    }

    /**
     * PASO 1.5: Importa líderes faltantes desde lideres_bosa.json.
     * Estos ya vienen fusionados (asistentes + users).
     */
    private function importarLideres(): void
    {
        $this->command->info('');
        $this->command->info('👤 Paso 1.5/5: Importando líderes faltantes...');

        $lideres = $this->leerJson('lideres');

        $creados = 0;
        $omitidos = 0;
        $errores = 0;

        foreach ($lideres as $lider) {
            try {
                // El JOIN sobreescribe el 'id' con el último leído, así que usamos asistente_id
                $asistenteId = $lider['asistente_id'] ?? null;

                if (! $asistenteId) {
                    $omitidos++;

                    continue;
                }

                // Verificar duplicado por ID
                if (DB::table('users')->where('id', $asistenteId)->exists()) {
                    $omitidos++;

                    continue;
                }

                $email = $lider['email'] ?? "lider.{$asistenteId}@redil-import.com";

                // Verificar duplicado por email
                if (DB::table('users')->where('email', $email)->exists()) {
                    $this->command->warn("   🟡 Email duplicado en líder: {$email}");
                    $omitidos++;

                    continue;
                }

                $identificacion = $this->valorONulo($lider, 'identificacion');
                if ($identificacion && DB::table('users')->where('identificacion', $identificacion)->exists()) {
                    $this->command->warn("   🟡 Identificación duplicada en líder: {$identificacion}");
                    $omitidos++;

                    continue;
                }

                DB::table('users')->insert([
                    // --- Identificadores ---
                    'id' => $asistenteId,
                    'asistente_id' => $asistenteId,

                    // --- Login (desde users) ---
                    'email' => $email,
                    'email_verified_at' => now(),
                    'password' => $lider['password'] ?? bcrypt('12345678'),

                    // --- Datos personales (desde asistentes) ---
                    'primer_nombre' => mb_substr($lider['primer_nombre'] ?? '', 0, 50),
                    'primer_apellido' => mb_substr($lider['primer_apellido'] ?? '', 0, 50),
                    'segundo_nombre' => mb_substr($this->limpiar($lider['segundo_nombre'] ?? ''), 0, 50),
                    'segundo_apellido' => mb_substr($this->limpiar($lider['segundo_apellido'] ?? ''), 0, 50),
                    'genero' => $lider['genero'] ?? 0,
                    'fecha_nacimiento' => $lider['fecha_nacimiento'] ?? null,
                    'tipo_identificacion_id' => $this->valorONulo($lider, 'tipo_identificacion'),
                    'identificacion' => $identificacion ? mb_substr($identificacion, 0, 20) : null,
                    'direccion' => mb_substr($lider['direccion'] ?: 'No especificada', 0, 200),
                    'telefono_fijo' => mb_substr($this->limpiar($lider['telefono_fijo'] ?? ''), 0, 20) ?: null,
                    'telefono_movil' => mb_substr($lider['telefono_movil'] ?: '0000000', 0, 20),
                    'telefono_otro' => mb_substr($this->limpiar($lider['telefono_otro'] ?? ''), 0, 20) ?: null,
                    'foto' => mb_substr($lider['foto'] ?? 'default-m.png', 0, 20),
                    'fecha_ingreso' => $lider['fecha_ingreso'] ?? null,
                    'indicaciones_medicas' => $this->vacioANulo($lider['indicaciones_medicas'] ?? ''),

                    // --- Relaciones FK ---
                    'estado_civil_id' => $lider['estado_civil'] ?: 1,
                    'tipo_usuario_id' => $this->valorONulo($lider, 'tipo_asistente_id'),
                    'sede_id' => $this->valorONulo($lider, 'sede_id'),
                    'pais_id' => $this->valorONulo($lider, 'pais_id'),
                    'barrio_id' => $this->valorONulo($lider, 'barrio_id'),
                    'tipo_vinculacion_id' => $lider['tipo_vinculacion_id'] ?? 1,
                    'nivel_academico_id' => $this->valorONulo($lider, 'nivel_academico'),
                    'estado_nivel_academico_id' => $this->valorONulo($lider, 'estado_nivel_academico'),
                    'profesion_id' => $this->valorONulo($lider, 'profesion'),
                    'ocupacion_id' => $this->valorONulo($lider, 'ocupacion'),
                    'sector_economico_id' => $this->valorONulo($lider, 'sector_economico'),
                    'tipo_vivienda_id' => $this->valorONulo($lider, 'tipo_vivienda'),
                    'tipo_sangre_id' => $this->valorONulo($lider, 'tipo_sangre'),

                    // --- Datos de acudiente ---
                    'nombre_acudiente' => $this->valorONulo($lider, 'nombre_acudiente') ? mb_substr($lider['nombre_acudiente'], 0, 200) : null,
                    'telefono_acudiente' => $this->valorONulo($lider, 'telefono_acudiente') ? mb_substr($lider['telefono_acudiente'], 0, 20) : null,
                    'tipo_identificacion_acudiente_id' => $this->valorONulo($lider, 'tipo_identificacion_acudiente'),
                    'identificacion_acudiente' => $this->valorONulo($lider, 'identificacion_acudiente') ? mb_substr($lider['identificacion_acudiente'], 0, 20) : null,

                    // --- Archivos ---
                    'archivo_a' => $this->valorONulo($lider, 'archivo_a'),
                    'archivo_b' => $this->valorONulo($lider, 'archivo_b'),
                    'archivo_c' => $this->valorONulo($lider, 'archivo_c'),
                    'archivo_d' => $this->valorONulo($lider, 'archivo_d'),

                    // --- Seguimiento y reportes ---
                    'ultimo_reporte_grupo' => $lider['ultimo_reporte_grupo'] ?? '2016-01-01 05:00:01',
                    'ultimo_reporte_grupo_auxiliar' => $lider['ultimo_reporte_grupo_auxiliar'] ?? '2016-01-01 05:00:01',
                    'ultimo_reporte_reunion' => $lider['ultimo_reporte_reunion'] ?? '2016-01-01 05:00:01',
                    'ultimo_reporte_reunion_auxiliar' => $lider['ultimo_reporte_reunion_auxiliar'] ?? '2016-01-01 05:00:01',
                    'indice_grafico_ministerial' => $lider['indice_grafico_ministerial'] ?? null,
                    'usuario_creacion_id' => $this->valorONulo($lider, 'usuario_creacion_id'),

                    // --- Estado ---
                    'activo' => $lider['activo'] ?? true,
                    'trasladado' => true,
                    'creado_como_menor_edad' => $lider['creado_como_menor_edad'] ?? false,
                    'creado_como_mayor_edad' => $lider['creado_como_mayor_edad'] ?? true,
                    'activado_como_mayor_edad' => $lider['activado_como_mayor_edad'] ?? false,
                    'esta_aprobado' => $lider['esta_aprobado'] ?? true,

                    // --- Timestamps ---
                    'created_at' => $lider['created_at'] ?? now(),
                    'updated_at' => $lider['updated_at'] ?? now(),
                    'email_verified_at' => $lider['email_verified_at'] ?? now(),
                    'deleted_at' => $lider['deleted_at'] ?? null,
                ]);

                $creados++;
            } catch (\Exception $e) {
                $id = $lider['asistente_id'] ?? '?';
                $this->command->error("   🔴 Error líder #{$id}: ".$e->getMessage());
                Log::error("BosaDataSeeder - Error líder #{$id}: ".$e->getMessage());
                $errores++;
            }
        }

        $this->command->info("   ✔️ Creados: {$creados} | Omitidos: {$omitidos} | Errores: {$errores}");
    }

    /**
     * PASO 2: Importa grupos desde grupos_bosa.
     *
     * Mapeos clave:
     * - tipo_vivienda → tipo_vivienda_id
     * - tipo_usuario_de_creacion_id → rol_de_creacion_id
     *
     * Usa DB::table para evitar model events (BitacoraSedeGrupo, BitacoraTipoGrupo)
     * que fallarían con auth()->id() = null en contexto de seeder.
     */
    private function importarGrupos(): void
    {
        $this->command->info('');
        $this->command->info('👥 Paso 2/5: Importando grupos...');

        $gruposData = $this->leerJson('grupos');
        $creados = 0;
        $omitidos = 0;
        $errores = 0;

        foreach ($gruposData as $grupo) {
            try {
                if (DB::table('grupos')->where('id', $grupo['id'])->exists()) {
                    $omitidos++;

                    continue;
                }

                DB::table('grupos')->insert([
                    'id' => $grupo['id'],
                    'nombre' => $grupo['nombre'],
                    'direccion' => $grupo['direccion'] ?? null,
                    'telefono' => $grupo['telefono'] ?? null,
                    'rhema' => $this->vacioANulo($grupo['rhema'] ?? ''),
                    'fecha_apertura' => $this->vacioANulo($grupo['fecha_apertura'] ?? ''),
                    'dia' => $grupo['dia'],
                    'hora' => $grupo['hora'],
                    'nivel' => $grupo['nivel'] ?? null,
                    'dado_baja' => $grupo['dado_baja'] ?? false,
                    'tipo_grupo_id' => $grupo['tipo_grupo_id'],
                    'tipo_vivienda_id' => $grupo['tipo_vivienda'] ?? null,
                    'barrio_id' => $grupo['barrio_id'] ?? null,
                    'dia_planeacion' => $grupo['dia_planeacion'] ?? null,
                    'codigo' => $grupo['codigo'] ?? null,
                    'hora_planeacion' => $grupo['hora_planeacion'] ?? null,
                    'barrio_auxiliar' => $grupo['barrio_auxiliar'] ?? null,
                    'latitud' => $grupo['latitud'] ?? null,
                    'longitud' => $grupo['longitud'] ?? null,
                    'contiene_amo' => $grupo['contiene_amo'] ?? false,
                    'inactivo' => $grupo['inactivo'] ?? false,
                    'sede_id' => $grupo['sede_id'] ?? null,
                    'ultimo_reporte_grupo' => $grupo['ultimo_reporte_grupo'] ?? null,
                    'ultimo_reporte_grupo_auxiliar' => $grupo['ultimo_reporte_grupo_auxiliar'] ?? null,
                    'rol_de_creacion_id' => $grupo['tipo_usuario_de_creacion_id'] ?? null,
                    'asistente_de_creacion_id' => $grupo['asistente_de_creacion_id'] ?? null,
                    'indice_grafico_ministerial' => $grupo['indice_grafico_ministerial'] ?? null,
                    'usuario_creacion_id' => $grupo['usuario_creacion_id'] ?? null,
                    'created_at' => $grupo['created_at'] ?? now(),
                    'updated_at' => $grupo['updated_at'] ?? now(),
                ]);

                $creados++;
            } catch (\Exception $e) {
                $id = $grupo['id'] ?? '?';
                $this->command->error("   🔴 Error grupo #{$id}: ".$e->getMessage());
                Log::error("BosaDataSeeder - Error grupo #{$id}: ".$e->getMessage());
                $errores++;
            }
        }

        $this->command->info("   ✔️ Creados: {$creados} | Omitidos: {$omitidos} | Errores: {$errores}");
    }

    /**
     * PASO 3: Importa encargados de grupo (tabla pivote).
     *
     * Mapeo: asistente_id → user_id
     */
    private function importarEncargados(): void
    {
        $this->command->info('');
        $this->command->info('🔑 Paso 3/5: Importando encargados de grupo...');

        $encargadosData = $this->leerJson('encargados');
        $creados = 0;
        $omitidos = 0;
        $errores = 0;

        foreach ($encargadosData as $encargado) {
            try {
                if (! isset($encargado['id'])) {
                    $omitidos++;

                    continue;
                }

                DB::table('encargados_grupo')->updateOrInsert(
                    ['id' => $encargado['id']],
                    [
                        'grupo_id' => $encargado['grupo_id'],
                        'user_id' => $encargado['asistente_id'],
                        'created_at' => $encargado['created_at'] ?? now(),
                        'updated_at' => $encargado['updated_at'] ?? now(),
                    ]
                );

                $creados++;
            } catch (\Exception $e) {
                $id = $encargado['id'] ?? '?';
                $this->command->error("   🔴 Error encargado #{$id}: ".$e->getMessage());
                Log::error("BosaDataSeeder - Error encargado #{$id}: ".$e->getMessage());
                $errores++;
            }
        }

        $this->command->info("   ✔️ Procesados: {$creados} | Omitidos: {$omitidos} | Errores: {$errores}");
    }

    /**
     * PASO 4: Importa integrantes de grupo (tabla pivote).
     *
     * Mapeo: asistente_id → user_id
     */
    private function importarIntegrantes(): void
    {
        $this->command->info('');
        $this->command->info('👫 Paso 4/5: Importando integrantes de grupo...');

        $integrantesData = $this->leerJson('integrantes');
        $creados = 0;
        $omitidos = 0;
        $errores = 0;

        foreach ($integrantesData as $integrante) {
            try {
                if (! isset($integrante['id'])) {
                    $omitidos++;

                    continue;
                }

                DB::table('integrantes_grupo')->updateOrInsert(
                    ['id' => $integrante['id']],
                    [
                        'grupo_id' => $integrante['grupo_id'],
                        'user_id' => $integrante['asistente_id'],
                        'created_at' => $integrante['created_at'] ?? now(),
                        'updated_at' => $integrante['updated_at'] ?? now(),
                    ]
                );

                $creados++;
            } catch (\Exception $e) {
                $id = $integrante['id'] ?? '?';
                $this->command->error("   🔴 Error integrante #{$id}: ".$e->getMessage());
                Log::error("BosaDataSeeder - Error integrante #{$id}: ".$e->getMessage());
                $errores++;
            }
        }

        $this->command->info("   ✔️ Procesados: {$creados} | Omitidos: {$omitidos} | Errores: {$errores}");
    }

    /**
     * PASO 5: Importa roles desde usuario_tipo_usuario_bosa → model_has_roles.
     *
     * Mapeo:
     * - usuario_tipo_usuario.usuario_id → model_has_roles.model_id
     * - usuario_tipo_usuario.tipo_usuario_id → TipoUsuario::id_rol_dependiente → model_has_roles.role_id
     * - usuario_tipo_usuario.activo → model_has_roles.activo
     * - usuario_tipo_usuario.dependiente → model_has_roles.dependiente
     */
    private function importarRoles(): void
    {
        $this->command->info('');
        $this->command->info('🛡️ Paso 5/5: Importando roles de usuario...');

        $rolesData = $this->leerJson('roles');

        // Precargar mapeo: TipoUsuario ID → Role ID (Spatie)
        $tipoUsuarioMap = TipoUsuario::whereNotNull('id_rol_dependiente')
            ->pluck('id_rol_dependiente', 'id')
            ->toArray();

        $creados = 0;
        $omitidos = 0;
        $errores = 0;

        foreach ($rolesData as $record) {
            try {
                $userId = $record['usuario_id'];
                $tipoUsuarioId = $record['tipo_usuario_id'];

                // Verificar que el usuario exista en la BD
                if (! DB::table('users')->where('id', $userId)->exists()) {
                    $omitidos++;

                    continue;
                }

                // Buscar el Role ID correspondiente al TipoUsuario
                $roleId = $tipoUsuarioMap[$tipoUsuarioId] ?? null;
                if (! $roleId) {
                    $omitidos++;

                    continue;
                }

                DB::table('model_has_roles')->updateOrInsert(
                    [
                        'role_id' => $roleId,
                        'model_id' => $userId,
                        'model_type' => 'App\\Models\\User',
                    ],
                    [
                        'activo' => $record['activo'] ?? false,
                        'dependiente' => $record['dependiente'] ?? true,
                    ]
                );

                $creados++;
            } catch (\Exception $e) {
                $id = $record['id'] ?? '?';
                $this->command->error("   🔴 Error rol #{$id}: ".$e->getMessage());
                Log::error("BosaDataSeeder - Error rol #{$id}: ".$e->getMessage());
                $errores++;
            }
        }

        $this->command->info("   ✔️ Procesados: {$creados} | Omitidos: {$omitidos} | Errores: {$errores}");
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    /**
     * Construye la ruta absoluta del archivo JSON.
     */
    private function rutaArchivo(string $filename): string
    {
        return base_path('storage/app/'.$this->basePath.$filename);
    }

    /**
     * Lee y decodifica un archivo JSON de Bosa.
     *
     * Los archivos tienen como clave un query SQL y como valor el array de registros.
     * Extraemos el valor de la primera (y única) clave.
     *
     * @return array<int, array<string, mixed>>
     */
    private function leerJson(string $key): array
    {
        $content = file_get_contents($this->rutaArchivo($this->archivos[$key]));
        $data = json_decode($content, true);

        if (! $data || ! is_array($data)) {
            $this->command->error("   Error: No se pudo decodificar {$this->archivos[$key]}");

            return [];
        }

        // La primera clave es el query SQL, el valor es el array de registros
        return reset($data) ?: [];
    }

    /**
     * Limpia un valor de texto: trim y reemplaza strings vacíos o solo espacios.
     */
    private function limpiar(?string $valor): string
    {
        if ($valor === null) {
            return '';
        }

        $trimmed = trim($valor);

        return $trimmed === '' ? '' : $trimmed;
    }

    /**
     * Retorna el valor de un campo o null si está vacío/no existe.
     * Útil para campos FK que deben ser null en lugar de 0 o ''.
     */
    private function valorONulo(array $data, string $key): mixed
    {
        $value = $data[$key] ?? null;

        return ($value === '' || $value === null || $value === 0) ? null : $value;
    }

    /**
     * Convierte strings vacíos a null.
     * Útil para campos como rhema, fecha_apertura, etc.
     */
    private function vacioANulo(mixed $valor): mixed
    {
        return ($valor === '' || $valor === null) ? null : $valor;
    }
}
