<?php

namespace App\Livewire\RolesPrivilegios;

use Livewire\Component;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class EditarPermisos extends Component
{
    public $role;
    public $search = '';

    public function mount(Role $role)
    {
        $this->role = $role;
    }

    public function togglePermiso($permisoName, $estado)
    {
        if ($estado) {
            $this->role->givePermissionTo($permisoName);
        } else {
            $this->role->revokePermissionTo($permisoName);
        }

        // Opcional: Notificar éxito
        // $this->dispatch('msn', ...);
    }

    public function bloquesDePermisos()
    {
        $bloques = [];

        $items = [
            'Personas' => 'personas.',
            'Grupos' => 'grupos.',
            'Reportes grupos' => 'reportes_grupos.',
            'Reuniones' => 'reuniones.',
            'Reporte reuniones' => 'reporte_reuniones.',
            'Sedes' => 'sedes.',
            'Ingresos' => 'ingresos.',
            'Informes' => 'informes.',
            'Temas' => 'temas.',
            'Iglesia' => 'iglesia.',
            'Actividades' => 'actividades.',
            'Puntos de pago' => 'pdp.',
            'Peticiones' => 'peticiones.',
            'Padres' => 'padres.',
            'Escuelas' => 'escuelas.',
            'Familiar' => 'familiar.',
            'Tiempo con Dios' => 'tiempo_con_dios.',
            'Dashboard' => 'dashboard.',
            'Administracion' => 'administracion.',
            'Consolidación' => 'consolidacion.',
            'Consejería' => 'consejeria.',
            'Cursos'=>'cursos.',
            'Versiculos'=>'versiculos.',
            'Publicaciones'=>'publicaciones.'
        ];

        foreach ($items as $nombre => $etiqueta) {
            $item = new \stdClass();
            $item->nombre = $nombre;
            $item->etiqueta = $etiqueta;
            $bloques[] = $item;
        }

        return $bloques;
    }

    public function render()
    {
        $checkboxes = [];
        $bloques = $this->bloquesDePermisos();

        // Limpiamos y preparamos el término de búsqueda
        $search = trim($this->search);

        foreach ($bloques as $bloque) {
            $permisos = Permission::whereRaw(
                "translate(name,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜ','aeiouAEIOUaeiouAEIOU') ILIKE '%" . $bloque->etiqueta . "%'"
            )->get();

            if ($permisos->isEmpty()) {
                continue;
            }

            // Lógica de filtrado
            $mostrarBloque = false;
            $permisosFiltrados = $permisos;

            if (empty($search)) {
                $mostrarBloque = true;
            } else {
                // 1. Buscar por nombre del bloque
                if (stripos($bloque->nombre, $search) !== false) {
                    $mostrarBloque = true;
                    // Si coincide el bloque, mostramos todos los permisos (sin filtrar)
                }
                // 2. Si no coincide el bloque, filtramos los permisos
                else {
                    $permisosFiltrados = $permisos->filter(function ($permiso) use ($search) {
                        return stripos(str_replace('_', ' ', $permiso->titulo), $search) !== false;
                    });

                    if ($permisosFiltrados->isNotEmpty()) {
                        $mostrarBloque = true;
                    }
                }
            }

            if ($mostrarBloque) {
                $item = new \stdClass();
                $item->bloque = $bloque;
                $item->permisos = $permisosFiltrados;
                $checkboxes[] = $item;
            }
        }

        return view('livewire.roles-privilegios.editar-permisos', [
            'bloquesPermisos' => $checkboxes
        ]);
    }
}
