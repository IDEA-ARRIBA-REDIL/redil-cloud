<?php

namespace App\Livewire\Matricula;

use App\Models\HorarioMateriaPeriodo;
use App\Models\MateriaPeriodo;
use App\Models\NivelEscuela;
use App\Models\Periodo;
use App\Models\User;
use App\Services\MatriculaNivelService;
use App\Services\MatriculaService;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Componente Livewire para el Modal de Matrícula por Nivel.
 * Permite seleccionar horarios para múltiples materias de un mismo grado/nivel simultáneamente.
 */
class MatriculaNivelModal extends Component
{
    public $showModal = false;

    // Modelos principales
    public $estudiante;

    public $nivel;

    public $periodo;

    // Estado del formulario
    public $seleccionHorarios = []; // [materia_id => horario_id]

    /**
     * Escucha el evento para abrir el modal desde la vista de gestión.
     */
    #[On('abrirModalMatriculaNivel')]
    public function openModal($nivelId, $estudianteId, MatriculaService $matriculaService)
    {
        $this->reset(['seleccionHorarios']);

        $this->nivel = NivelEscuela::find($nivelId);
        $this->estudiante = User::find($estudianteId);

        if (! $this->nivel || ! $this->estudiante) {
            $this->dispatch('swal:error', ['title' => 'Error', 'text' => 'Nivel o estudiante no encontrado.']);

            return;
        }

        // 1. Buscamos el periodo activo de la escuela del nivel
        $this->periodo = Periodo::where('escuela_id', $this->nivel->escuela_id)
            ->where('estado', true)
            ->first();

        if (! $this->periodo) {
            $this->dispatch('swal:error', ['title' => 'Error', 'text' => 'No hay un periodo activo para esta escuela.']);

            return;
        }

        // 2. VALIDACIÓN DE ELEGIBILIDAD (Workflow Rule 1)
        $reporte = $matriculaService->getReporteDisponibilidadNiveles($this->estudiante, $this->nivel->escuela)
            ->where('item.id', $this->nivel->id)
            ->first();

        if ($reporte && $reporte->estado === 'BLOQUEADA') {
            $this->dispatch('swal:error', [
                'title' => 'Requisitos No Cumplidos',
                'text' => 'Motivos: '.implode(', ', $reporte->motivos),
            ]);

            return;
        }

        // Verificamos si hay materias antes de mostrar el modal
        if ($this->getMateriasDelNivel()->isEmpty()) {
            $this->dispatch('swal:warning', [
                'title' => 'Sin Materias',
                'text' => 'No se encontraron materias configuradas para este nivel en el periodo actual.',
            ]);

            return;
        }

        $this->showModal = true;
    }

    /**
     * Obtiene las materias habilitadas para el nivel y periodo actual.
     */
    private function getMateriasDelNivel()
    {
        if (! $this->nivel || ! $this->periodo) {
            return collect();
        }

        return MateriaPeriodo::with('materia')
            ->where('nivel_id', $this->nivel->id)
            ->where('periodo_id', $this->periodo->id)
            ->get()
            ->map(function ($mp) {
                $materia = $mp->materia;

                if ($materia) {
                    $materia->horariosDisponibles = HorarioMateriaPeriodo::where('materia_periodo_id', $mp->id)
                        ->where('habilitado', true)
                        ->with(['horarioBase.aula.sede', 'maestros.user'])
                        ->get();
                }

                return $materia;
            })->filter();
    }

    /**
     * Procesa la matrícula de todas las materias seleccionadas.
     */
    public function matricularNivel(MatriculaNivelService $service)
    {
        $materias = $this->getMateriasDelNivel();

        // 1. Validar que se haya seleccionado un horario para CADA materia
        foreach ($materias as $materia) {
            if (! isset($this->seleccionHorarios[$materia->id]) || empty($this->seleccionHorarios[$materia->id])) {
                $this->dispatch('swal:warning', [
                    'title' => 'Selección Incompleta',
                    'text' => "Debe seleccionar un horario para la materia: {$materia->nombre}",
                ]);

                return;
            }
        }

        try {
            // 2. Ejecutar la lógica de inscripción masiva vía servicio
            $service->inscribirEstudiante(
                $this->estudiante,
                $this->nivel,
                $this->periodo,
                $this->seleccionHorarios
            );

            $this->dispatch('swal:success', [
                'title' => '¡Matrícula Exitosa!',
                'text' => "El estudiante ha sido matriculado en el nivel {$this->nivel->nombre}.",
            ]);

            $this->closeModal();
            $this->dispatch('recargarPagina'); // Evento para que el controlador refresque la lista

        } catch (\Exception $e) {
            $this->dispatch('swal:error', [
                'title' => 'Error en Matrícula',
                'text' => $e->getMessage(),
            ]);
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
    }

    public function render()
    {
        return view('livewire.matricula.matricula-nivel-modal', [
            'materiasDelNivel' => $this->getMateriasDelNivel(),
        ]);
    }
}
