<?php

namespace Database\Seeders;

use App\Models\AccionDashboard;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AccionDashboardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Registrar permisos de Cumpleaños y de Configuración de Acciones del Dashboard
        $permisoCumpleanos = Permission::firstOrCreate(
            ['name' => 'dashboard.ver_cumpleanos', 'guard_name' => 'web'],
            [
                'titulo' => 'ver_cumpleanos',
                'descripcion' => 'Permite visualizar la opción automática de próximos cumpleaños en el dashboard.',
            ]
        );

        $permisoConfigAcciones = Permission::firstOrCreate(
            ['name' => 'configuraciones.subitem_acciones_dashboard', 'guard_name' => 'web'],
            [
                'titulo' => 'subitem_acciones_dashboard',
                'descripcion' => 'Permite configurar los enlaces rápidos de la sección ¿Qué deseas hacer hoy? en el dashboard.',
            ]
        );

        // 2. Asignar permisos a los roles administrativos y de liderazgo
        $rolesAdmin = Role::whereIn('name', [
            'Super Administrador Prueba',
            'Super Administrador',
            'Super Administrador REDIL',
            'Administrador iglesia',
        ])->get();

        foreach ($rolesAdmin as $rolAdmin) {
            $rolAdmin->givePermissionTo([$permisoCumpleanos, $permisoConfigAcciones]);
        }

        $rolesLideres = Role::whereIn('name', [
            'Pastor Prueba',
            'Lider Prueba',
            'Pastor',
            'Líder',
            'Lider',
        ])->orWhereHas('permissions', function ($query) {
            $query->whereIn('name', [
                'personas.lista_asistentes_solo_ministerio',
                'personas.lista_asistentes_todos',
            ]);
        })->get();

        foreach ($rolesLideres as $rolLider) {
            $rolLider->givePermissionTo($permisoCumpleanos);
        }

        // 3. Crear las configuraciones iniciales (Academia y Grupos) si no existen
        AccionDashboard::firstOrCreate(
            ['nombre' => 'Academia'],
            [
                'url' => '/escuelas/dashboard',
                'abrir_nueva_pestana' => false,
                'tipo_visual' => 'imagen',
                'icono_clase' => 'ti ti-school',
                'icono_color' => '#1d4ed8',
                'imagen' => 'Funcionalidad-escuelas.png',
                'visible_todos' => true,
                'orden' => 1,
                'activo' => true,
            ]
        );

        AccionDashboard::firstOrCreate(
            ['nombre' => 'Grupos'],
            [
                'url' => '/grupos',
                'abrir_nueva_pestana' => false,
                'tipo_visual' => 'imagen',
                'icono_clase' => 'ti ti-users-group',
                'icono_color' => '#4d9a1d',
                'imagen' => 'Funcionalidad-grupos.png',
                'visible_todos' => true,
                'orden' => 2,
                'activo' => true,
            ]
        );
    }
}
