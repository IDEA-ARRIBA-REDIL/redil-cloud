<?php

namespace App\Livewire\Cursos;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Curso;
use App\Models\CategoriaCurso;
use Illuminate\Support\Facades\Auth;

class CatalogoCursos extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    // Propiedades del componente
    public $search = '';
    public $categoriasSeleccionadas = [];
    public $orden = 'reciente'; // Opciones por defecto: reciente, antiguo, az

    protected $queryString = [
        'search' => ['except' => ''],
        'categoriasSeleccionadas' => ['except' => []],
        'orden' => ['except' => 'reciente'],
    ];

    public $categoriasList = [];

    public function mount()
    {
        // Cargar las categorías para los filtros tipo tab
        $this->categoriasList = CategoriaCurso::orderBy('nombre')->get();
    }

    public function updatingSearch()
    {
        $this->resetPage(); // Resetear a la página 1 cuando se busca
    }

    public function updatingCategoriasSeleccionadas()
    {
        $this->resetPage();
    }

    public function toggleCategoria($id)
    {
        if (in_array($id, $this->categoriasSeleccionadas)) {
            $this->categoriasSeleccionadas = array_diff($this->categoriasSeleccionadas, [$id]);
        } else {
            $this->categoriasSeleccionadas[] = $id;
        }
        $this->resetPage();
    }

    public function render()
    {
        // 1. Obtener "Mis cursos" solo si el usuario ha iniciado sesión
        $misCursos = collect();
        if (Auth::check()) {
            $misCursos = Curso::whereHas('usuarios', function ($q) {
                $q->where('users.id', Auth::id());
            })
                ->with(['usuarios' => function ($q) {
                    $q->where('users.id', Auth::id());
                }])
                ->get();
        }

        // 2. Obtener "Cursos Disponibles" usando los filtros
        $query = Curso::query()->where('estado', 'Publicado');

        // Filtro por búsqueda de nombre
        if ($this->search) {
            $query->whereRaw('LOWER(nombre) LIKE ?', ['%' . strtolower($this->search) . '%']);
        }

        // Filtro por categoría seleccionada
        if (!empty($this->categoriasSeleccionadas)) {
            $query->whereHas('categorias', function ($q) {
                $q->whereIn('categoria_curso_id', $this->categoriasSeleccionadas);
            });
        }

        // Filtro de ordenamiento
        if ($this->orden === 'reciente') {
            $query->orderBy('created_at', 'desc');
        } elseif ($this->orden === 'antiguo') {
            $query->orderBy('created_at', 'asc');
        } elseif ($this->orden === 'az') {
            $query->orderBy('nombre', 'asc');
        }

        // Paginación a 12 elementos por página
        $cursosDisponibles = $query->paginate(12);

        $configuracion = \App\Models\Configuracion::first();

        // Retornar la vista de Livewire
        return view('livewire.cursos.catalogo-cursos', [
            'misCursos' => $misCursos,
            'cursosDisponibles' => $cursosDisponibles,
            'configuracion' => $configuracion,
        ]);
    }
}
