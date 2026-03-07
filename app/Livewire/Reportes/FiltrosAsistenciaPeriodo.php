<?php

namespace App\Livewire\Reportes;

use Livewire\Component;
use App\Models\Periodo;
use App\Models\Sede;
use App\Models\Materia;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class FiltrosAsistenciaPeriodo extends Component
{
    public $periodoSeleccionado = null;
    public $sedesSeleccionadas = [];
    // CAMBIO: Ahora es un array para selección múltiple
    public $materiasSeleccionadas = [];
    public $semanaSeleccionada = null;

    public $periodos = [];
    public $sedes = [];
    public $materias = [];
    public $semanas = [];

    public function mount()
    {
        $this->periodos = Periodo::orderBy('fecha_inicio', 'desc')->get();
    }

    public function updatedPeriodoSeleccionado($periodoId)
    {
        $this->reset(['sedesSeleccionadas', 'materiasSeleccionadas', 'semanaSeleccionada', 'sedes', 'materias', 'semanas']);
        $this->dispatch('actualizar-sedes-select2', sedes: []);
        $this->dispatch('actualizar-materias-select2', materias: []);

        if ($periodoId) {
            $periodo = Periodo::find($periodoId);
            if ($periodo) {
                $this->sedes = $periodo->sedes()->orderBy('nombre')->get();
            }
            $this->calcularSemanasDelPeriodo($periodoId);
            $this->dispatch('actualizar-sedes-select2', sedes: $this->sedes->toArray());
        }
    }

    public function updatedSedesSeleccionadas()
    {
        $this->reset(['materiasSeleccionadas', 'materias']);
        $this->dispatch('actualizar-materias-select2', materias: []);
        if (!empty($this->sedesSeleccionadas)) {
            $this->materias = Materia::whereHas('materiasPeriodo.horariosMateriaPeriodo.horarioBase.aula', function ($query) {
                $query->whereIn('sede_id', $this->sedesSeleccionadas);
            })->whereHas('materiasPeriodo', function ($q) {
                $q->where('periodo_id', $this->periodoSeleccionado);
            })->orderBy('nombre')->get();
            $this->dispatch('actualizar-materias-select2', materias: $this->materias->toArray());
        }
    }

    private function calcularSemanasDelPeriodo($periodoId)
    {
        $periodo = Periodo::with('escuela')->find($periodoId);
        if (!$periodo) return;
        $diaInicioSemana = $periodo->escuela->dia_inicio_semana ?? 0;
        $fechaInicio = Carbon::parse($periodo->fecha_inicio);
        $fechaFin = Carbon::parse($periodo->fecha_fin);
        $fechaInicioReal = $fechaInicio->copy()->startOfWeek($diaInicioSemana);
        $periodoDeFechas = CarbonPeriod::create($fechaInicioReal, '1 week', $fechaFin);
        $this->semanas = [];
        $contadorSemana = 1;
        foreach ($periodoDeFechas as $fecha) {
            $inicioSemana = $fecha->copy()->startOfWeek($diaInicioSemana);
            $finSemana = $fecha->copy()->endOfWeek($diaInicioSemana + 6);
            $this->semanas[] = [
                'valor' => $inicioSemana->toDateString() . '|' . $finSemana->toDateString(),
                'texto' => "Semana {$contadorSemana}: " . $inicioSemana->isoFormat('D MMM') . ' - ' . $finSemana->isoFormat('D MMM, YYYY')
            ];
            $contadorSemana++;
        }
    }

    public function render()
    {
        return view('livewire.reportes.filtros-asistencia-periodo');
    }
}
