<?php

namespace App\Livewire\Escuelas;

use App\Models\CortePeriodo; // Modelo principal para este componente
use App\Models\Periodo;
use App\Models\CorteEscuela; // Para acceder a nombre y orden del corte base
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\Attributes\On;
use Carbon\Carbon;

class CortesPeriodo extends Component
{
    public Periodo $periodo;
    public $cortesPeriodo; // Colección de modelos CortePeriodo
    public $sumaPorcentajesActual = 0; // NUEVA: Para mostrar la suma actual en la vista

    // Propiedades para la edición en el offcanvas
    public ?CortePeriodo $cortePeriodoEdicion = null; // El modelo CortePeriodo que se está editando
    public $fecha_inicio_editar;
    public $fecha_fin_editar;
    public $porcentaje_editar;

    /**
     * Reglas de validación para el formulario de edición.
     */
    protected function rules()
    {
        $periodoFechaInicio = $this->periodo->fecha_inicio ? Carbon::parse($this->periodo->fecha_inicio)->toDateString() : null;
        $periodoFechaFin = $this->periodo->fecha_fin ? Carbon::parse($this->periodo->fecha_fin)->toDateString() : null;

        return [
            'fecha_inicio_editar' => [
                'required',
                'date',
                $periodoFechaInicio ? 'after_or_equal:' . $periodoFechaInicio : '',
                'before_or_equal:fecha_fin_editar',
            ],
            'fecha_fin_editar' => [
                'required',
                'date',
                $periodoFechaFin ? 'before_or_equal:' . $periodoFechaFin : '',
                'after_or_equal:fecha_inicio_editar',
            ],
            'porcentaje_editar' => 'required|numeric|min:0|max:100',
        ];
    }

    /**
     * Mensajes de validación personalizados.
     */
    protected $messages = [
        'fecha_inicio_editar.required' => 'La fecha de inicio es obligatoria.',
        'fecha_inicio_editar.date' => 'La fecha de inicio debe ser una fecha válida.',
        'fecha_inicio_editar.after_or_equal' => 'La fecha de inicio debe ser igual o posterior a la fecha de inicio del período.',
        'fecha_inicio_editar.before_or_equal' => 'La fecha de inicio no puede ser posterior a la fecha de fin del corte.',
        'fecha_fin_editar.required' => 'La fecha de fin es obligatoria.',
        'fecha_fin_editar.date' => 'La fecha de fin debe ser una fecha válida.',
        'fecha_fin_editar.before_or_equal' => 'La fecha de fin debe ser igual o anterior a la fecha de fin del período.',
        'fecha_fin_editar.after_or_equal' => 'La fecha de fin no puede ser anterior a la fecha de inicio del corte.',
        'porcentaje_editar.required' => 'El porcentaje es obligatorio.',
        'porcentaje_editar.numeric' => 'El porcentaje debe ser un valor numérico.',
        'porcentaje_editar.min' => 'El porcentaje no puede ser menor que 0.',
        'porcentaje_editar.max' => 'El porcentaje no puede ser mayor que 100.',
    ];

    /**
     * Se ejecuta cuando el componente es inicializado.
     */
    public function mount(Periodo $periodo)
    {
        $this->periodo = $periodo;
        $this->cargarCortesPeriodo();
    }

    /**
     * Carga o recarga los cortes de período asociados al periodo actual.
     * Se ordenan por el 'orden' del CorteEscuela relacionado.
     * ACTUALIZADO: Calcula la suma de los porcentajes.
     */
    public function cargarCortesPeriodo()
    {
        $this->cortesPeriodo = $this->periodo->cortesPeriodo()
            ->join('cortes_escuela', 'cortes_periodo.corte_escuela_id', '=', 'cortes_escuela.id')
            ->orderBy('cortes_escuela.orden', 'asc')
            ->select('cortes_periodo.*')
            ->with('corteEscuela')
            ->get();

        // NUEVO: Calcular la suma de los porcentajes para la alerta persistente
        $this->sumaPorcentajesActual = $this->cortesPeriodo->sum('porcentaje');
    }

    /**
     * Prepara el offcanvas para editar un CortePeriodo existente.
     */
    public function prepararEdicionCortePeriodo(CortePeriodo $cortePeriodo)
    {
        $this->cortePeriodoEdicion = $cortePeriodo;
        $this->fecha_inicio_editar = $cortePeriodo->fecha_inicio ? Carbon::parse($cortePeriodo->fecha_inicio)->toDateString() : null;
        $this->fecha_fin_editar = $cortePeriodo->fecha_fin ? Carbon::parse($cortePeriodo->fecha_fin)->toDateString() : null;
        $this->porcentaje_editar = $cortePeriodo->porcentaje;
        
        $this->resetErrorBag();
        $this->dispatch('abrirOffcanvas', nombreModal: 'offcanvasEditarCortePeriodo');
    }

