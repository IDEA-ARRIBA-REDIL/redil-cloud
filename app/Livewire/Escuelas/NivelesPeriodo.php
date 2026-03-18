<?php

namespace App\Livewire\Escuelas;

use App\Models\NivelEscuela;
use App\Models\NivelPeriodo;
use App\Models\Periodo;
use Livewire\Attributes\On;
use Livewire\Component;

class NivelesPeriodo extends Component
{
    public Periodo $periodo;

    public $nivelesDisponibles = [];

    public $nivelSeleccionado = '';

    public $mostrarModalAnadir = false;

    public $mostrarModalDuplicar = false;

    public $periodoOrigenId = '';

    public $periodosDisponiblesParaDuplicar = [];

    public function mount(Periodo $periodo)
    {
        $this->periodo = $periodo;
    }

    public function abrirModalAnadir()
    {
        // Obtener niveles de la escuela que aún no están en este periodo
        $nivelesYaAsociados = NivelPeriodo::where('periodo_id', $this->periodo->id)
            ->pluck('nivel_escuela_id')
            ->toArray();

        $this->nivelesDisponibles = NivelEscuela::where('escuela_id', $this->periodo->escuela_id)
            ->whereNotIn('id', $nivelesYaAsociados)
            ->get();

        $this->mostrarModalAnadir = true;
    }

    public function anadirNivel()
    {
        $this->validate([
            'nivelSeleccionado' => 'required|exists:niveles_escuelas,id',
        ]);

        NivelPeriodo::create([
            'periodo_id' => $this->periodo->id,
            'nivel_escuela_id' => $this->nivelSeleccionado,
            'escuela_id' => $this->periodo->escuela_id,
        ]);

        $this->mostrarModalAnadir = false;
        $this->nivelSeleccionado = '';
        session()->flash('mensaje_exito', 'Grado añadido correctamente al periodo.');
    }

    public function abrirModalDuplicar()
    {
        $this->periodosDisponiblesParaDuplicar = Periodo::where('escuela_id', $this->periodo->escuela_id)
            ->where('id', '!=', $this->periodo->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $this->mostrarModalDuplicar = true;
    }

    public function duplicarConfiguracion()
    {
        $this->validate([
            'periodoOrigenId' => 'required|exists:periodos,id',
        ]);

        $periodoOrigen = Periodo::with(['nivelesPeriodo', 'materiasPeriodo.horariosMateriaPeriodo'])->find($this->periodoOrigenId);

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $nivelesAnadidos = 0;
            $materiasAnadidas = 0;

            foreach ($periodoOrigen->nivelesPeriodo as $nivelOrigen) {
                // 1. Asegurar el Nivel en el nuevo periodo
                $nivelDestino = NivelPeriodo::firstOrCreate([
                    'periodo_id' => $this->periodo->id,
                    'nivel_escuela_id' => $nivelOrigen->nivel_escuela_id,
                    'escuela_id' => $this->periodo->escuela_id,
                ]);

                if ($nivelDestino->wasRecentlyCreated) {
                    $nivelesAnadidos++;
                }

                // 2. Obtener materias del periodo origen para este nivel
                $materiasOrigen = \App\Models\MateriaPeriodo::where('periodo_id', $periodoOrigen->id)
                    ->where('nivel_id', $nivelOrigen->nivel_escuela_id)
                    ->get();

                foreach ($materiasOrigen as $mpOrigen) {
                    // Verificar si ya existe en el destino
                    $existeMateria = \App\Models\MateriaPeriodo::where('periodo_id', $this->periodo->id)
                        ->where('materia_id', $mpOrigen->materia_id)
                        ->where('nivel_id', $nivelOrigen->nivel_escuela_id)
                        ->exists();

                    if ($existeMateria) {
                        continue;
                    }

                    // Clonar MateriaPeriodo
                    $mpDestino = $mpOrigen->replicate();
                    $mpDestino->periodo_id = $this->periodo->id;
                    $mpDestino->finalizado = false;
                    $mpDestino->save();
                    $materiasAnadidas++;

                    // 3. Clonar Horarios si existen
                    foreach ($mpOrigen->horariosMateriaPeriodo as $hmpOrigen) {
                        $hmpDestino = $hmpOrigen->replicate();
                        $hmpDestino->materia_periodo_id = $mpDestino->id;
                        $hmpDestino->save();

                        // 4. Clonar Items de Evaluación asociados al horario
                        $itemsOrigen = \App\Models\ItemCorteMateriaPeriodo::where('horario_materia_periodo_id', $hmpOrigen->id)->get();
                        foreach ($itemsOrigen as $itemOrigen) {
                            // Buscar el CortePeriodo equivalente en el nuevo periodo por su relación con el corte_escuela_id
                            $corteEscuelaId = \App\Models\CortePeriodo::where('id', $itemOrigen->corte_periodo_id)->value('corte_escuela_id');
                            $corteDestinoId = \App\Models\CortePeriodo::where('periodo_id', $this->periodo->id)
                                ->where('corte_escuela_id', $corteEscuelaId)
                                ->value('id');

                            if ($corteDestinoId) {
                                $itemDestino = $itemOrigen->replicate();
                                $itemDestino->materia_periodo_id = $mpDestino->id;
                                $itemDestino->horario_materia_periodo_id = $hmpDestino->id;
                                $itemDestino->corte_periodo_id = $corteDestinoId;
                                $itemDestino->save();
                            }
                        }
                    }
                }
            }

            \Illuminate\Support\Facades\DB::commit();
            $this->mostrarModalDuplicar = false;
            $this->periodoOrigenId = '';

            session()->flash('mensaje_exito', "Se han duplicado $nivelesAnadidos grados y $materiasAnadidas materias exitosamente (incluyendo horarios e ítems).");
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Error al duplicar periodo: '.$e->getMessage());
            session()->flash('mensaje_error', 'Ocurrió un error al duplicar la configuración: '.$e->getMessage());
        }
    }

    public function confirmarEliminar($nivelPeriodoId)
    {
        $this->dispatch('confirmar-eliminar-nivel', $nivelPeriodoId);
    }

    #[On('eliminarNivelConfirmado')]
    public function eliminarNivel($nivelPeriodoId)
    {
        $nivelP = NivelPeriodo::find($nivelPeriodoId);
        if ($nivelP) {
            // Verificar si tiene materias asociadas en este periodo
            $tieneMaterias = \App\Models\MateriaPeriodo::where('periodo_id', $this->periodo->id)
                ->where('nivel_id', $nivelP->nivel_escuela_id)
                ->exists();

            if ($tieneMaterias) {
                session()->flash('mensaje_error', 'No se puede eliminar el grado porque ya tiene materias asociadas en este periodo.');

                return;
            }

            $nivelP->delete();
            session()->flash('mensaje_exito', 'Grado eliminado del periodo.');
        }
    }

    public function render()
    {
        $nivelesPeriodo = NivelPeriodo::where('periodo_id', $this->periodo->id)
            ->with('nivelEscuela')
            ->get();

        return view('livewire.escuelas.niveles-periodo', [
            'nivelesPeriodo' => $nivelesPeriodo,
        ]);
    }
}
