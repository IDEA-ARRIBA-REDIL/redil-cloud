<?php

namespace Database\Seeders;

use App\Models\Role as ModelsRole;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Ruta relativa a storage/app para el archivo JSON de tipos de usuario (roles).
     */
    protected string $tipoUsuariosPath = 'seeders/todos_tipo_usuarios.json';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // RolAdmistrador
        $superAdmin = Role::firstOrCreate(['name' => 'Super Administrador Prueba'], ['icono' => 'ti ti-key', 'dependiente' => false]);

        // creo relacion create_privilegios_tipo_grupo_rol
        // Usamos la variable $superAdmin en lugar de find(1)
        if ($superAdmin->wasRecentlyCreated) {
            ModelsRole::find($superAdmin->id)?->privilegiosTiposGrupo()->attach(3, ['asignar_asistente' => false, 'desvincular_asistente' => true, 'asignar_encargado' => false, 'desvincular_encargado' => true]);
            ModelsRole::find($superAdmin->id)?->privilegiosTiposGrupo()->attach(4, ['asignar_asistente' => true, 'desvincular_asistente' => false, 'asignar_encargado' => true, 'desvincular_encargado' => false]);
        }

        // RolPastor
        $pastor = Role::firstOrCreate(['name' => 'Pastor Prueba'], ['icono' => 'ti ti-user-shield', 'dependiente' => true]);

        // RolLider
        $lider = Role::firstOrCreate(['name' => 'Lider Prueba'], ['icono' => 'ti ti-user-star', 'dependiente' => true]);

        // creo relacion create_privilegios_tipo_grupo_rol
        // Usamos la variable $lider en lugar de find(3)
        if ($lider->wasRecentlyCreated) {
            ModelsRole::find($lider->id)?->privilegiosTiposGrupo()->attach(2, ['asignar_asistente' => true, 'desvincular_asistente' => true, 'asignar_encargado' => false, 'desvincular_encargado' => false]);
        }

        // RolOveja
        $oveja = Role::firstOrCreate(['name' => 'Oveja Prueba'], ['icono' => 'ti ti-mood-heart', 'dependiente' => true]);

        // RolNuevo
        $nuevo = Role::firstOrCreate(['name' => 'Nuevo Prueba'], ['icono' => 'ti ti-paper-bag', 'dependiente' => true]);

        // RolEmpleado (Corregí esto, en tu original creabas 'Oveja' dos veces)
        $empleado = Role::firstOrCreate(['name' => 'Empleado Prueba'], ['icono' => 'ti ti-brand-ctemplar', 'dependiente' => true]);

        // RolDesarrollador
        $desarrollador = Role::firstOrCreate(['name' => 'Desarrollador Prueba'], ['icono' => 'ti ti-anchor', 'dependiente' => true]);

        // RolDP
        $pdp = Role::firstOrCreate(['name' => 'PDP Prueba'], ['icono' => 'ti ti-paperclip', 'dependiente' => false, 'es_encargado_pdp' => true]);
        $cajero = Role::firstOrCreate(['name' => 'Cajero PDP Prueba'], ['icono' => 'ti ti-paperclip', 'dependiente' => false, 'es_cajero_pdp' => true]);

        // / roles para escuelas
        $alumno = Role::firstOrCreate(['name' => 'Alumno Prueba'], ['icono' => 'ti ti-user-square-rounded', 'dependiente' => false]);
        $maestro = Role::firstOrCreate(['name' => 'Maestro Prueba'], ['icono' => 'ti ti-user-square', 'dependiente' => false, 'es_maestro' => true]);
        if (! $maestro->es_maestro) {
            $maestro->update(['es_maestro' => true]);
        }
        $coordinador = Role::firstOrCreate(['name' => 'Coordinador Prueba'], ['icono' => 'ti ti ti-user-pentagon', 'dependiente' => false]);
        $administrador = Role::firstOrCreate(['name' => 'Administrativo Prueba'], ['icono' => 'ti ti ti-user-pentagon', 'dependiente' => false]);

        $consejero = Role::firstOrCreate(['name' => 'Consejero Prueba'], ['icono' => 'ti ti ti-message-circle-user', 'dependiente' => false, 'es_consejero' => true]);

        // roles consolidacion
        $consolidadorMedellin = Role::firstOrCreate(['name' => 'Consolidador Medellin Prueba'], ['icono' => 'ti ti ti-user', 'dependiente' => false, 'zona_de_consolidacion_id' => 5]);
        $consolidadorBogota = Role::firstOrCreate(['name' => 'Consolidador Bogota Prueba'], ['icono' => 'ti ti ti-user', 'dependiente' => false, 'zona_de_consolidacion_id' => 6]);

        $intercesor = Role::firstOrCreate(['name' => 'Intercesor Prueba'], ['icono' => 'ti ti ti-message-circle-user', 'dependiente' => false, 'es_intercesor' => true]);

        // Cargar y procesar los roles desde el JSON todos_tipo_usuarios.json
        $rolesJson = $this->loadJson($this->tipoUsuariosPath);

        if (! empty($rolesJson)) {
            $createdCount = 0;

            foreach ($rolesJson as $item) {
                $nombre = trim($item['nombre'] ?? $item['name'] ?? '');

                if (empty($nombre)) {
                    continue;
                }

                $dependienteRaw = $item['dependiente'] ?? null;
                $isDependiente = ($dependienteRaw === true || $dependienteRaw === 1 || $dependienteRaw === '1' || $dependienteRaw === 'true');

                $attributes = [
                    'icono' => 'ti ti-user',
                    'dependiente' => $isDependiente,
                ];

                if (isset($item['es_maestro'])) {
                    $attributes['es_maestro'] = (bool) $item['es_maestro'];
                }
                if (isset($item['es_consejero'])) {
                    $attributes['es_consejero'] = (bool) $item['es_consejero'];
                }
                if (isset($item['es_cajero_pdp'])) {
                    $attributes['es_cajero_pdp'] = (bool) $item['es_cajero_pdp'];
                }
                if (isset($item['es_encargado_pdp'])) {
                    $attributes['es_encargado_pdp'] = (bool) $item['es_encargado_pdp'];
                }
                if (isset($item['es_intercesor'])) {
                    $attributes['es_intercesor'] = (bool) $item['es_intercesor'];
                }
                if (isset($item['zona_de_consolidacion_id'])) {
                    $attributes['zona_de_consolidacion_id'] = $item['zona_de_consolidacion_id'];
                }

                $role = Role::firstOrCreate(['name' => $nombre], $attributes);

                if ($role->wasRecentlyCreated) {
                    $createdCount++;
                }
            }

            if ($this->command) {
                $this->command->info("✔️ Roles cargados desde JSON ({$this->tipoUsuariosPath}): {$createdCount} nuevos roles creados.");
            }
        }
    }

    /**
     * Carga y decodifica un archivo JSON ubicado en storage/app.
     */
    private function loadJson(string $path): ?array
    {
        $fullPath = base_path('storage/app/'.$path);

        if (! file_exists($fullPath)) {
            if ($this->command) {
                $this->command->warn("🟡 Archivo JSON no encontrado: {$path}");
            }

            return null;
        }

        $content = file_get_contents($fullPath);
        $json = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            if ($this->command) {
                $this->command->error("❌ Error al decodificar JSON ({$path}): ".json_last_error_msg());
            }

            return null;
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
}
