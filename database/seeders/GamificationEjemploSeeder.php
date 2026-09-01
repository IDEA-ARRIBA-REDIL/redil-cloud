<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GamificationEjemploSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('📊 DATOS DE EJEMPLO PARA GAMIFICATION:');
        // 1. Crear o actualizar insignias en el catálogo
        $insignias = [
            [
                'nombre' => 'Perfil Completo',
                'descripcion' => 'Otorga puntos por completar toda la información del perfil de usuario.',
                'icono_clase' => 'ti-user-check',
                'icono_color' => '#4CAF50',
                'orden' => 1,
            ],
            [
                'nombre' => 'Devocional Fiel',
                'descripcion' => 'Registrar 30 tiempos con Dios.',
                'icono_clase' => 'ti-book',
                'icono_color' => '#2196F3',
                'orden' => 2,
            ],
            [
                'nombre' => 'Guerrero de Oración',
                'descripcion' => 'Registrar 20 peticiones o sesiones de oración.',
                'icono_clase' => 'ti-flame',
                'icono_color' => '#FF9800',
                'orden' => 3,
            ],
            [
                'nombre' => 'Asistente Constante',
                'descripcion' => 'Registrar 10 asistencias a grupo de conexión.',
                'icono_clase' => 'ti-users',
                'icono_color' => '#9C27B0',
                'orden' => 4,
            ],
        ];

        $insigniaIds = [];
        foreach ($insignias as $insignia) {
            DB::table('insignias')->updateOrInsert(
                ['nombre' => $insignia['nombre']],
                [
                    'descripcion' => $insignia['descripcion'],
                    'icono_clase' => $insignia['icono_clase'],
                    'icono_color' => $insignia['icono_color'],
                    'orden' => $insignia['orden'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            $insigniaIds[$insignia['nombre']] = DB::table('insignias')
                ->where('nombre', $insignia['nombre'])
                ->value('id');
        }

        // 2. Asignar insignias al usuario ID 1
        $userId = 1;

        if (DB::table('users')->where('id', $userId)->exists()) {
            // Estado 1: Completadas
            if (isset($insigniaIds['Perfil Completo'])) {
                DB::table('insignia_user')->updateOrInsert(
                    ['user_id' => $userId, 'insignia_id' => $insigniaIds['Perfil Completo']],
                    [
                        'progreso_actual' => 1,
                        'completada' => true,
                        'obtenida_el' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }

            if (isset($insigniaIds['Devocional Fiel'])) {
                DB::table('insignia_user')->updateOrInsert(
                    ['user_id' => $userId, 'insignia_id' => $insigniaIds['Devocional Fiel']],
                    [
                        'progreso_actual' => 30,
                        'completada' => true,
                        'obtenida_el' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }

            // Estado 2: En progreso (ej: 12 registradas)
            if (isset($insigniaIds['Guerrero de Oración'])) {
                DB::table('insignia_user')->updateOrInsert(
                    ['user_id' => $userId, 'insignia_id' => $insigniaIds['Guerrero de Oración']],
                    [
                        'progreso_actual' => 12,
                        'completada' => false,
                        'obtenida_el' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }

            // Estado 3: No iniciada -> 'Asistente Constante' NO se inserta en insignia_user
        }
    }
}
