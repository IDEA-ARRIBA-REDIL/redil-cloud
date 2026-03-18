<?php

namespace App\Livewire\Maestros;

use App\Models\HorarioMateriaPeriodo;
use App\Models\Maestro;
use App\Models\MateriaPeriodo;
use App\Models\NivelPeriodo;
use App\Models\Periodo; // Asegúrate de importar Sede
use App\Models\Sede;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Component;

class AsignarHorarioModal extends Component
{
    public bool $mostrarModal = false;

    public ?Maestro $maestro = null;

    public $periodoIdSeleccionado = null;

    public $nivelPeriodoIdSeleccionado = null; // NUEVA PROPIEDAD

    public $materiaPeriodoIdSeleccionada = null;

    public $sedeIdSeleccionada = null; // NUEVA PROPIEDAD

    public $horarioMateriaPeriodoIdSeleccionado = null;

    public $periodos = [];

    public $nivelesPeriodo = []; // NUEVA PROPIEDAD

    public $materiasPeriodo = [];

    public $sedesDisponibles = []; // NUEVA PROPIEDAD

    public $horariosMateriaPeriodoDisponibles = [];

    protected function rules()
    {
        return [
            'periodoIdSeleccionado' => 'required|integer|exists:periodos,id',
            'materiaPeriodoIdSeleccionada' => 'required|integer|exists:materia_periodo,id',
            'nivelPeriodoIdSeleccionado' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if ($this->requiereNivel() && empty($value)) {
                        $fail('Debes seleccionar un nivel para este periodo.');
                    }
                },
            ],
            'sedeIdSeleccionada' => 'required|integer|exists:sedes,id', // NUEVA REGLA
            'horarioMateriaPeriodoIdSeleccionado' => [
                'required',
                'integer',
                'exists:horarios_materia_periodo,id',
            ],
        ];
    }

    protected $messages = [
        'periodoIdSeleccionado.required' => 'Debes seleccionar un periodo.',
        'nivelPeriodoIdSeleccionado.required' => 'Debes seleccionar un nivel.',
        'materiaPeriodoIdSeleccionada.required' => 'Debes seleccionar una materia.',
        'sedeIdSeleccionada.required' => 'Debes seleccionar una sede.', // NUEVO MENSAJE
        'horarioMateriaPeriodoIdSeleccionado.required' => 'Debes seleccionar un horario.',
        'horarioMateriaPeriodoIdSeleccionado.exists' => 'El horario seleccionado no es válido.',
    ];

    #[On('abrirModalAsignarHorario')]
    public function abrirModal(int $maestroId)
    {
        $this->maestro = Maestro::find($maestroId);
        if (! $this->maestro) {
            $this->dispatch('mensajeError', 'Maestro no encontrado.');

            return;
        }
        $this->resetearFormulario();
        $this->cargarPeriodos();
        $this->mostrarModal = true;
    }

    public function cerrarModal()
    {
        $this->mostrarModal = false;
        $this->resetearFormulario();
    }

    private function resetearFormulario()
    {
        $this->reset([
            'periodoIdSeleccionado',
            'nivelPeriodoIdSeleccionado', // RESETEAR NUEVA PROPIEDAD
            'materiaPeriodoIdSeleccionada',
            'sedeIdSeleccionada', // RESETEAR NUEVA PROPIEDAD
            'horarioMateriaPeriodoIdSeleccionado',
            'materiasPeriodo',
            'nivelesPeriodo', // RESETEAR NUEVA PROPIEDAD
            'sedesDisponibles', // RESETEAR NUEVA PROPIEDAD
            'horariosMateriaPeriodoDisponibles',
        ]);
        $this->resetErrorBag();
    }

    public function mount()
    {
        // No cargamos periodos aquí para no hacerlo siempre, solo al abrir el modal
    }

    public function cargarPeriodos()
    {
        $this->periodos = Periodo::orderBy('nombre')->get(['id', 'nombre', 'fecha_inicio', 'fecha_fin']);
    }

    public function updatedPeriodoIdSeleccionado($value)
    {
        $this->nivelPeriodoIdSeleccionado = null;
        $this->materiaPeriodoIdSeleccionada = null;
        $this->sedeIdSeleccionada = null;
        $this->horarioMateriaPeriodoIdSeleccionado = null;
        $this->nivelesPeriodo = [];
        $this->materiasPeriodo = [];
        $this->sedesDisponibles = [];
        $this->horariosMateriaPeriodoDisponibles = [];

        if ($value) {
            $periodo = Periodo::with('escuela')->find($value);

            // Si la escuela es de niveles agrupados, cargamos los niveles del periodo
            if ($periodo && $periodo->escuela && $periodo->escuela->tipo_matricula === 'niveles_agrupados') {
                $this->nivelesPeriodo = NivelPeriodo::where('periodo_id', $value)
                    ->with('nivelEscuela:id,nombre')
                    ->get()
                    ->map(function ($np) {
                        return [
                            'id' => $np->id,
                            'nombre' => $np->nivelEscuela->nombre ?? 'Nivel sin nombre',
                        ];
                    })->toArray();
            } else {
                // Si no tiene niveles, cargamos todas las materias del periodo directamente
                $this->cargarMateriasPorPeriodo($value);
            }
        }
    }

    public function updatedNivelPeriodoIdSeleccionado($value)
    {
        $this->materiaPeriodoIdSeleccionada = null;
        $this->sedeIdSeleccionada = null;
        $this->horarioMateriaPeriodoIdSeleccionado = null;
        $this->sedesDisponibles = [];
        $this->horariosMateriaPeriodoDisponibles = [];

        if ($value) {
            $nivelPeriodo = NivelPeriodo::find($value);
            if ($nivelPeriodo) {
                $this->cargarMateriasPorPeriodo($this->periodoIdSeleccionado, $nivelPeriodo->nivel_escuela_id);
            }
        } else {
            $this->materiasPeriodo = [];
        }
    }

    private function cargarMateriasPorPeriodo($periodoId, $nivelId = null)
    {
        $query = MateriaPeriodo::where('periodo_id', $periodoId)
            ->with('materia:id,nombre');

        if ($nivelId) {
            $query->where('nivel_id', $nivelId);
        }

        $this->materiasPeriodo = $query->select('id', 'materia_id', 'descripcion')
            ->get()
            ->map(function ($mp) {
                return [
                    'id' => $mp->id,
                    'nombre_display' => $mp->materia->nombre.($mp->descripcion ? " ({$mp->descripcion})" : ''),
                ];
            })->toArray();
    }

    public function requiereNivel(): bool
    {
        if (! $this->periodoIdSeleccionado) {
            return false;
        }
        $periodo = Periodo::with('escuela')->find($this->periodoIdSeleccionado);

        return $periodo && $periodo->escuela && $periodo->escuela->tipo_matricula === 'niveles_agrupados';
    }

    public function updatedMateriaPeriodoIdSeleccionada($value)
    {
        $this->sedeIdSeleccionada = null;
        $this->horarioMateriaPeriodoIdSeleccionado = null;
        $this->horariosMateriaPeriodoDisponibles = [];

        if ($value) {
            // Cargar sedes disponibles para esta MateriaPeriodo
            // Esto implica encontrar todos los HorarioMateriaPeriodo para esta MateriaPeriodo,
            // luego, a través de HorarioBase -> Aula -> Sede, obtener las sedes únicas.
            $materiaPeriodo = MateriaPeriodo::find($value);
            if ($materiaPeriodo) {
                // Usamos el método que ya tenías, ¡perfecto!
                $this->sedesDisponibles = HorarioMateriaPeriodo::getSedesForMateriaPeriodo($materiaPeriodo->id)
                    ->map(function ($sede) {
                        return [
                            'id' => $sede->id,
                            'nombre' => $sede->nombre,
                        ];
                    })->toArray();
            }
        } else {
            $this->sedesDisponibles = [];
        }
    }

    public function updatedSedeIdSeleccionada($value) // NUEVO MÉTODO
    {
        $this->horarioMateriaPeriodoIdSeleccionado = null;
        if ($value && $this->materiaPeriodoIdSeleccionada && $this->maestro) {
            $horariosAsignadosAlMaestroIds = $this->maestro->horariosMateriaPeriodo()
                ->pluck('horarios_materia_periodo.id')
                ->toArray();

            $this->horariosMateriaPeriodoDisponibles = HorarioMateriaPeriodo::where('materia_periodo_id', $this->materiaPeriodoIdSeleccionada)
                ->whereHas('horarioBase.aula', function ($query) use ($value) {
                    $query->where('sede_id', $value);
                })
                ->get();

        } else {
            $this->horariosMateriaPeriodoDisponibles = [];
        }
    }

    public function asignarHorario()
    {
        $this->validate();

        if (! $this->maestro) {
            $this->dispatch('mensajeError', 'No se ha especificado un maestro.');

            return;
        }

        try {
            $yaAsignado = $this->maestro->horariosMateriaPeriodo()
                ->where('horario_materia_periodo_id', $this->horarioMateriaPeriodoIdSeleccionado)
                ->exists();

            if ($yaAsignado) {
                $this->addError('horarioMateriaPeriodoIdSeleccionado', 'Este horario ya está asignado a este maestro.');

                return;
            }

            $this->maestro->horariosMateriaPeriodo()->attach($this->horarioMateriaPeriodoIdSeleccionado);

            $this->cerrarModal();
            // $this->dispatch('horarioAsignadoCorrectamente'); // Ya no usamos este para refresco parcial
            $this->dispatch('mensajeExito', 'Horario asignado correctamente al maestro.');

            // Emitir un evento para que JavaScript recargue la página
            $this->dispatch('recargarPagina'); // Evento para JavaScript

        } catch (\Exception $e) {
            Log::error("Error al asignar horario {$this->horarioMateriaPeriodoIdSeleccionado} al maestro {$this->maestro->id}: ".$e->getMessage());
            $this->dispatch('mensajeError', 'Ocurrió un error al asignar el horario.');
        }
    }

    public function render()
    {
        return view('livewire.maestros.asignar-horario-modal');
    }
}
