<?php

namespace App\Livewire\Cursos;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Curso;

class GestionarCursos extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $filtroEstado = '';
    public $filtroDificultad = '';
    public $filtroCarrera = '';
    public $filtroCategoria = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'filtroEstado' => ['except' => ''],
        'filtroDificultad' => ['except' => ''],
        'filtroCarrera' => ['except' => ''],
        'filtroCategoria' => ['except' => ''],
    ];
    public $configuracion;

    public $carrerasList = [];
    public $categoriasList = [];

    public function mount()
    {
        $this->configuracion = \App\Models\Configuracion::find(1);
        $this->carrerasList = \App\Models\Carrera::where('estado', 'Activo')->get();
        $this->categoriasList = \App\Models\CategoriaCurso::all();
    }



    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function aplicarFiltros()
    {
        $this->resetPage();
    }

    public function limpiarFiltros()
    {
        $this->reset(['search', 'filtroEstado', 'filtroDificultad', 'filtroCarrera', 'filtroCategoria']);
        $this->resetPage();
    }

    public function removeTag($field)
    {
        if (in_array($field, ['search', 'filtroEstado', 'filtroDificultad', 'filtroCarrera', 'filtroCategoria'])) {
            $this->$field = '';
            $this->resetPage();
        }
    }

    public function render()
    {
        $query = Curso::query();

        // 1. Jerarquía Superior: Permisos Globales (Spatie)
        $user = auth()->user();
        
        if ($user->can('cursos.listar_todos_cursos')) {
            // Acceso total por rol de sistema -> No aplicamos ningún filtro adicional
        } elseif ($user->can('cursos.listar_solo_cursos_asignados')) {
            // Acceso restringido -> Aplicamos el filtro de Cargos de Curso granular
            $usuarioId = $user->id;
            $cargosUsuario = \App\Models\CursoUsuarioCargo::with('tipoCargo')
                ->where('usuario_id', $usuarioId)
                ->where('activo', true)
                ->get();

            if ($cargosUsuario->isEmpty()) {
                // Si tiene el permiso de Spatie pero no tiene ningún cargo asignado, no debería ver nada
                $query->whereRaw('1 = 0');
            } else {
                // Verificar si algún cargo le da acceso total dentro del módulo
                $tieneAccesoTotal = $cargosUsuario->contains(function ($cargo) {
                    return $cargo->tipoCargo && $cargo->tipoCargo->puede_ver_todos_los_cursos;
                });

                if (!$tieneAccesoTotal) {
                    $carrerasPermitidasIds = [];
                    $limitaAlgunaCarrera = false;
                    
                    foreach ($cargosUsuario as $cargo) {
                        if ($cargo->tipoCargo && $cargo->tipoCargo->limita_carreras) {
                            $limitaAlgunaCarrera = true;
                            $permitidas = $cargo->tipoCargo->carreras_permitidas ?? [];
                            if (is_array($permitidas)) {
                                $carrerasPermitidasIds = array_merge($carrerasPermitidasIds, $permitidas);
                            }
                        }
                    }
                    
                    $carrerasPermitidasIds = array_unique($carrerasPermitidasIds);

                    if ($limitaAlgunaCarrera) {
                        if (empty($carrerasPermitidasIds)) {
                            $query->whereRaw('1 = 0');
                        } else {
                            $query->whereIn('carrera_id', $carrerasPermitidasIds);
                        }
                    } else {
                        // Si no tiene "ver todos" Y ninguno de sus cargos limita carreras explícitamente, 
                        // mantenemos el comportamiento por defecto de ver solo los cursos donde está asignado.
                        $query->whereHas('equipo', function ($q) use ($usuarioId) {
                            $q->where('usuario_id', $usuarioId)
                              ->where('activo', true);
                        });
                    }
                }
            }
        } else {
            // No tiene ninguno de los dos permisos de listado de Spatie
            $query->whereRaw('1 = 0');
        }

        if ($this->search) {
            $query->whereRaw('LOWER(nombre) LIKE ?', ['%' . strtolower($this->search) . '%']);
        }

        if ($this->filtroEstado) {
            $query->where('estado', $this->filtroEstado);
        }

        if ($this->filtroDificultad) {
            $query->where('nivel_dificultad', $this->filtroDificultad);
        }

        if ($this->filtroCarrera) {
            $query->where('carrera_id', $this->filtroCarrera);
        }

        if ($this->filtroCategoria) {
            $query->whereHas('categorias', function ($q) {
                $q->where('categoria_curso_id', $this->filtroCategoria);
            });
        }

        $cursos = $query->orderBy('id', 'desc')->paginate(10);

        return view('livewire.cursos.gestionar-cursos', [
            'cursos' => $cursos
        ]);
    }
}