    /**
     * Actualiza el CortePeriodo que se está editando.
     */
    public function actualizarCortePeriodo()
    {
        $this->validate();

        $querySolapamiento = $this->periodo->cortesPeriodo()
            ->where('id', '!=', $this->cortePeriodoEdicion->id)
            ->where(function ($q) {
                $q->where(function ($sq) {
                    $sq->where('fecha_inicio', '<=', $this->fecha_fin_editar)
                       ->where('fecha_fin', '>=', $this->fecha_inicio_editar);
                });
            });

        if ($querySolapamiento->exists()) {
            $this->addError('fecha_inicio_editar', 'Las fechas del corte se superponen con otro corte existente.');
            $this->addError('fecha_fin_editar', 'Las fechas del corte se superponen con otro corte existente.');
            return;
        }

        $otrosPorcentajes = $this->periodo->cortesPeriodo()
            ->where('id', '!=', $this->cortePeriodoEdicion->id)
            ->sum('porcentaje');
        $nuevoTotalPorcentaje = $otrosPorcentajes + (float)$this->porcentaje_editar;

        if ($nuevoTotalPorcentaje > 100) {
            $this->addError('porcentaje_editar', 'La suma total de porcentajes (' . number_format($nuevoTotalPorcentaje, 2) . '%) para el período no puede exceder el 100%.');
            return;
        }

        if ($this->cortePeriodoEdicion) {
            $this->cortePeriodoEdicion->update([
                'fecha_inicio' => $this->fecha_inicio_editar,
                'fecha_fin' => $this->fecha_fin_editar,
                'porcentaje' => $this->porcentaje_editar,
            ]);
            session()->flash('mensaje_exito', 'Corte de período actualizado exitosamente.');
            $this->cerrarOffcanvasEdicion();
            $this->cargarCortesPeriodo(); // Recalculará la suma de porcentajes
        }
    }

    /**
     * Cierra el offcanvas de edición y resetea el estado del formulario.
     */
    public function cerrarOffcanvasEdicion()
    {
        $this->cortePeriodoEdicion = null;
        $this->fecha_inicio_editar = null;
        $this->fecha_fin_editar = null;
        $this->porcentaje_editar = null;
        $this->resetErrorBag();
        $this->dispatch('cerrarOffcanvas', nombreModal: 'offcanvasEditarCortePeriodo');
    }

    /**
     * Escucha el evento 'offcanvasFueCerrado' disparado por JS.
     */
    #[On('offcanvasFueCerrado')]
    public function manejarCierreExternoOffcanvas()
    {
        if ($this->cortePeriodoEdicion) { 
            $this->cerrarOffcanvasEdicion();
        }
    }
    
    /**
     * Prepara la confirmación para eliminar un CortePeriodo.
     */
    public function confirmarEliminacionCortePeriodo($idCortePeriodo)
    {
        $cortePeriodo = CortePeriodo::with('corteEscuela')->find($idCortePeriodo);
        if ($cortePeriodo) {
            $nombreCorte = $cortePeriodo->corteEscuela ? $cortePeriodo->corteEscuela->nombre : 'este corte';
            $this->dispatch('mostrar-confirmacion-eliminacion', 
                idCortePeriodo: $idCortePeriodo, 
                nombreCorte: $nombreCorte
            );
        }
    }

    /**
     * Escucha el evento 'eliminar-corte-periodo-confirmado' y elimina el CortePeriodo.
     */
    #[On('eliminar-corte-periodo-confirmado')]
    public function eliminarCortePeriodo($payload)
    {
        $idCortePeriodo = $payload['idCortePeriodo'] ?? null;
        if ($idCortePeriodo) {
            $cortePeriodo = CortePeriodo::find($idCortePeriodo);

            if ($cortePeriodo && $cortePeriodo->periodo_id == $this->periodo->id) {
                $cortePeriodo->delete();
                $this->cargarCortesPeriodo(); // Recalculará la suma de porcentajes
                session()->flash('mensaje_exito', 'Corte de período eliminado exitosamente.');
            } else {
                session()->flash('mensaje_error', 'No se pudo encontrar o eliminar el corte de período.');
            }
        }
    }

    /**
     * NUEVO: Cambia el estado de un CortePeriodo a 'abierto' (cerrado = false).
     */
    public function abrirCorte($idCortePeriodo)
    {
        $cortePeriodo = CortePeriodo::find($idCortePeriodo);
        if ($cortePeriodo && $cortePeriodo->periodo_id == $this->periodo->id) {
            $cortePeriodo->update(['cerrado' => false]);
            $this->cargarCortesPeriodo(); // Recarga los cortes para actualizar la vista
            session()->flash('mensaje_exito', 'El corte "' . ($cortePeriodo->corteEscuela->nombre ?? 'N/A') . '" ha sido abierto.');
        } else {
            session()->flash('mensaje_error', 'No se pudo encontrar o abrir el corte de período.');
        }
    }

    /**
     * NUEVO: Cambia el estado de un CortePeriodo a 'cerrado' (cerrado = true).
     */
    public function cerrarCorte($idCortePeriodo)
    {
        $cortePeriodo = CortePeriodo::find($idCortePeriodo);
        if ($cortePeriodo && $cortePeriodo->periodo_id == $this->periodo->id) {
            $cortePeriodo->update(['cerrado' => true]);
            $this->cargarCortesPeriodo(); // Recarga los cortes para actualizar la vista
            session()->flash('mensaje_exito', 'El corte "' . ($cortePeriodo->corteEscuela->nombre ?? 'N/A') . '" ha sido cerrado.');
        } else {
            session()->flash('mensaje_error', 'No se pudo encontrar o cerrar el corte de período.');
        }
    }


    /**
     * Renderiza la vista del componente.
     */
    public function render()
    {
        return view('livewire.escuelas.cortes-periodo');
    }
}
