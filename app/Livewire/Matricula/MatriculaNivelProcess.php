<?php

namespace App\Livewire\Matricula;

use App\Models\Escuela;
use App\Models\NivelAgrupacion;
use App\Models\Periodo;
use App\Models\HorarioMateriaPeriodo;
use App\Services\MatriculaNivelService;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class MatriculaNivelProcess extends Component
{
    public $escuela;
    public $periodo;

    // Pasos del Wizard
    public $currentStep = 1;

    // Datos de selección
    public $nivelSeleccionadoId;
    public $seleccionHorarios = []; // [materia_id => horario_id]

    public function mount(Escuela $escuela, Periodo $periodo)
    {
        $this->escuela = $escuela;
        $this->periodo = $periodo;
    }

    public function seleccionarNivel($nivelId)
    {
        $this->nivelSeleccionadoId = $nivelId;
        $this->currentStep = 2;
        // Inicializar array de selección
        $this->seleccionHorarios = [];
    }

    public function irAPaso($paso)
    {
        // Validaciones simples para retroceder o avanzar
        if ($paso < $this->currentStep) {
            $this->currentStep = $paso;
        }
    }

    public function confirmarMatricula()
    {
        // Validar que todas las materias obligatorias tengan horario seleccionado
        $nivel = NivelAgrupacion::find($this->nivelSeleccionadoId);
        $materiasObligatorias = $nivel->materias()->wherePivot('es_obligatoria', true)->pluck('materias.id')->toArray();

        foreach ($materiasObligatorias as $materiaId) {
            if (!isset($this->seleccionHorarios[$materiaId]) || empty($this->seleccionHorarios[$materiaId])) {
                $this->dispatch('msn', ['icon' => 'error', 'title' => 'Debes seleccionar un horario para todas las materias obligatorias.']);
                return;
            }
        }

        try {
            DB::beginTransaction();

            $service = app(MatriculaNivelService::class);
            $usuario = auth()->user();

            $service->inscribirEstudiante(
                $usuario,
                $nivel,
                $this->periodo,
                $this->seleccionHorarios
            );

            DB::commit();

            $this->currentStep = 3; // Paso de éxito

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('msn', ['icon' => 'error', 'title' => 'Error al procesar matrícula: ' . $e->getMessage()]);
        }
    }

    public function render()
    {
        $niveles = [];
        $materias = [];

        if ($this->currentStep == 1) {
            $niveles = NivelAgrupacion::where('escuela_id', $this->escuela->id)
                ->where('activo', true)
                ->orderBy('orden')
                ->get();
        }

        if ($this->currentStep == 2 && $this->nivelSeleccionadoId) {
             $nivel = NivelAgrupacion::find($this->nivelSeleccionadoId);
             $materias = $nivel->materias()->orderBy('orden')->get();

             // Cargar horarios disponibles para estas materias en este periodo
             foreach($materias as $materia) {
                 $materia->horariosDisponibles = HorarioMateriaPeriodo::where('materia_id', $materia->id)
                    ->where('periodo_id', $this->periodo->id)
                    // ->where('cupos_disponibles', '>', 0) // Validar cupos si fuera necesario
                    ->get();
             }
        }

        return view('livewire.matricula.matricula-nivel-process', [
            'niveles' => $niveles,
            'materias' => $materias
        ]);
    }
}
