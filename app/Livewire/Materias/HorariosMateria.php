<?php

namespace App\Livewire\Materias;

use App\Exports\HorariosMateriaExport;
use App\Models\Aula;
use App\Models\HorarioBase;
use App\Models\Materia;
use App\Models\Matricula;
use App\Models\MatriculaHorarioMateriaPeriodo;
use App\Models\PasoCrecimiento;
use App\Models\Sede;
use App\Models\TipoAula;
use Illuminate\Support\Str;
use Livewire\Component; // Importa la clase de exportación
use Livewire\WithPagination; // Importa el Facade de Excel
use Maatwebsite\Excel\Facades\Excel; // Para generar el nombre del archivo

class HorariosMateria extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

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
        'filtrosActualizados' => '$refresh',
    ];

    protected $rules = [
        'sedeAula' => 'required', // Para validar la sede seleccionada
        'aula_id' => 'required',
        'dia' => 'required|integer|between:0,6',
        'hora_inicio' => 'required',
        'hora_fin' => 'required',
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
        $this->variable = 'inicio';
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
        $fileName = 'horarios-'.Str::slug($this->materia->nombre).'.xlsx';

        // Usa el Facade de Excel para descargar, pasándole tu clase de exportación y la materia actual
        return Excel::download(new HorariosMateriaExport($this->materia), $fileName);
    }

    public function guardarHorario()
    {
        // 1. Validar los datos del formulario
        $this->validate();

        // 2. Creación de nuevo horario base
        $horario = new HorarioBase;
        $horario->materia_id = $this->materia->id;
        $horario->aula_id = $this->aula_id;
        $horario->dia = $this->dia;
        $horario->capacidad = $this->cupos_iniciales;
        $horario->capacidad_limite = $this->cupos_limite;
        $horario->hora_inicio = $this->hora_inicio;
        $horario->hora_fin = $this->hora_fin;
        $horario->activo = true; // Valor por defecto
        $horario->save();

        // 3. Limpiar todos los campos del formulario y cerrar el offcanvas
        $this->resetearFormulario();
        $this->dispatch('cerrarOffcanvas', nombreModal: 'offcanvasRight');
        $this->dispatch(
            'notificacion',
            tipo: 'success',
            titulo: '¡Horario creado!',
            mensaje: 'El horario se ha creado correctamente.'
        );
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
        $this->dispatch(
            'notificacion',
            tipo: 'success',
            titulo: '¡Actualizado!',
            mensaje: 'Horario actualizado correctamente.'
        );
        $this->resetearFormulario();
        $this->horarioEditando = null;
    }

    // Modifica el método abrirFormularioEditar
    public function abrirFormularioEditar(HorarioBase $horario)
    {
        $this->resetValidation();
        $this->horarioEditando = $horario;
        $this->sedeAula = $horario->aula->sede->id;
        $this->buscarAulas($this->sedeAula);
        $this->aula_id = $horario->aula_id;
        $this->dia = $horario->dia;
        $this->cupos_iniciales = $horario->capacidad;
        $this->cupos_limite = $horario->capacidad_limite;
        $this->hora_inicio = $horario->hora_inicio;
        $this->hora_fin = $horario->hora_fin;

        $this->dispatch('abrirOffcanvas', nombreModal: 'offcanvasEditar');
    }

    public function abrirFormularioNuevo()
    {
        $this->resetearFormulario();
        $this->dispatch('abrirOffcanvas', nombreModal: 'offcanvasRight');
    }

    public function cancelar()
    {
        $this->resetearFormulario();
        $this->dispatch('cerrarOffcanvas', nombreModal: 'offcanvasRight');
    }

    public function toggleEstado(int $horarioId)
    {
        $horario = HorarioBase::find($horarioId);
        if (! $horario) {
            return;
        }

        $horario->activo = ! $horario->activo;
        $horario->save();

        $estadoTexto = $horario->activo ? 'activado' : 'inactivado';
        $this->dispatch(
            'notificacion',
            tipo: 'success',
            titulo: 'Estado actualizado',
            mensaje: "El horario ha sido {$estadoTexto} correctamente."
        );
    }

    /**
     * Verifica si el horario base tiene matrículas registradas en algún periodo.
     * - Si tiene matrículas: no permite eliminar, deshabilita el horario y notifica al usuario.
     * - Si no tiene matrículas: solicita confirmación mediante SweetAlert2.
     */
    public function confirmarEliminar(int $horarioId)
    {
        $horario = HorarioBase::with('aula.sede')->find($horarioId);
        if (! $horario) {
            $this->dispatch(
                'notificacion',
                tipo: 'error',
                titulo: 'Error',
                mensaje: 'No se encontró el horario seleccionado.'
            );

            return;
        }

        // 1. Obtener los IDs de horarios_materia_periodo vinculados a este horario base
        $horariosMPIds = $horario->horariosMateriaPeriodo()->pluck('id');

        // 2. Comprobar si existen matrículas registradas en esos horarios
        $tieneMatriculas = $horariosMPIds->isNotEmpty() && (
            Matricula::whereIn('horario_materia_periodo_id', $horariosMPIds)->exists() ||
            MatriculaHorarioMateriaPeriodo::whereIn('horario_materia_periodo_id', $horariosMPIds)->exists()
        );

        // 3. Si tiene matrículas, deshabilitar el horario e informar que no se puede eliminar
        if ($tieneMatriculas) {
            $horario->activo = false;
            $horario->save();

            $this->dispatch(
                'notificacion',
                tipo: 'warning',
                titulo: 'No se puede eliminar',
                mensaje: 'Este horario ya tiene matrículas registradas, por lo que no se puede eliminar y ha sido deshabilitado.'
            );

            return;
        }

        // 4. Si no tiene matrículas, disparar confirmación con SweetAlert2
        $this->dispatch(
            'confirmarEliminarHorario',
            id: $horario->id,
            dia: $horario->dia_semana,
            hora: "{$horario->hora_inicio} - {$horario->hora_fin}",
            aula: $horario->aula->nombre ?? ''
        );
    }

    public function eliminarHorario(int $horarioId)
    {
        $horario = HorarioBase::find($horarioId);
        if (! $horario) {
            return;
        }

        // Validación de seguridad adicional antes de eliminar
        $horariosMPIds = $horario->horariosMateriaPeriodo()->pluck('id');
        $tieneMatriculas = $horariosMPIds->isNotEmpty() && (
            Matricula::whereIn('horario_materia_periodo_id', $horariosMPIds)->exists() ||
            MatriculaHorarioMateriaPeriodo::whereIn('horario_materia_periodo_id', $horariosMPIds)->exists()
        );

        if ($tieneMatriculas) {
            $horario->activo = false;
            $horario->save();

            $this->dispatch(
                'notificacion',
                tipo: 'warning',
                titulo: 'No se puede eliminar',
                mensaje: 'Este horario ya tiene matrículas registradas, por lo que no se puede eliminar y ha sido deshabilitado.'
            );

            return;
        }

        $horario->delete();
        $this->dispatch(
            'notificacion',
            tipo: 'success',
            titulo: '¡Eliminado!',
            mensaje: 'Horario eliminado correctamente.'
        );
    }

    public function resetearFormulario()
    {
        $this->reset([
            'sede_id',
            'sedeAula',
            'aulas',
            'aula_id',
            'dia',
            'cupos_iniciales',
            'cupos_limite',
            'hora_inicio',
            'hora_fin',
            'horarioEditando',
        ]);
        $this->aulas = [];
        $this->resetErrorBag();
        $this->resetValidation();
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
            'tiposAulaFiltro' => TipoAula::all(),
        ]);
    }
}
