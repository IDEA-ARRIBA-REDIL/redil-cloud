<?php

namespace App\Livewire\Materias;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Materia;
use App\Models\Aula;
use App\Models\PasoCrecimiento;
use App\Models\Sede;
use App\Models\TipoAula;
use App\Models\HorarioBase;
use App\Exports\HorariosMateriaExport; // Importa la clase de exportación
use Maatwebsite\Excel\Facades\Excel; // Importa el Facade de Excel
use Illuminate\Support\Str; // Para generar el nombre del archivo
use \stdClass;

class HorariosMateria extends Component
{
    use WithPagination;

    public Materia $materia;
    public $aulas = [];       // Aulas filtradas según la sede seleccionada
    public $sedes;       // Lista completa de sedes
    public $sede_id;     // Sede seleccionada (nueva propiedad)
    public $sedeAula;
    public $horarioEditando = null;
    public $tiposAula;
    public $shouldCloseOffcanvas = false;
    public $pasosCrecimiento = [];

    public $tipoAulaFiltro = null;
    public $sedeFiltro = null;




    // Campos del formulario
    public $aula_id;
    public $dia;
    public $hora_inicio;
    public $hora_fin;
    public $variable;
    public $cupos_iniciales;
    public $cupos_limite;


    protected $listeners = [
        'resetearFormulario' => 'resetearFormulario',
        'resetearFormularioConDelay' => 'resetearConDelay',
        'filtrosActualizados' => '$refresh'
    ];


    protected $rules = [
        'sedeAula' => 'required', // Para validar la sede seleccionada
        'aula_id' => 'required',
        'dia' => 'required|integer|between:0,6',
        'hora_inicio' => 'required',
        'hora_fin' => 'required'
    ];

    public function mount(Materia $materia)
    {
        $this->pasosCrecimiento = PasoCrecimiento::orderBy('id', 'asc')->get();
        $contador_ids = 1;



        $this->materia = $materia;
        // Cargar todas las sedes disponibles
        $this->sedes = Sede::all();
        // Inicialmente, no se ha seleccionado sede; por ello, el select de aulas estará vacío
        $this->aulas;

        $this->tiposAula = TipoAula::all();

        $this->sedeAula;
        $this->variable = "inicio";
    }

    public function getHayFiltrosProperty()
    {
        return $this->sedeFiltro || $this->tipoAulaFiltro;
    }

    // Método para aplicar filtros
    public function aplicarFiltros()
    {
        $this->dispatch('cerrarOffcanvas', nombreModal: 'addEventSidebarFiltros');
        $this->resetPage(); // Reinicia la paginación

    }

    // Método para resetear filtros
    public function resetFiltros()
    {
        $this->reset(['tipoAulaFiltro', 'sedeFiltro']);
        $this->dispatch('cerrarOffcanvas', nombreModal: 'addEventSidebarFiltros');
        $this->dispatch('resetearFiltrosOffcanvas'); // Nuevo evento para JavaScript
        $this->resetPage();
    }


    public function buscarAulas($sedeId)
    {
        // Cuando se selecciona una sede, se cargan solo las aulas de esa sede
        $this->sedeAula = $sedeId; // Asegúrate de tener esta propiedad definida
        $this->aulas = Aula::where('sede_id', $sedeId)->orderBy('nombre')->get();
        $this->aula_id = null; // Resetear el aula seleccionada

        // Emitir evento para JavaScript si es necesario
        //  $this->dispatch('aulasActualizadas');
    }

    public function exportarExcel()
    {
        // Genera un nombre de archivo descriptivo
        $fileName = 'horarios-' . Str::slug($this->materia->nombre) . '.xlsx';

        // Usa el Facade de Excel para descargar, pasándole tu clase de exportación y la materia actual
        return Excel::download(new HorariosMateriaExport($this->materia), $fileName);
    }



    public function guardarHorario()
    {

        // Validar los datos del formulario
        $this->validate();

        // Creación de nuevo horario
        $horario = new HorarioBase();
        $horario->materia_id = $this->materia->id;
        $horario->aula_id = $this->aula_id;
        $horario->dia = $this->dia;
        $horario->capacidad = $this->cupos_iniciales;
        $horario->capacidad_limite = $this->cupos_limite;
        $horario->hora_inicio = $this->hora_inicio;
        $horario->hora_fin = $this->hora_fin;
        $horario->activo = true; // Valor por defecto
        $horario->save();

        $message = 'Horario creado correctamente';


        $this->dispatch('cerrarOffcanvas', nombreModal: 'offcanvasRight');
    }

    public function actualizarHorario()
    {
        $this->validate();

        $horario = $this->horarioEditando;
        $horario->aula_id = $this->aula_id;
        $horario->dia = $this->dia;
        $horario->hora_inicio = $this->hora_inicio;
        $horario->hora_fin = $this->hora_fin;
        $horario->capacidad = $this->cupos_iniciales;
        $horario->capacidad_limite = $this->cupos_limite;
        $horario->save();

        $this->dispatch('cerrarOffcanvas', nombreModal: 'offcanvasEditar');
        $this->dispatch('notificacion', tipo: 'success', mensaje: 'Horario actualizado correctamente');
        $this->resetearFormulario();
        $this->horarioEditando = null;
    }

    // Modifica el método abrirFormularioEditar
    public function abrirFormularioEditar(HorarioBase $horario)
    {
        $this->horarioEditando = $horario;
        $this->sedeAula = $horario->aula->sede->id;
        $this->buscarAulas($this->sedeAula);
        $this->aula_id = $horario->aula_id;
        $this->dia = $horario->dia;
        $this->hora_inicio = $horario->hora_inicio;
        $this->hora_fin = $horario->hora_fin;

        $this->dispatch('abrirOffcanvas', nombreModal: 'offcanvasEditar');
    }

    public function abrirFormularioNuevo()
    {

        $this->dispatch('abrirOffcanvas', nombreModal: 'offcanvasRight');
    }


    public function eliminarHorario(HorarioBase $horario)
    {
        $horario->delete();
        $this->dispatch('notificacion', tipo: 'success', mensaje: 'Horario eliminado correctamente');
    }

    private function resetearFormulario()
    {
        $this->reset(['sede_id', 'aula_id', 'dia', 'hora_inicio', 'hora_fin']);
        $this->resetErrorBag();
    }

    public function render()
    {

        $query = HorarioBase::where('materia_id', $this->materia->id)
            ->with(['aula.tipo', 'aula.sede']);

        // Aplicar filtros
        if ($this->sedeFiltro) {
            $query->whereHas('aula', function ($q) {
                $q->where('sede_id', $this->sedeFiltro);
            });
        }

        if ($this->tipoAulaFiltro) {
            $query->whereHas('aula', function ($q) {
                $q->where('tipo_aula_id', $this->tipoAulaFiltro);
            });
        }

        $horarios = $query->orderBy('dia')
            ->orderBy('hora_inicio')
            ->paginate(10);

        return view('livewire.materias.horarios-materia', [
            'horarios' => $horarios,
            'sedesFiltro' => Sede::all(), // Reutilizamos las sedes existentes
            'tiposAulaFiltro' => TipoAula::all()
        ]);
    }
}
