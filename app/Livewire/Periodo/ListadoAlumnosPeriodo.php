<?php

namespace App\Livewire\Periodo;

use App\Models\Periodo;
use App\Models\User;
use App\Models\Configuracion;
use App\Models\Sede;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Rule; // Importar el atributo de validación
use App\Exports\AlumnosPeriodoExport; // <-- Importa la nueva clase
use Maatwebsite\Excel\Facades\Excel; // <-- Importa el Facade de Excel

class ListadoAlumnosPeriodo extends Component
{
    use WithPagination;

    public Periodo $periodo;
    public $sedes;
    public $materiasPeriodo;

    public $filtroSedeMatricula = [];
    public $filtroSedeAlumno = [];
    protected $paginationTheme = 'bootstrap';


    // La materia es obligatoria para la búsqueda
    #[Rule('required', message: 'Debe seleccionar una materia para buscar.')]
    public $filtroMateriaPeriodo = '';


    public function mount(Periodo $periodo)
    {
        $this->periodo = $periodo;
        $this->sedes = Sede::orderBy('nombre')->get();
        $this->materiasPeriodo = $this->periodo->materiasPeriodo()->with('materia')->get();
    }

    // MÉTODO NUEVO: Para eliminar un tag de filtro específico
    public function removeTag($field, $value)
    {
        // Si el campo es un array (selects múltiples)
        if (is_array($this->{$field})) {
            // Filtramos el array para quitar el valor que se quiere eliminar
            $this->{$field} = array_filter($this->{$field}, fn($item) => $item != $value);
        } else {
            // Si es un campo simple, simplemente lo reseteamos
            $this->{$field} = '';
        }
        $this->resetPage();
    }


    public function limpiarFiltros()
    {
        $this->reset(['filtroSedeMatricula', 'filtroSedeAlumno', 'filtroMateriaPeriodo']);
        $this->resetPage();

        $this->dispatch('reset-select2');
    }

    // MÉTODO NUEVO: Para manejar la descarga del archivo Excel
    public function exportarExcel()
    {
        // Preparamos un nombre de archivo dinámico
        $fileName = 'alumnos-' . \Str::slug($this->periodo->nombre) . '-' . now()->format('Y-m-d') . '.xlsx';

        // Retornamos la descarga, pasando los filtros actuales a la clase de exportación
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


    // El método que se ejecuta al presionar el botón "Buscar"
    public function buscarMatriculas()
    {
        // Primero, validamos que la materia haya sido seleccionada
        $this->validate();
        $this->resetPage(); // Reseteamos la paginación para la nueva búsqueda
    }


    public function render()
    {
        $configuracion = Configuracion::find(1);

        // 1. Iniciar con la consulta base: SIEMPRE trae a todos los alumnos matriculados en el periodo.
        $alumnosQuery = User::query()->whereHas('matriculas', fn($q) => $q->where('periodo_id', $this->periodo->id));


        $tagsBusqueda = []; // Array para guardar los tags

        // 2. Si se ha realizado una búsqueda (se ha presionado el botón y hay una materia seleccionada),
        //    ENTONCES aplicamos los filtros adicionales a la consulta base.
        if ($this->filtroMateriaPeriodo) {
            // Construir el Tag para la Materia
            $tag = new \stdClass();
            $tag->label = 'Materia: ' . $this->materiasPeriodo->find($this->filtroMateriaPeriodo)->materia->nombre;
            $tag->field = 'filtroMateriaPeriodo';
            $tag->value = $this->filtroMateriaPeriodo;
            $tagsBusqueda[] = $tag;

            // FILTRO OBLIGATORIO (AND): La matrícula debe ser en la materia seleccionada.
            $alumnosQuery->whereHas('matriculas', function ($matriculaQuery) {
                $matriculaQuery->where('periodo_id', $this->periodo->id)
                    ->whereHas('horarioMateriaPeriodo', function ($horarioQuery) {
                        $horarioQuery->where('materia_periodo_id', $this->filtroMateriaPeriodo);
                    });
            });

            // FILTROS OPCIONALES DE SEDE (OR):
            if (!empty($this->filtroSedeAlumno) || !empty($this->filtroSedeMatricula)) {
                $alumnosQuery->where(function ($query) {
                    if (!empty($this->filtroSedeAlumno)) {
                        $query->whereIn('sede_id', $this->filtroSedeAlumno);
                    }
                    if (!empty($this->filtroSedeMatricula)) {
                        $query->orWhereHas('matriculas', function ($matriculaQuery) {
                            $matriculaQuery->where('periodo_id', $this->periodo->id)
                                ->whereHas('horarioMateriaPeriodo.horarioBase.aula.sede', function ($sedeQuery) {
                                    $sedeQuery->whereIn('sedes.id', $this->filtroSedeMatricula);
                                });
                        });
                    }
                });
            }

            // Construir Tags para Sede de Alumno
            foreach ($this->filtroSedeAlumno as $sedeId) {
                $tag = new \stdClass();
                $tag->label = 'Sede Alumno: ' . $this->sedes->find($sedeId)->nombre;
                $tag->field = 'filtroSedeAlumno';
                $tag->value = $sedeId;
                $tagsBusqueda[] = $tag;
            }

            // Construir Tags para Sede de Matrícula
            foreach ($this->filtroSedeMatricula as $sedeId) {
                $tag = new \stdClass();
                $tag->label = 'Sede Matrícula: ' . $this->sedes->find($sedeId)->nombre;
                $tag->field = 'filtroSedeMatricula';
                $tag->value = $sedeId;
                $tagsBusqueda[] = $tag;
            }
        }
        // 3. Si no se ha realizado ninguna búsqueda, la consulta base no se modifica y traerá a TODOS los alumnos.
        //    Se ha eliminado la línea que forzaba un resultado vacío.

        $alumnos = $alumnosQuery->distinct()->paginate(15);

        return view('livewire.periodo.listado-alumnos-periodo', [
            'alumnos' => $alumnos,
            'configuracion' => $configuracion,
            'tagsBusqueda' => $tagsBusqueda, // Pasar los tags a la vista
        ]);
    }
}
