<?php

namespace App\Livewire\Periodo;

use App\Exports\AlumnosPeriodoExport;
use App\Models\Configuracion;
use App\Models\Periodo;
use App\Models\Sede;
use App\Models\User;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class ListadoAlumnosPeriodo extends Component
{
    use WithPagination;

    public Periodo $periodo;

    public $sedes;

    public $materiasPeriodo;

    public $filtroSedeMatricula = [];

    public $filtroSedeAlumno = [];

    public $search = '';

    protected $paginationTheme = 'bootstrap';

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    // La materia es obligatoria para la búsqueda avanzada desde el panel de filtros
    #[Rule('required', message: 'Debe seleccionar una materia para buscar.')]
    public $filtroMateriaPeriodo = '';

    public function mount(Periodo $periodo)
    {
        $this->periodo = $periodo;
        $this->sedes = Sede::orderBy('nombre')->get();
        $this->materiasPeriodo = $this->periodo->materiasPeriodo()->with('materia')->get();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    // MÉTODO: Para eliminar un tag de filtro específico
    public function removeTag($field, $value = null)
    {
        if ($field === 'search') {
            $this->search = '';
        } elseif (is_array($this->{$field})) {
            $this->{$field} = array_values(array_filter($this->{$field}, fn ($item) => $item != $value));
        } else {
            $this->{$field} = '';
        }
        $this->resetPage();
    }

    public function limpiarFiltros()
    {
        $this->reset(['filtroSedeMatricula', 'filtroSedeAlumno', 'filtroMateriaPeriodo', 'search']);
        $this->resetPage();

        $this->dispatch('reset-select2');
    }

    // MÉTODO: Para manejar la descarga del archivo Excel
    public function exportarExcel()
    {
        $fileName = 'alumnos-'.\Str::slug($this->periodo->nombre).'-'.now()->format('Y-m-d').'.xlsx';

        return Excel::download(
            new AlumnosPeriodoExport(
                $this->periodo,
                $this->filtroMateriaPeriodo,
                $this->filtroSedeAlumno,
                $this->filtroSedeMatricula
            ),
            $fileName
        );
    }

    // El método que se ejecuta al presionar el botón "Buscar Matrículas" en el offcanvas
    public function buscarMatriculas()
    {
        $this->validate();
        $this->resetPage();
    }

    public function render()
    {
        $configuracion = Configuracion::find(1);

        // 1. Iniciar con la consulta base: Alumnos con matrículas en este periodo y sus relaciones eager loaded
        $alumnosQuery = User::query()
            ->with([
                'tipoUsuario',
                'sede:id,nombre',
                'matriculas' => function ($q) {
                    $q->where('periodo_id', $this->periodo->id)
                        ->with([
                            'sede:id,nombre',
                            'horarioMateriaPeriodo.materiaPeriodo.materia:id,nombre',
                            'horarioMateriaPeriodo.horarioBase.aula.sede:id,nombre',
                        ]);

                    // Si hay un filtro específico de materia activo, cargamos preferentemente esa matrícula
                    if (! empty($this->filtroMateriaPeriodo)) {
                        $q->whereHas('horarioMateriaPeriodo', function ($hq) {
                            $hq->where('materia_periodo_id', $this->filtroMateriaPeriodo);
                        });
                    }
                },
            ])
            ->whereHas('matriculas', fn ($q) => $q->where('periodo_id', $this->periodo->id));

        $tagsBusqueda = [];

        // 2. Filtro de búsqueda rápida reactiva por texto (nombre completo, identificación, email)
        if (! empty(trim($this->search))) {
            $termino = '%'.strtolower(trim($this->search)).'%';

            $alumnosQuery->where(function ($q) use ($termino) {
                $q->whereRaw("LOWER(CONCAT_WS(' ', primer_nombre, segundo_nombre, primer_apellido, segundo_apellido)) LIKE ?", [$termino])
                    ->orWhereRaw('LOWER(identificacion) LIKE ?', [$termino])
                    ->orWhereRaw('LOWER(email) LIKE ?', [$termino]);
            });

            $tag = new \stdClass;
            $tag->label = 'Búsqueda: '.trim($this->search);
            $tag->field = 'search';
            $tag->value = trim($this->search);
            $tagsBusqueda[] = $tag;
        }

        // 3. Si se ha seleccionado una materia para filtrar, aplicamos los filtros avanzados
        if ($this->filtroMateriaPeriodo) {
            $materiaPeriodoSeleccionada = $this->materiasPeriodo->find($this->filtroMateriaPeriodo);
            if ($materiaPeriodoSeleccionada && $materiaPeriodoSeleccionada->materia) {
                $tag = new \stdClass;
                $tag->label = 'Materia: '.$materiaPeriodoSeleccionada->materia->nombre;
                $tag->field = 'filtroMateriaPeriodo';
                $tag->value = $this->filtroMateriaPeriodo;
                $tagsBusqueda[] = $tag;
            }

            // FILTRO OBLIGATORIO (AND): La matrícula debe ser en la materia seleccionada
            $alumnosQuery->whereHas('matriculas', function ($matriculaQuery) {
                $matriculaQuery->where('periodo_id', $this->periodo->id)
                    ->whereHas('horarioMateriaPeriodo', function ($horarioQuery) {
                        $horarioQuery->where('materia_periodo_id', $this->filtroMateriaPeriodo);
                    });
            });

            // FILTROS OPCIONALES DE SEDE (OR):
            if (! empty($this->filtroSedeAlumno) || ! empty($this->filtroSedeMatricula)) {
                $alumnosQuery->where(function ($query) {
                    if (! empty($this->filtroSedeAlumno)) {
                        $query->whereIn('sede_id', $this->filtroSedeAlumno);
                    }
                    if (! empty($this->filtroSedeMatricula)) {
                        $query->orWhereHas('matriculas', function ($matriculaQuery) {
                            $matriculaQuery->where('periodo_id', $this->periodo->id)
                                ->whereHas('horarioMateriaPeriodo.horarioBase.aula.sede', function ($sedeQuery) {
                                    $sedeQuery->whereIn('sedes.id', $this->filtroSedeMatricula);
                                });
                        });
                    }
                });
            }

            // Tags para Sede de Alumno
            foreach ($this->filtroSedeAlumno as $sedeId) {
                $sede = $this->sedes->find($sedeId);
                if ($sede) {
                    $tag = new \stdClass;
                    $tag->label = 'Sede Alumno: '.$sede->nombre;
                    $tag->field = 'filtroSedeAlumno';
                    $tag->value = $sedeId;
                    $tagsBusqueda[] = $tag;
                }
            }

            // Tags para Sede de Matrícula
            foreach ($this->filtroSedeMatricula as $sedeId) {
                $sede = $this->sedes->find($sedeId);
                if ($sede) {
                    $tag = new \stdClass;
                    $tag->label = 'Sede Matrícula: '.$sede->nombre;
                    $tag->field = 'filtroSedeMatricula';
                    $tag->value = $sedeId;
                    $tagsBusqueda[] = $tag;
                }
            }
        }

        $alumnos = $alumnosQuery->distinct()->paginate(15);

        return view('livewire.periodo.listado-alumnos-periodo', [
            'alumnos' => $alumnos,
            'configuracion' => $configuracion,
            'tagsBusqueda' => $tagsBusqueda,
            'materiasPeriodo' => $this->materiasPeriodo,
            'sedes' => $this->sedes,
        ]);
    }
}
