<?php

namespace App\Livewire\Cursos;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Curso;
use App\Models\CategoriaCurso;
use App\Models\CursoUser;
use App\Models\CursoItemUser;
use App\Models\CursoEvaluacionResultado;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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

    /**
     * Reinicia el progreso de un curso para el usuario autenticado.
     */
    public function reiniciarCurso($cursoId)
    {
        $usuario = Auth::user();
        $curso = Curso::findOrFail($cursoId);

        // 1. Obtener la inscripción
        $inscripcion = CursoUser::where('curso_id', $cursoId)
            ->where('user_id', $usuario->id)
            ->firstOrFail();

        // 2. Validar límites de reinicio
        if ($curso->limite_reintentos > 0 && $inscripcion->numero_reintentos >= $curso->limite_reintentos) {
            $msn = "Has alcanzado el límite máximo de " . $curso->limite_reintentos . " reinicios para este curso.";
            if ($curso->dias_castigo > 0) {
                $msn .= " Debes esperar " . $curso->dias_castigo . " días para volver a intentarlo o realizar un nuevo pago según las condiciones.";
            }

            $this->dispatch('msn', [
                'msn' => $msn,
                'icon' => 'error'
            ]);
            return;
        }

        // 3. Ejecutar reinicio dentro de una transacción
        try {
            DB::transaction(function () use ($curso, $usuario, $inscripcion) {
                // A. Obtener IDs de los items del curso para limpiar progreso
                $itemIds = DB::table('curso_items')
                    ->join('curso_modulos', 'curso_items.curso_modulo_id', '=', 'curso_modulos.id')
                    ->where('curso_modulos.curso_id', $curso->id)
                    ->pluck('curso_items.id');

                // B. Borrar progreso de lecciones e ítems
                CursoItemUser::where('user_id', $usuario->id)
                    ->whereIn('curso_item_id', $itemIds)
                    ->delete();

                // C. Borrar resultados de evaluaciones
                CursoEvaluacionResultado::where('user_id', $usuario->id)
                    ->where('curso_id', $curso->id)
                    ->delete();

                // D. Actualizar inscripción
                $inscripcion->update([
                    'numero_reintentos' => $inscripcion->numero_reintentos + 1,
                    'ultimo_reintento_at' => now(),
                    'porcentaje_progreso' => 0,
                    'estado' => 'activo' // Asegurar que esté activo si estaba finalizado o suspendido
                ]);
            });

            $this->dispatch('msn', [
                'msn' => 'El curso ha sido reiniciado correctamente. Tu progreso ha vuelto a cero.',
                'icon' => 'success'
            ]);

        } catch (\Exception $e) {
            $this->dispatch('msn', [
                'msn' => 'Ocurrió un error al intentar reiniciar el curso: ' . $e->getMessage(),
                'icon' => 'error'
            ]);
        }
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
