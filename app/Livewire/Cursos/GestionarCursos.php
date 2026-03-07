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

        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();

        $verTodos = $rolActivo && $rolActivo->hasPermissionTo('cursos.listar_todos_cursos');
        $verSoloAsignados = $rolActivo && $rolActivo->hasPermissionTo('cursos.listar_solo_cursos_asignados');

        if ($verTodos) {
            // Ver todos -> no aplica restricción
        } elseif ($verSoloAsignados) {
            $query->whereHas('equipo', function ($q) {
                // Filtramos por su ID de usuario en la tabla de asignaciones (curso_usuario_cargo)
                $q->where('usuario_id', auth()->id())
                  ->where('activo', true);
            });
        } else {
            // Si el usuario llega aquí pero no tiene explícitamente parametrizado su permiso de listado
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
