<?php

namespace App\Livewire\Escuelas;

use Livewire\Component;
use App\Models\Escuela;
use App\Models\Sede;
use App\Models\SistemaCalificacion;
use App\Models\CorteEscuela;
use App\Models\Periodo;
use App\Models\CortePeriodo; // Asegúrate que este modelo exista y tenga la relación corteEscuela
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class NuevoPeriodo extends Component
{
    // --- Estado del Wizard ---
    public int $currentStep = 1;
    public int $totalSteps = 2;

    // --- ID del Periodo en edición/creación ---
    public ?int $periodoId = null;
    public ?Periodo $periodoActual = null; // Para tener el objeto Periodo cargado

    // --- Listas para Selects (Paso 1) ---
    public Collection $escuelas;
    public Collection $sedes;
    public Collection $sistemasCalificacion;
    public Collection $cortesEscuelaDisponibles; // Para información o si se necesita en el futuro

    // --- Propiedades del Formulario (Paso 1) ---
    public string $nombre = '';
    public ?int $escuelaId = null;
    public ?int $sistema_calificacion_id = null;
    public string $fecha_inicio = '';
    public string $fecha_fin = '';
    public string $fecha_limite_maestro = ''; // Ya no es nullable según tus reglas
    public array $selectedSedes = [];

    // --- Propiedades para el Paso 2 (Lista de Cortes) ---
    public array $cortesDelPeriodo = []; // Se cargará desde la BD

    // --- Propiedades para el Offcanvas de Edición de Corte (Paso 2) ---
    public bool $showOffcanvas = false;
    public ?int $corteEnEdicionId = null;
    public array $corteEnEdicionData = [ // Estructura para el form del offcanvas
        'nombre_display' => '', // Nombre del corte escuela para mostrar
        'fecha_inicio' => '',
        'fecha_fin' => '',
        'porcentaje' => 0,
    ];

    public function mount(?int $periodo_id_externo = null) // Para permitir cargar un periodo existente
    {
        $this->escuelas = Escuela::orderBy('nombre')->get(['id', 'nombre']);
        $this->sedes = Sede::orderBy('nombre')->get(['id', 'nombre']);
        $this->sistemasCalificacion = SistemaCalificacion::orderBy('nombre')->get(['id', 'nombre']);
        $this->cortesEscuelaDisponibles = collect();

        if ($periodo_id_externo) {
            $this->loadPeriodoExistente($periodo_id_externo);
        }
    }

    protected function rulesForStep1(): array
    {
        return [
            'nombre' => 'required|string|max:200',
            'escuelaId' => 'required|integer',
            'sistema_calificacion_id' => 'required|integer',
            'fecha_inicio' => 'required|date_format:Y-m-d',
            'fecha_fin' => 'required|date_format:Y-m-d|after_or_equal:fecha_inicio',
            // Considera si la fecha límite debe ser after_or_equal al inicio o fin del periodo.
            // Usaré after_or_equal:fecha_fin, que es más común.
            'fecha_limite_maestro' => 'required|date_format:Y-m-d',
            'selectedSedes' => 'required|array|min:1',
            'selectedSedes.*' => 'required|integer',
        ];
    }

    protected function rulesForCorteEnEdicion(): array
    {
        if (!$this->periodoActual) {
            // Si no hay periodo actual cargado, algo anda mal.
            return ['corteEnEdicionData.porcentaje' => 'required']; // Falla genérica
        }
        $periodo_fecha_inicio = $this->periodoActual->fecha_inicio;
        $periodo_fecha_fin = $this->periodoActual->fecha_fin;

        return [
            'corteEnEdicionData.fecha_inicio' => [
                'required',
              
                function ($attribute, $value, $fail) use ($periodo_fecha_inicio) {
                    if (Carbon::parse($value)->lt(Carbon::parse($periodo_fecha_inicio))) {
                        $fail('La fecha de inicio del corte no puede ser anterior a la fecha de inicio del periodo ('.$periodo_fecha_inicio.').');
                    }
                },
            ],
            'corteEnEdicionData.fecha_fin' => [
                'required',
        
                'after_or_equal:corteEnEdicionData.fecha_inicio',
                function ($attribute, $value, $fail) use ($periodo_fecha_fin) {
                    if (Carbon::parse($value)->gt(Carbon::parse($periodo_fecha_fin))) {
                        $fail('La fecha de fin del corte no puede ser posterior a la fecha de fin del periodo ('.$periodo_fecha_fin.').');
                    }
                },
                // Validación de no solapamiento
                function ($attribute, $value, $fail) {
                    $otrosCortes = CortePeriodo::where('periodo_id', $this->periodoId)
                                              ->where('id', '!=', $this->corteEnEdicionId)
                                              ->get();
                    $inicioActual = Carbon::parse($this->corteEnEdicionData['fecha_inicio']);
                    $finActual = Carbon::parse($value);

                    foreach ($otrosCortes as $otroCorte) {
                        $inicioOtro = Carbon::parse($otroCorte->fecha_inicio);
                        $finOtro = Carbon::parse($otroCorte->fecha_fin);
                        if ($inicioActual->lte($finOtro) && $finActual->gte($inicioOtro)) {
                            // Intenta obtener el nombre del CorteEscuela asociado
                            $nombreOtroCorte = $otroCorte->corteEscuela ? $otroCorte->corteEscuela->nombre : "ID: {$otroCorte->corte_escuela_id}";
                            $fail('Las fechas se solapan con el corte "' . $nombreOtroCorte . '" ('.$inicioOtro->toDateString().' - '.$finOtro->toDateString().').');
                            return;
                        }
                    }
                }
            ],
            'corteEnEdicionData.porcentaje' => 'required|numeric|min:0|max:100',
        ];
    }

    protected function rules(): array // El método rules principal ahora delega
    {
        if ($this->showOffcanvas) {
            return $this->rulesForCorteEnEdicion();
        }
        return $this->rulesForStep1();
    }

    protected $messages = [
        // Paso 1
        'nombre.required' => 'El nombre del periodo es obligatorio.',
        'nombre.string' => 'El nombre debe ser texto.',
        'nombre.max' => 'El nombre no puede exceder los 200 caracteres.',
        'escuelaId.required' => 'Debes seleccionar una escuela.',
        'escuelaId.integer' => 'La escuela seleccionada no es válida.',
      
        'sistema_calificacion_id.required' => 'Debes seleccionar un sistema de calificación.',

       
        'fecha_inicio.required' => 'La fecha de inicio del periodo es obligatoria.',
        'fecha_inicio.date_format' => 'Formato inválido para fecha de inicio del periodo (AAAA-MM-DD).',
        'fecha_fin.required' => 'La fecha de fin del periodo es obligatoria.',
        'fecha_fin.date_format' => 'Formato inválido para fecha de fin del periodo (AAAA-MM-DD).',
        'fecha_fin.after_or_equal' => 'La fecha fin del periodo debe ser posterior o igual a su fecha inicio.',
        'fecha_limite_maestro.required' => 'La fecha límite para calificaciones es obligatoria.',

        'selectedSedes.required' => 'Debes seleccionar al menos una sede.',
        'selectedSedes.min' => 'Debes seleccionar al menos una sede.',
        'selectedSedes.*.required' => 'La selección de sedes no puede estar vacía.',
        'selectedSedes.*.integer' => 'Una de las sedes seleccionadas no es válida.',
      

        // Offcanvas (Corte en Edición)
        'corteEnEdicionData.fecha_inicio.required' => 'La fecha de inicio del corte es obligatoria.',
     
        'corteEnEdicionData.fecha_fin.required' => 'La fecha de fin del corte es obligatoria.',
    
        'corteEnEdicionData.fecha_fin.after_or_equal' => 'La fecha fin del corte debe ser posterior o igual a su fecha inicio.',
        'corteEnEdicionData.porcentaje.required' => 'El porcentaje del corte es obligatorio.',
        'corteEnEdicionData.porcentaje.numeric' => 'El porcentaje debe ser un número.',
        'corteEnEdicionData.porcentaje.min' => 'El porcentaje no puede ser negativo.',
        'corteEnEdicionData.porcentaje.max' => 'El porcentaje no puede ser mayor a 100.',
    ];

    public function updatedEscuelaId($value)
    {
        if ($value) {
            $escuela = Escuela::with('cortesEscuela')->find($value);
            $this->cortesEscuelaDisponibles = $escuela ? $escuela->cortesEscuela->sortBy('orden') : collect();
        } else {
            $this->cortesEscuelaDisponibles = collect();
        }
    }

    public function proceedToStep2OrCreatePeriodo()
    {
        $validatedDataStep1 = $this->validate($this->rulesForStep1());
        $isNewPeriodo = is_null($this->periodoId);

        DB::beginTransaction();
        try {
            if ($isNewPeriodo) {
                $this->periodoActual = new Periodo();
            } else {
                $this->periodoActual = Periodo::findOrFail($this->periodoId);
                // Si se actualiza y la escuela o fechas cambian drásticamente,
                // podrías querer eliminar y recrear cortes.
                // Por simplicidad, asumiremos que si se actualiza, los cortes se mantienen
                // y se ajustan manualmente si es necesario, o se recrean si la escuela cambia.
                if ($this->periodoActual->escuela_id != $validatedDataStep1['escuelaId']) {
                    CortePeriodo::where('periodo_id', $this->periodoActual->id)->delete();
                    // Forzar la recreación de cortes
                }
            }

            $this->periodoActual->nombre = $validatedDataStep1['nombre'];
            $this->periodoActual->escuela_id = $validatedDataStep1['escuelaId'];
            $this->periodoActual->sistema_calificaciones_id = $validatedDataStep1['sistema_calificacion_id'];
            $this->periodoActual->fecha_inicio = $validatedDataStep1['fecha_inicio'];
            $this->periodoActual->fecha_fin = $validatedDataStep1['fecha_fin'];
            $this->periodoActual->fecha_maxima_entrega_notas = $validatedDataStep1['fecha_limite_maestro'];
            $this->periodoActual->estado = true; // O un estado por defecto
            $this->periodoActual->save();

            $this->periodoId = $this->periodoActual->id; // Asegurar que periodoId está seteado

            // Sincronizar Sedes
            $this->periodoActual->sedes()->sync($validatedDataStep1['selectedSedes']);

            // Crear/Recrear Cortes solo si es nuevo o si se borraron por cambio de escuela
             if ($isNewPeriodo || ($this->periodoActual->escuela_id == $validatedDataStep1['escuelaId'] && $this->periodoActual->cortesPeriodo()->count() == 0) ) {
                 $this->updatedEscuelaId($this->periodoActual->escuela_id); // Carga cortesEscuelaDisponibles
                 if ($this->cortesEscuelaDisponibles->isNotEmpty()) {
                     $this->distributeCorteDatesAndCreate($this->periodoActual, $this->cortesEscuelaDisponibles);
                 } else {
                      DB::rollBack();
                      $this->addError('escuelaId', 'La escuela seleccionada no tiene cortes base definidos. No se pueden crear los cortes del periodo.');
                      return;
                 }
             }

            DB::commit();
            $this->loadCortesDelPeriodo();
            $this->currentStep = 2;
            $this->resetErrorBag(); // Limpiar errores del paso 1
            session()->flash('success', $isNewPeriodo ? 'Periodo creado exitosamente. Configure los cortes.' : 'Periodo actualizado. Verifique los cortes.');

        } catch (ValidationException $e) {
            DB::rollBack();
            // Los errores de validación ya están en el errorBag.
            // No es necesario hacer nada más aquí, Livewire los mostrará.
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en proceedToStep2OrCreatePeriodo: ' . $e->getMessage());
            session()->flash('error', 'Ocurrió un error inesperado: ' . $e->getMessage());
        }
    }

    private function distributeCorteDatesAndCreate(Periodo $periodo, Collection $cortesEscuela)
    {
        $countCortes = $cortesEscuela->count();
        if ($countCortes == 0) return;

        $fechaInicioPeriodo = Carbon::parse($periodo->fecha_inicio);
        $fechaFinPeriodo = Carbon::parse($periodo->fecha_fin);
        $duracionTotalPeriodo = $fechaInicioPeriodo->diffInDays($fechaFinPeriodo);

        // Asegurar que la duración no sea negativa o cero si las fechas son iguales
        $duracionTotalPeriodo = max(0, $duracionTotalPeriodo);


        $duracionPorCorte = $countCortes > 0 ? floor($duracionTotalPeriodo / $countCortes) : 0;
        $diasSobrantes = $countCortes > 0 ? $duracionTotalPeriodo % $countCortes : 0;

        $fechaInicioActualCorte = $fechaInicioPeriodo->copy();

        foreach ($cortesEscuela as $index => $corteEscuelaItem) {
            $fechaFinActualCorte = $fechaInicioActualCorte->copy()->addDays($duracionPorCorte -1);
             if ($index == $countCortes - 1) { // Último corte
                 $fechaFinActualCorte = $fechaFinPeriodo->copy(); // Asegura que el último corte termine en la fecha fin del periodo
             } else if ($index < $diasSobrantes) { // Distribuir días sobrantes
                 $fechaFinActualCorte->addDay();
             }


            // Asegurar que la fecha de fin no exceda la del periodo y no sea anterior a la de inicio
            if ($fechaFinActualCorte->gt($fechaFinPeriodo)) {
                $fechaFinActualCorte = $fechaFinPeriodo->copy();
            }
            if ($fechaFinActualCorte->lt($fechaInicioActualCorte)) {
                $fechaFinActualCorte = $fechaInicioActualCorte->copy(); // Mínimo la misma fecha de inicio
            }


            CortePeriodo::create([
                'periodo_id' => $periodo->id,
                'corte_escuela_id' => $corteEscuelaItem->id,
                'fecha_inicio' => $fechaInicioActualCorte->toDateString(),
                'fecha_fin' => $fechaFinActualCorte->toDateString(),
                'porcentaje' => $corteEscuelaItem->porcentaje, // Tomado del corte escuela
                'orden' => $corteEscuelaItem->orden, // Asumiendo que CortePeriodo tiene 'orden'
                'cerrado' => false,
            ]);
            $fechaInicioActualCorte = $fechaFinActualCorte->copy()->addDay();
        }
    }


    public function loadCortesDelPeriodo()
    {
        if ($this->periodoId) {
            $this->periodoActual = Periodo::with('cortesPeriodo.corteEscuela')->find($this->periodoId); // Cargar relación para nombre
            if($this->periodoActual){
                $this->cortesDelPeriodo = $this->periodoActual->cortesPeriodo->sortBy('orden')->map(function ($corte) {
                    return [
                        'id' => $corte->id,
                        'nombre_display' => $corte->corteEscuela ? $corte->corteEscuela->nombre : 'N/A',
                        'fecha_inicio' => $corte->fecha_inicio,
                        'fecha_fin' => $corte->fecha_fin,
                        'porcentaje' => $corte->porcentaje,
                        'orden' => $corte->corteEscuela ? $corte->corteEscuela->orden: $corte->orden, // Asumir un campo orden en corte_periodo o tomarlo del corte_escuela
                    ];
                })->values()->all();
            } else {
                $this->cortesDelPeriodo = [];
            }
        } else {
            $this->cortesDelPeriodo = [];
        }
    }

    public function editCorte(int $cortePeriodoId)
    {
        $corteAEditar = CortePeriodo::with('corteEscuela')->find($cortePeriodoId);
        if ($corteAEditar && $corteAEditar->periodo_id == $this->periodoId) {
            $this->corteEnEdicionId = $corteAEditar->id;
            $this->corteEnEdicionData = [
                'nombre_display' => $corteAEditar->corteEscuela ? $corteAEditar->corteEscuela->nombre : 'N/A',
                'fecha_inicio' => $corteAEditar->fecha_inicio,
                'fecha_fin' => $corteAEditar->fecha_fin,
                'porcentaje' => $corteAEditar->porcentaje,
            ];
            $this->resetErrorBag();
          
            $this->dispatch('abrirOffcanvas',nombreModal:'offcanvasEditarCorte'); // <--- Evento para JS
        }
    }
    public function clearCorteEnEdicion()
    {
        $this->corteEnEdicionId = null;
        $this->corteEnEdicionData = ['nombre_display' => '', 'fecha_inicio' => '', 'fecha_fin' => '', 'porcentaje' => 0];
        $this->resetErrorBag();
    }

    public function saveCorteEditado()
    {
        $validatedCorteData = $this->validate($this->rulesForCorteEnEdicion());

        // Validación de suma de porcentajes
        $sumaTotalPorcentajes = 0;
        $cortesParaSuma = CortePeriodo::where('periodo_id', $this->periodoId)->get();
        foreach ($cortesParaSuma as $corte) {
            if ($corte->id == $this->corteEnEdicionId) {
                $sumaTotalPorcentajes += (float) $this->corteEnEdicionData['porcentaje'];
            } else {
                $sumaTotalPorcentajes += (float) $corte->porcentaje;
            }
        }

        if (round($sumaTotalPorcentajes, 2) > 100.00) {
            throw ValidationException::withMessages([
                'corteEnEdicionData.porcentaje' => 'La suma de los porcentajes de todos los cortes debe ser 100%. Suma actual: ' . $sumaTotalPorcentajes . '%.'
            ]);
        }

        DB::beginTransaction();
        try {
            $corteAActualizar = CortePeriodo::find($this->corteEnEdicionId);
            if ($corteAActualizar) {
                $corteAActualizar->update([
                    'fecha_inicio' => $this->corteEnEdicionData['fecha_inicio'],
                    'fecha_fin' => $this->corteEnEdicionData['fecha_fin'],
                    'porcentaje' => $this->corteEnEdicionData['porcentaje'],
                ]);
            }
            DB::commit();
            session()->flash('success_corte', 'Corte actualizado exitosamente.');
            $this->dispatch('cerrarOffcanvas', nombreModal:'offcanvasEditarCorte');

            $this->loadCortesDelPeriodo(); // Recargar la lista
            session()->flash('success', 'Corte actualizado exitosamente.');

        } catch (ValidationException $e){
            DB::rollBack(); // Importante si la validación de suma de porcentajes lanza excepción
            // El error se propaga y Livewire lo maneja
        }
        catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al guardar corte editado: ' . $e->getMessage());
            $this->addError('corteEnEdicionData.general', 'Error al guardar el corte: ' . $e->getMessage());
        }
    }

    public function closeOffcanvas()
    {
        $this->showOffcanvas = false;
        $this->corteEnEdicionId = null;
        $this->corteEnEdicionData = ['nombre_display' => '', 'fecha_inicio' => '', 'fecha_fin' => '', 'porcentaje' => 0];
        $this->resetErrorBag();
    }

    public function previousStep()
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
            $this->closeOffcanvas(); // Asegurar que se cierre el offcanvas
            // Si volvemos al paso 1 y ya hay un periodo, cargamos sus datos
            if($this->periodoId){
                $this->loadPeriodoExistente($this->periodoId);
            }
        }
    }
    
    public function loadPeriodoExistente(int $periodoId)
    {
        $periodo = Periodo::with('sedes')->find($periodoId);
        if ($periodo) {
            $this->periodoId = $periodo->id;
            $this->periodoActual = $periodo;
            $this->nombre = $periodo->nombre;
            $this->escuelaId = $periodo->escuela_id;
            $this->sistema_calificacion_id = $periodo->sistema_calificaciones_id;
            $this->fecha_inicio = $periodo->fecha_inicio;
            $this->fecha_fin = $periodo->fecha_fin;
            $this->fecha_limite_maestro = $periodo->fecha_maxima_entrega_notas;
            $this->selectedSedes = $periodo->sedes->pluck('id')->toArray();
            
            $this->updatedEscuelaId($this->escuelaId); // Cargar cortes de escuela
            $this->loadCortesDelPeriodo(); // Cargar cortes del periodo para el paso 2
        }
    }


    public function finalizeConfiguration()
    {
        
        session()->flash('success', 'Configuración del periodo finalizada.');
        return redirect()->route('periodo.actualizar',$this->periodoId); // Asegúrate que esta ruta exista
    }

    public function render()
    {
        // Asegurar que los cortes del periodo estén cargados si estamos en el paso 2 y hay un periodoId
        if ($this->currentStep == 2 && $this->periodoId && empty($this->cortesDelPeriodo)) {
            $this->loadCortesDelPeriodo();
        }

        return view('livewire.escuelas.nuevo-periodo', [
          
        ]);
    }
}