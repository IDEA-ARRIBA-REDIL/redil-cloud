<?php

namespace Database\Seeders;

use App\Models\TipoUsuario;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class TipoUsuarioSeeder extends Seeder
{
    /**
     * Rutas de los archivos JSON.
     */
    protected string $tipoAsistentesPath = 'seeders/tipo_asistentes.json';
    protected string $todosTipoUsuariosPath = 'seeders/todos_tipo_usuarios.json';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Conservar los registros base de prueba si no existen
        TipoUsuario::firstOrCreate(
            ['nombre' => 'Pastor prueba'],
            [
                'nombre_plural' => 'Pastores',
                'color' => '#6b2682',
                'icono' => 'ti ti-book',
                'imagen' => 'indicador_general.png',
                'id_rol_dependiente' => 2,
                'puntaje' => 5,
            ]
        );

        TipoUsuario::firstOrCreate(
            ['nombre' => 'Lider prueba'],
            [
                'nombre_plural' => 'Lideres',
                'color' => '#a251bd',
                'icono' => 'ti ti-star',
                'imagen' => 'indicador_general.png',
                'id_rol_dependiente' => 3,
                'puntaje' => 4,
            ]
        );

        TipoUsuario::firstOrCreate(
            ['nombre' => 'Hermano menor prueba'],
            [
                'nombre_plural' => 'Hermano menor',
                'color' => '#dd4b39',
                'icono' => 'ti ti-mood-heart',
                'imagen' => 'indicador_general.png',
                'id_rol_dependiente' => 4,
                'puntaje' => 2,
                'habilitado_para_consolidacion' => true,
            ]
        );

        TipoUsuario::firstOrCreate(
            ['nombre' => 'Nuevo prueba'],
            [
                'nombre_plural' => 'Nuevos',
                'color' => '#00c0ef',
                'icono' => 'ti ti-mood-smile',
                'imagen' => 'indicador_general.png',
                'id_rol_dependiente' => 5,
                'default' => true,
                'puntaje' => 1,
                'habilitado_para_consolidacion' => true,
            ]
        );

        TipoUsuario::firstOrCreate(
            ['nombre' => 'Empleado prueba'],
            [
                'nombre_plural' => 'Empleados',
                'color' => '#055498',
                'icono' => 'ti ti-building-skyscraper',
                'imagen' => 'indicador_general.png',
                'id_rol_dependiente' => 6,
                'puntaje' => 0,
            ]
        );

        TipoUsuario::firstOrCreate(
            ['nombre' => 'Desarrollador prueba'],
            [
                'nombre_plural' => 'Desarrolladores',
                'color' => '#055498',
                'icono' => 'ti ti-building-skyscraper',
                'imagen' => 'indicador_general.png',
                'id_rol_dependiente' => 7,
                'visible' => 0,
                'puntaje' => 0,
            ]
        );

        TipoUsuario::firstOrCreate(
            ['nombre' => 'Hermano mayor prueba'],
            [
                'nombre_plural' => 'Hermano mayor',
                'color' => '#966201b6',
                'icono' => 'ti ti-mood-heart',
                'imagen' => 'indicador_general.png',
                'id_rol_dependiente' => 4,
                'puntaje' => 3,
                'habilitado_para_consolidacion' => false,
                'es_miembro_oficial' => true,
            ]
        );

        // 2. Cargar mapas de todos_tipo_usuarios.json para relacionar con la tabla roles
        $todosTipoUsuarios = $this->loadJson($this->todosTipoUsuariosPath);
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

        // 3. Cargar tipo_asistentes.json (o tipo_asistentes.json de fallback)
        $tiposAsistentes = $this->loadJson($this->tipoAsistentesPath);
        if (empty($tiposAsistentes)) {
            $tiposAsistentes = $this->loadJson('seeders/tipo_asistentes.json');
        }

        if (! empty($tiposAsistentes)) {
            $createdCount = 0;

            foreach ($tiposAsistentes as $item) {
                $nombre = trim($item['nombre'] ?? '');
                if (empty($nombre)) {
                    continue;
                }

                // Resolver el id_rol_dependiente legítimo de Spatie
                $idRolDependiente = null;
                $idTipoDepViejo = $item['id_tipo_usuario_dependiente'] ?? null;

                if ($idTipoDepViejo && isset($tipoUsuarioNombreMap[$idTipoDepViejo])) {
                    $nombreRolDep = $tipoUsuarioNombreMap[$idTipoDepViejo];
                    $roleModel = Role::where('name', $nombreRolDep)->first();
                    if ($roleModel) {
                        $idRolDependiente = $roleModel->id;
                    }
                }

                // Adaptación de icono FontAwesome (fa-) a Tabler Icons (ti ti-)
                $iconoRaw = $item['icono'] ?? '';
                $icono = ! empty($iconoRaw)
                    ? (str_starts_with($iconoRaw, 'ti ') ? $iconoRaw : str_replace(['fa-', 'fa '], 'ti ti-', $iconoRaw))
                    : 'ti ti-user';

                $tipoUsuario = TipoUsuario::updateOrCreate(
                    ['nombre' => $nombre],
                    [
                        'descripcion' => trim($item['descripcion'] ?? ''),
                        'color' => $item['color'] ?? '#39cccc',
                        'icono' => $icono,
                        'imagen' => 'indicador_general.png',
                        'nombre_plural' => trim($item['nombre_plural'] ?? ''),
                        'tipo_pastor' => (bool) ($item['tipo_pastor'] ?? false),
                        'tipo_pastor_principal' => (bool) ($item['tipo_pastor_principal'] ?? false),
                        'id_rol_dependiente' => $idRolDependiente,
                        'orden' => (int) ($item['orden'] ?? 0),
                        'seguimiento_actividad_grupo' => (bool) ($item['seguimiento_actividad_grupo'] ?? false),
                        'seguimiento_actividad_reunion' => (bool) ($item['seguimiento_actividad_reunion'] ?? false),
                        'puntaje' => (int) ($item['puntaje'] ?? 0),
                    ]
                );

                if ($tipoUsuario->wasRecentlyCreated) {
                    $createdCount++;
                }
            }

            if ($this->command) {
                $this->command->info("✔️ Tipos de usuario cargados desde JSON: {$createdCount} nuevos creados.");
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
