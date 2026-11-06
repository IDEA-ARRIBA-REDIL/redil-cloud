<?php

namespace App\Livewire\RolesPrivilegios;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class GestionarPermisos extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // Filtros de búsqueda
    public string $search = '';

    public string $bloqueSeleccionado = 'todos';

    public string $filtroCobertura = 'todos';

    public int $porPagina = 12;

    // Estado del Offcanvas de Ajuste Masivo
    public ?int $permisoSeleccionadoId = null;

    public string $permisoSeleccionadoNombre = '';

    public string $permisoSeleccionadoTitulo = '';

    public string $permisoSeleccionadoDescripcion = '';

    public string $searchRol = '';

    /**
     * @var array<int> IDs de los roles asignados al permiso abierto
     */
    public array $rolesAsignadosIds = [];

    /**
     * Reseteo de página al interactuar con los filtros
     */
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingBloqueSeleccionado(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroCobertura(): void
    {
        $this->resetPage();
    }

    public function updatingPorPagina(): void
    {
        $this->resetPage();
    }

    public function limpiarBusqueda(): void
    {
        $this->search = '';
        $this->resetPage();
    }

    /**
     * Catálogo maestro de bloques/módulos para agrupar permisos
     *
     * @return array<string, string>
     */
    public function bloquesDePermisos(): array
    {
        return [
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
            'Novedades' => 'novedades.',
            'Puntos de pago' => 'pdp.',
            'Peticiones' => 'peticiones.',
            'Padres' => 'padres.',
            'Escuelas' => 'escuelas.',
            'Familiar' => 'familiar.',
            'Tiempo con Dios' => 'tiempo_con_dios.',
            'Dashboard' => 'dashboard.',
            'Administración' => 'administracion.',
            'Configuración' => 'configuraciones.',
            'Consolidación' => 'consolidacion.',
            'Consejería' => 'consejeria.',
            'Cursos' => 'cursos.',
            'Versículos' => 'versiculos.',
            'Publicaciones' => 'posts.',
            'Iglesia Infantil' => 'iglesia_infantil.',
            'Rueda de la vida' => 'rueda_de_la_vida.',
            'Planes Lectores' => 'planes_lectores.',
            'Hitos' => 'hitos.',
        ];
    }

    /**
     * Abre el Offcanvas para ajustar un permiso específico
     */
    public function abrirAjustePermiso(int $permisoId): void
    {
        $permiso = Permission::with('roles:id')->findOrFail($permisoId);

        $this->permisoSeleccionadoId = $permiso->id;
        $this->permisoSeleccionadoNombre = $permiso->name;
        $this->permisoSeleccionadoTitulo = str_replace('_', ' ', $permiso->titulo ?: $permiso->name);
        $this->permisoSeleccionadoDescripcion = $permiso->descripcion ?? '';
        $this->rolesAsignadosIds = $permiso->roles->pluck('id')->toArray();
        $this->searchRol = '';

        $this->dispatch('abrir-offcanvas-permiso');
    }

    /**
     * Alterna la asignación de un rol individual al permiso seleccionado
     */
    public function toggleRolPermiso(int $rolId): void
    {
        if (! $this->permisoSeleccionadoId || ! $this->permisoSeleccionadoNombre) {
            return;
        }

        $role = Role::findOrFail($rolId);

        if (in_array($rolId, $this->rolesAsignadosIds)) {
            $role->revokePermissionTo($this->permisoSeleccionadoNombre);
            $this->rolesAsignadosIds = array_values(array_diff($this->rolesAsignadosIds, [$rolId]));
        } else {
            $role->givePermissionTo($this->permisoSeleccionadoNombre);
            $this->rolesAsignadosIds[] = $rolId;
        }

        // Limpiar caché de Spatie para reflejo inmediato en sesiones activas
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Asigna el permiso seleccionado a todos los roles del sistema
     */
    public function activarEnTodos(): void
    {
        if (! $this->permisoSeleccionadoId || ! $this->permisoSeleccionadoNombre) {
            return;
        }

        $permiso = Permission::findOrFail($this->permisoSeleccionadoId);
        $roles = Role::all();
        $permiso->syncRoles($roles);

        $this->rolesAsignadosIds = $roles->pluck('id')->toArray();
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->dispatch('msn', [
            'icono' => 'success',
            'titulo' => '¡Asignado a todos los roles!',
            'texto' => "El permiso '{$this->permisoSeleccionadoTitulo}' fue asignado exitosamente a los {$roles->count()} roles del sistema.",
        ]);
    }

    /**
     * Revoca el permiso seleccionado de todos los roles del sistema
     */
    public function desactivarDeTodos(): void
    {
        if (! $this->permisoSeleccionadoId || ! $this->permisoSeleccionadoNombre) {
            return;
        }

        $permiso = Permission::findOrFail($this->permisoSeleccionadoId);
        $permiso->syncRoles([]);

        $this->rolesAsignadosIds = [];
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->dispatch('msn', [
            'icono' => 'success',
            'titulo' => '¡Revocado de todos los roles!',
            'texto' => "El permiso '{$this->permisoSeleccionadoTitulo}' fue retirado de todos los roles.",
        ]);
    }

    /**
     * Cierra el Offcanvas y limpia el estado local
     */
    public function cerrarOffcanvas(): void
    {
        $this->permisoSeleccionadoId = null;
        $this->permisoSeleccionadoNombre = '';
        $this->permisoSeleccionadoTitulo = '';
        $this->permisoSeleccionadoDescripcion = '';
        $this->rolesAsignadosIds = [];
        $this->searchRol = '';

        $this->dispatch('cerrar-offcanvas-permiso');
    }

    public function render()
    {
        // 1. Cargar todos los roles para métricas y Offcanvas
        $todosLosRoles = Role::orderBy('name', 'asc')->get();
        $totalRolesCount = $todosLosRoles->count();

        // 2. Filtrar roles para el Offcanvas según término de búsqueda
        $rolesParaOffcanvas = $todosLosRoles;
        $terminoRol = trim($this->searchRol);
        if ($terminoRol !== '') {
            $rolesParaOffcanvas = $todosLosRoles->filter(function ($rol) use ($terminoRol) {
                return stripos($rol->name, $terminoRol) !== false;
            });
        }

        // 3. Consulta de permisos optimizada con relaciones
        $query = Permission::query()->with(['roles' => function ($q) {
            $q->select('roles.id', 'roles.name', 'roles.icono');
        }]);

        // Filtro de búsqueda insensible a acentos y mayúsculas
        $termino = trim($this->search);
        if ($termino !== '') {
            $query->where(function ($q) use ($termino) {
                $q->whereRaw(
                    "translate(name,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜ','aeiouAEIOUaeiouAEIOU') ILIKE translate(?,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜ','aeiouAEIOUaeiouAEIOU')",
                    ["%{$termino}%"]
                )->orWhereRaw(
                    "translate(COALESCE(titulo, ''),'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜ','aeiouAEIOUaeiouAEIOU') ILIKE translate(?,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜ','aeiouAEIOUaeiouAEIOU')",
                    ["%{$termino}%"]
                );
            });
        }

        // Filtro por bloque seleccionado
        $bloques = $this->bloquesDePermisos();
        if ($this->bloqueSeleccionado !== 'todos') {
            if ($this->bloqueSeleccionado === 'otros') {
                foreach ($bloques as $prefijo) {
                    $query->where('name', 'NOT ILIKE', '%'.$prefijo.'%');
                }
            } else {
                $query->where('name', 'ILIKE', '%'.$this->bloqueSeleccionado.'%');
            }
        }

        // Filtro por cobertura de asignación de roles
        if ($this->filtroCobertura === 'con_roles') {
            $query->has('roles');
        } elseif ($this->filtroCobertura === 'sin_roles') {
            $query->doesntHave('roles');
        } elseif ($this->filtroCobertura === 'todos_roles' && $totalRolesCount > 0) {
            $query->has('roles', '>=', $totalRolesCount);
        }

        $permisos = $query->orderBy('name', 'asc')->paginate($this->porPagina);

        return view('livewire.roles-privilegios.gestionar-permisos', [
            'permisos' => $permisos,
            'bloques' => $bloques,
            'todosLosRoles' => $todosLosRoles,
            'rolesParaOffcanvas' => $rolesParaOffcanvas,
            'totalRolesCount' => $totalRolesCount,
        ]);
    }
}
