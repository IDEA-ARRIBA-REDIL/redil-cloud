<?php

namespace App\Livewire\Cursos;

use App\Models\Curso;
use App\Models\CursoUser;
use Livewire\Component;
use Livewire\WithPagination;

class ListadoEstudiantesCurso extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public Curso $curso;

    /*
     * OPTIMIZACIÓN (Fix #4):
     * Configuracion::first() se movíscal de render() a mount().
     * render() corre en cada interacción de Livewire (búsqueda, paginación, filtros).
     * La configuración no cambia entre requests, así que bastaba cargarla una sola vez
     * al montar el componente y guardarla como propiedad pública persistida por Livewire.
     */
    public $configuracion = null;

    // Filtros
    public $search = '';

    public $filtroEstado = '';

    public $filtroAno = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'filtroEstado' => ['except' => ''],
        'filtroAno' => ['except' => ''],
    ];

    public function mount(Curso $curso)
    {
        $this->curso = $curso;

        // Cargamos la configuración una sola vez al montar el componente.
        // Así evitamos que render() haga esta query en cada interacción del usuario.
        $this->configuracion = \App\Models\Configuracion::first();
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
        $this->reset(['search', 'filtroEstado', 'filtroAno']);
        $this->resetPage();
    }

    // Remover un filtro específico desde los badges
    public function removeTag($field)
    {
        if (in_array($field, ['search', 'filtroEstado', 'filtroAno'])) {
            $this->$field = '';
            $this->resetPage();
        }
    }

    public function render()
    {
        // La configuración ya está disponible en $this->configuracion (cargada en mount)
        $query = CursoUser::with('user')
            ->where('curso_id', $this->curso->id);

        if (! empty($this->search)) {
            $searchTerm = '%'.strtolower($this->search).'%';

            $query->whereHas('user', function ($q) use ($searchTerm) {
                $q->whereRaw("LOWER(CONCAT_WS(' ', primer_nombre, segundo_nombre, primer_apellido, segundo_apellido)) LIKE ?", [$searchTerm])
                    ->orWhereRaw('LOWER(identificacion) LIKE ?', [$searchTerm])
                    ->orWhereRaw('LOWER(email) LIKE ?', [$searchTerm]);
            });
        }

        if (! empty($this->filtroEstado)) {
            $query->where('estado', $this->filtroEstado);
        }

        if (! empty($this->filtroAno)) {
            $query->whereYear('created_at', $this->filtroAno);
        }

        $estudiantes = $query->orderByDesc('created_at')->paginate(12);

        // Años disponibles para el filtro
        $anosDisponibles = CursoUser::where('curso_id', $this->curso->id)
            ->selectRaw('EXTRACT(YEAR FROM created_at) as ano')
            ->distinct()
            ->orderByDesc('ano')
            ->pluck('ano');

        return view('livewire.cursos.listado-estudiantes-curso', [
            'estudiantes' => $estudiantes,
            'configuracion' => $this->configuracion,
            'anosDisponibles' => $anosDisponibles,
        ]);
    }
}
