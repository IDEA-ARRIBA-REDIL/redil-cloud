<?php

namespace Database\Seeders;

use App\Models\TipoUsuario;
use Illuminate\Database\Seeder;

class TipoUsuarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TipoUsuario::firstOrCreate(
            ['nombre' => 'Pastor'],
            [
                'nombre_plural' => 'Pastores',
                'color' => '#6b2682',
                'icono' => 'ti ti-book',
                'imagen' => 'indicador_general.png',
                'id_rol_dependiente' => 2,
                'puntaje' => 5,
            ]);

        TipoUsuario::firstOrCreate(
            ['nombre' => 'Lider'],
            [
                'nombre_plural' => 'Lideres',
                'color' => '#a251bd',
                'icono' => 'ti ti-star',
                'imagen' => 'indicador_general.png',
                'id_rol_dependiente' => 3,
                'puntaje' => 4,
            ]);

        TipoUsuario::firstOrCreate(
            ['nombre' => 'Hermano menor'],
            [
                'nombre_plural' => 'Hermano menor',
                'color' => '#dd4b39',
                'icono' => 'ti ti-mood-heart',
                'imagen' => 'indicador_general.png',
                'id_rol_dependiente' => 4,
                'puntaje' => 2,
                'habilitado_para_consolidacion' => true,
            ]);

        TipoUsuario::firstOrCreate(
            ['nombre' => 'Nuevo'],
            [
                'nombre_plural' => 'Nuevos',
                'color' => '#00c0ef',
                'icono' => 'ti ti-mood-smile',
                'imagen' => 'indicador_general.png',
                'id_rol_dependiente' => 5,
                'default' => true,
                'puntaje' => 1,
                'habilitado_para_consolidacion' => true,
            ]);

        TipoUsuario::firstOrCreate(
            ['nombre' => 'Empleado'],
            [
                'nombre_plural' => 'Empleados',
                'color' => '#055498',
                'icono' => 'ti ti-building-skyscraper',
                'imagen' => 'indicador_general.png',
                'id_rol_dependiente' => 6,
                'puntaje' => 0,
            ]);

        TipoUsuario::firstOrCreate(
            ['nombre' => 'Desarrollador'],
            [
                'nombre_plural' => 'Desarrolladores',
                'color' => '#055498',
                'icono' => 'ti ti-building-skyscraper',
                'imagen' => 'indicador_general.png',
                'id_rol_dependiente' => 7,
                'visible' => 0,
                'puntaje' => 0,
            ]);

        TipoUsuario::firstOrCreate(
            ['nombre' => 'Hermano mayor'],
            [
                'nombre_plural' => 'Hermano mayor',
                'color' => '#966201b6',
                'icono' => 'ti ti-mood-heart',
                'imagen' => 'indicador_general.png',
                'id_rol_dependiente' => 4,
                'puntaje' => 3,
                'habilitado_para_consolidacion' => false,
                'es_miembro_oficial' => true,
            ]);

        $jsonPath = base_path('tipos_usuario.json');
        if (file_exists($jsonPath)) {
            $json = file_get_contents($jsonPath);
            $data = json_decode($json, true);
            if (isset($data['tipo_usuarios'])) {
                TipoUsuario::unguard();
                foreach ($data['tipo_usuarios'] as $tipo) {
                    $icono = isset($tipo['icono']) ? str_replace('fa fa-', 'ti ti-', $tipo['icono']) : 'ti ti-user';
                    $color = isset($tipo['color']) ? $tipo['color'] : '#cccccc';

                    TipoUsuario::firstOrCreate(
                        ['id' => $tipo['id']],
                        [
                            'nombre' => $tipo['nombre'],
                            'icono' => $icono,
                            'color' => $color,
                            'imagen' => 'indicador_general.png',
                        ]
                    );
                }
                TipoUsuario::reguard();
            }
        }
    }
}
