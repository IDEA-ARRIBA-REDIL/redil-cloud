<?php

namespace App\Livewire\RolesPrivilegios;

use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class EditarPermisos extends Component
{
    public $role;

    public $rolActivo;

    public $search = '';

    public function mount(Role $role)
    {
        $this->role = $role;
        $this->rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    }

    public function togglePermiso($permisoName, $estado)
    {
        if ($estado) {
            $this->role->givePermissionTo($permisoName);
        } else {
            $this->role->revokePermissionTo($permisoName);
        }
        // Refrescamos los permisos en memoria del objeto role para que la vista refleje el cambio
        $this->role->unsetRelation('permissions');
        // Limpiamos el caché de permisos de Spatie para que los cambios se apliquen de inmediato
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function bloquesDePermisos()
    {
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
            'Cursos' => 'cursos.',
            'Versiculos' => 'versiculos.',
            'Publicaciones' => 'posts.',
            'Iglesia Infantil' => 'iglesia_infantil.',
            'Rueda de la vida' => 'rueda_de_la_vida.',
            'Planes Lectores' => 'planes_lectores.',
            'Hitos' => 'hitos.',
        ];
        foreach ($items as $nombre => $etiqueta) {
            $item = new \stdClass;
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
            $permisos = Permission::where('guard_name', $this->role->guard_name)
                ->whereRaw(
                    "translate(name,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜ','aeiouAEIOUaeiouAEIOU') ILIKE '%".$bloque->etiqueta."%'"
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
                if (stripos($bloque->nombre, $search) !== false) {
                    $mostrarBloque = true;
                } else {
                    $permisosFiltrados = $permisos->filter(function ($permiso) use ($search) {
                        return stripos(str_replace('_', ' ', $permiso->titulo), $search) !== false;
                    });
                    if ($permisosFiltrados->isNotEmpty()) {
                        $mostrarBloque = true;
                    }
                }
            }
            if ($mostrarBloque) {
                $item = new \stdClass;
                $item->bloque = $bloque;
                $item->permisos = $permisosFiltrados;
                $checkboxes[] = $item;
            }
        }
        // Permisos huérfanos que no encajan en ningún bloque
        $permisosHuerfanos = Permission::where('guard_name', $this->role->guard_name);
        foreach ($bloques as $b) {
            $permisosHuerfanos->where('name', 'NOT ILIKE', '%'.$b->etiqueta.'%');
        }
        $huerfanos = $permisosHuerfanos->get();
        if ($huerfanos->isNotEmpty()) {
            $item = new \stdClass;
            $item->bloque = (object) ['nombre' => 'Otros Permisos'];
            $item->permisos = $huerfanos;
            if (! empty($search)) {
                $item->permisos = $huerfanos->filter(function ($p) use ($search) {
                    return stripos(str_replace('_', ' ', $p->titulo), $search) !== false;
                });
            }
            if ($item->permisos->isNotEmpty()) {
                $checkboxes[] = $item;
            }
        }

        return view('livewire.roles-privilegios.editar-permisos', [
            'bloquesPermisos' => $checkboxes,
        ]);
    }
}
