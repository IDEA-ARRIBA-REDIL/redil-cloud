<?php

namespace App\Livewire\Escuelas; // O el namespace correcto que estés usando, ej: App\Http\Livewire\Escuelas

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\MateriaPeriodo as ModeloMateriaPeriodo;
use App\Models\HorarioBase;
use App\Models\HorarioMateriaPeriodo as ModeloHorarioMateriaPeriodo;
use App\Models\Aula;
use App\Models\Sede;
use App\Models\TipoAula;
use App\Models\CortePeriodo;
use App\Models\Periodo;
use App\Models\ItemPlantilla;
use App\Models\ItemCorteMateriaPeriodo;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection as EloquentCollection; // Para tipar explícitamente

class HorariosMateriaPeriodo extends Component
{
    use WithPagination;

    public ModeloMateriaPeriodo $materiaPeriodo; // Instancia del modelo MateriaPeriodo actual
    public $sedes; // Colección de todas las Sede
    public $tiposAula; // Colección de todos los TipoAula

    // Para el formulario de NUEVO HorarioMateriaPeriodo
    public $sede_id_formulario = '';         // Sede seleccionada
    public $aula_id_formulario = '';         // Aula seleccionada
    public EloquentCollection $aulas_formulario; // Aulas disponibles para la sede seleccionada
    public EloquentCollection $horarios_base_formulario; // HorariosBase disponibles para el aula y materia
    public $horario_base_id_seleccionado = ''; // HorarioBase ID seleccionado

    // Campos específicos del HorarioMateriaPeriodo que se está creando o editando (capacidad)
    public $hmp_habilitado = true;
    public $hmp_fecha_inicio_habilitado = '';
    public $hmp_fecha_fin_habilitado = '';
    public $hmp_capacidad = 0;
    public $hmp_capacidad_limite = 0;
    public $hmp_ampliar_cupos_limite = false;
    public $hmp_cupos_disponibles_calculado = 0; // Para mostrar, no para guardar directamente

    // Para el formulario de EDICIÓN (solo capacidad)
    public ?ModeloHorarioMateriaPeriodo $horarioMPEditando = null; // Instancia del HMP que se edita

    // Propiedades para mostrar información del HorarioBase en el formulario de edición (no son editables)
    public $display_hb_dia = '';
    public $display_hb_hora_inicio = '';
    public $display_hb_hora_fin = '';
    public $display_hb_aula_nombre = '';
    public $display_hb_sede_nombre = '';
    public $display_hb_capacidad_base = 0;

    // Filtros para la lista principal
    public $tipoAulaFiltro = '';
    public $sedeFiltro = '';

    public function getHayFiltrosProperty()
    {
        return $this->sedeFiltro || $this->tipoAulaFiltro;
    }

    protected $listeners = [
        'resetearFormularioHorarioMP' => 'resetearFormulario',
        // 'filtrosActualizadosHorarioMP' => '$refresh' // $refresh es un método mágico de Livewire
    ];

    protected function rules()
    {
        if ($this->horarioMPEditando) { // Reglas para Editar (solo capacidad del HMP)
            return [
                'hmp_capacidad' => 'required|integer|min:0',
                'hmp_capacidad_limite' => 'required|integer|min:0|gte:hmp_capacidad',
                'hmp_ampliar_cupos_limite' => 'required|boolean',
            ];
        }
        // Reglas para Crear un nuevo HorarioMateriaPeriodo
        return [
            'sede_id_formulario' => 'required|exists:sedes,id',
            'aula_id_formulario' => 'required|exists:aulas,id',
            'horario_base_id_seleccionado' => 'required|exists:horarios_base,id',
            'hmp_habilitado' => 'required|boolean',
            'hmp_fecha_inicio_habilitado' => 'nullable|date_format:Y-m-d',
            'hmp_fecha_fin_habilitado' => 'nullable|date_format:Y-m-d|after_or_equal:hmp_fecha_inicio_habilitado',
            'hmp_capacidad' => 'required|integer|min:0',
            'hmp_capacidad_limite' => 'required|integer|min:0|gte:hmp_capacidad',
            'hmp_ampliar_cupos_limite' => 'required|boolean',
        ];
    }

    protected $messages = [
        'sede_id_formulario.required' => 'Debes seleccionar una sede.',
        'aula_id_formulario.required' => 'Debes seleccionar un aula.',
        'horario_base_id_seleccionado.required' => 'Debes seleccionar un horario base.',
        'horario_base_id_seleccionado.exists' => 'El horario base seleccionado no es válido o ya está asignado.',
        'hmp_capacidad.required' => 'La capacidad es obligatoria.',
        'hmp_capacidad.integer' => 'La capacidad debe ser un número.',
        'hmp_capacidad.min' => 'La capacidad no puede ser negativa.',
        'hmp_capacidad_limite.required' => 'La capacidad límite es obligatoria.',
        'hmp_capacidad_limite.gte' => 'La capacidad límite debe ser mayor o igual a la capacidad.',
        'hmp_fecha_fin_habilitado.after_or_equal' => 'La fecha fin debe ser posterior o igual a la fecha de inicio.',
    ];

    public function mount(ModeloMateriaPeriodo $materiaPeriodo)
    {
      // Cargar las sedes asociadas al PERIODO de esta MateriaPeriodo
        if ($this->materiaPeriodo->periodo) {
            $this->sedes = $this->materiaPeriodo->periodo->sedes()->orderBy('nombre')->get();
        } else {
            $this->sedes = new EloquentCollection(); // Vacío si no hay periodo
            Log::warning("MateriaPeriodo ID {$this->materiaPeriodo->id} no tiene un periodo asociado para cargar sedes.");
        }
        $this->tiposAula = TipoAula::orderBy('nombre')->get();
        $this->aulas_formulario = new EloquentCollection();
        $this->horarios_base_formulario = new EloquentCollection();
        $this->inicializarFechasHabilitacion();
    }

    protected function inicializarFechasHabilitacion()
    {
        // Inicializar fechas con las del periodo si están disponibles
        if ($this->materiaPeriodo->periodo) {
            $this->hmp_fecha_inicio_habilitado = $this->materiaPeriodo->periodo->fecha_inicio ? Carbon::parse($this->materiaPeriodo->periodo->fecha_inicio)->format('Y-m-d') : '';
            $this->hmp_fecha_fin_habilitado = $this->materiaPeriodo->periodo->fecha_fin ? Carbon::parse($this->materiaPeriodo->periodo->fecha_fin)->format('Y-m-d') : '';
        } else {
            $this->hmp_fecha_inicio_habilitado = '';
            $this->hmp_fecha_fin_habilitado = '';
        }
    }


    public function updatedSedeIdFormulario($sedeId)
    {
        if (!empty($sedeId)) {
            $this->aulas_formulario = Aula::where('sede_id', $sedeId)->orderBy('nombre')->get();
        } else {
            $this->aulas_formulario = new EloquentCollection();
        }
        $this->aula_id_formulario = ''; // Resetear aula
        $this->horarios_base_formulario = new EloquentCollection();
        $this->horario_base_id_seleccionado = ''; // Resetear horario base
    }

    public function updatedAulaIdFormulario($aulaId)
    {
        if (!empty($aulaId) && $this->materiaPeriodo->materia_id) {
            $horariosBaseYaAsignadosIds = ModeloHorarioMateriaPeriodo::where('materia_periodo_id', $this->materiaPeriodo->id)
                ->pluck('horario_base_id')->toArray();

            $this->horarios_base_formulario = HorarioBase::where('materia_id', $this->materiaPeriodo->materia_id)
                ->where('aula_id', $aulaId)
                ->where('activo', true)
                ->whereNotIn('id', $horariosBaseYaAsignadosIds)
                ->with(['aula.sede']) // Eager load para display_info
                ->get()
                ->map(function ($hb) {
                    $diaSemana = $hb->dia_semana ?? "Día {$hb->dia}";
                    $horaInicio = $hb->hora_inicio_formato ?? $hb->hora_inicio;
                    $horaFin = $hb->hora_fin_formato ?? $hb->hora_fin;
                    $hb->display_info = "{$diaSemana} {$horaInicio}-{$horaFin} | Cap.Base: {$hb->capacidad}";
                    return $hb;
                });
        } else {
            $this->horarios_base_formulario = new EloquentCollection();
        }
        $this->horario_base_id_seleccionado = ''; // Resetear horario base
    }

    /**
     * Cuando se selecciona un HorarioBase, actualiza los campos de capacidad en el formulario.
     */
    public function updatedHorarioBaseIdSeleccionado($horarioBaseId)
    {
        if (!empty($horarioBaseId)) {
            $horarioBase = HorarioBase::find($horarioBaseId);
            if ($horarioBase) {
                $this->hmp_capacidad = $horarioBase->capacidad;
                $this->hmp_capacidad_limite = $horarioBase->capacidad_limite > $horarioBase->capacidad ? $horarioBase->capacidad_limite : $horarioBase->capacidad + 10; // Default si es menor
            }
        } else {
             $this->hmp_capacidad = 0;
             $this->hmp_capacidad_limite = 0;
        }
    }


    public function abrirFiltros()
    {
        $this->dispatch('abrirOffcanvas', nombreModal: 'offcanvasFiltrosHorarioMP');
    }

    public function aplicarFiltros()
    {
        $this->dispatch('cerrarOffcanvas', nombreModal: 'offcanvasFiltrosHorarioMP');
        $this->resetPage(); // Reinicia la paginación de Livewire
    }

    public function getTagsBusquedaProperty()
    {
        $tags = [];

        if ($this->sedeFiltro) {
            $sede = $this->sedes->firstWhere('id', $this->sedeFiltro);
            if ($sede) {
                $tags[] = [
                    'label' => 'Sede: ' . $sede->nombre,
                    'field' => 'sedeFiltro',
                    'value' => $this->sedeFiltro
                ];
            }
        }

        if ($this->tipoAulaFiltro) {
            $tipo = $this->tiposAula->firstWhere('id', $this->tipoAulaFiltro);
            if ($tipo) {
                $tags[] = [
                    'label' => 'Tipo Aula: ' . $tipo->nombre,
                    'field' => 'tipoAulaFiltro',
                    'value' => $this->tipoAulaFiltro
                ];
            }
        }

        return $tags;
    }

    public function removerFiltro($field)
    {
        if (property_exists($this, $field)) {
            $this->$field = '';
            $this->resetPage();
        }
    }

    public function resetFiltros()
    {
        $this->reset(['tipoAulaFiltro', 'sedeFiltro']);
        $this->dispatch('cerrarOffcanvas', nombreModal: 'offcanvasFiltrosHorarioMP');
        $this->resetPage();
    }

    public function guardarHorario()
    {
        $this->validate($this->rules()); // Validar según las reglas (creación o edición)

        DB::beginTransaction();
        try {
            $existente = ModeloHorarioMateriaPeriodo::where('materia_periodo_id', $this->materiaPeriodo->id)
                ->where('horario_base_id', $this->horario_base_id_seleccionado)
                ->first();

            if ($existente) {
                DB::rollBack();
                session()->flash('mensaje_error_hmp', 'Este horario base ya está asignado a esta materia en este período.');
                // $this->addError('horario_base_id_seleccionado', 'Este horario base ya está asignado.'); // Para mostrar error en campo
                return;
            }

            $horarioMP = new ModeloHorarioMateriaPeriodo();
            $horarioMP->materia_periodo_id = $this->materiaPeriodo->id;
            $horarioMP->horario_base_id = $this->horario_base_id_seleccionado;
            $horarioMP->habilitado = $this->hmp_habilitado;
            $horarioMP->fecha_inicio_habilitado = $this->hmp_fecha_inicio_habilitado ?: null;
            $horarioMP->fecha_fin_habilitado = $this->hmp_fecha_fin_habilitado ?: null;
            $horarioMP->capacidad = $this->hmp_capacidad;
            $horarioMP->capacidad_limite = $this->hmp_capacidad_limite;
            $horarioMP->ampliar_cupos_limite = $this->hmp_ampliar_cupos_limite;
            $horarioMP->cupos_disponibles = $this->hmp_ampliar_cupos_limite ? $this->hmp_capacidad_limite : $this->hmp_capacidad;
            $horarioMP->save();

            // --- LÓGICA PARA DUPLICAR ITEMS ---
            $this->duplicarItemsParaHorario($horarioMP);
            // --- FIN LÓGICA PARA DUPLICAR ITEMS ---

            DB::commit();
            $this->dispatch('cerrarOffcanvas', nombreModal: 'offcanvasNuevoHorarioMP');
            session()->flash('mensaje_exito_hmp', 'Horario vinculado e ítems de calificación generados correctamente.');
            $this->resetearFormulario();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al guardar HorarioMateriaPeriodo y/o duplicar ítems: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine());
            session()->flash('mensaje_error_hmp', 'No se pudo vincular el horario. Verifique los datos o contacte soporte. Error: ' . substr($e->getMessage(), 0, 100) . '...');
        }
    }

    /**
     * Duplica los ItemPlantilla como ItemCorteMateriaPeriodo para un HorarioMateriaPeriodo dado.
     */
    protected function duplicarItemsParaHorario(ModeloHorarioMateriaPeriodo $horarioMP)
    {
        $materiaOriginalId = $this->materiaPeriodo->materia_id;
        $periodoActualId = $this->materiaPeriodo->periodo_id;
        // $escuelaId = $this->materiaPeriodo->materia->escuela_id; // Ya cargado en mount

        if (!$this->materiaPeriodo->materia || !$this->materiaPeriodo->materia->escuela_id) {
            Log::error("No se pudo determinar la escuela para duplicar ítems. HMP ID: {$horarioMP->id}");
            throw new \Exception("Falta información de la escuela para duplicar ítems.");
        }
        $escuelaId = $this->materiaPeriodo->materia->escuela_id;

        $cortesDelPeriodo = CortePeriodo::where('periodo_id', $periodoActualId)
            ->whereHas('corteEscuela', function ($query) use ($escuelaId) { // Asegurar que el corte plantilla sea de la misma escuela
                $query->where('escuela_id', $escuelaId);
            })
            ->with('corteEscuela:id,orden') // Necesitamos el corte_escuela_id
            ->get();

        if ($cortesDelPeriodo->isEmpty()) {
            Log::warning("No se encontraron CortePeriodo para el Periodo ID {$periodoActualId} (Escuela ID: {$escuelaId}) para duplicar ítems para HMP ID {$horarioMP->id}.");
            return; // No es un error fatal, simplemente no hay cortes para procesar.
        }

        $itemsDuplicadosCount = 0;
        foreach ($cortesDelPeriodo as $cortePeriodo) {
            $itemPlantillas = ItemPlantilla::where('materia_id', $materiaOriginalId)
                ->where('corte_escuela_id', $cortePeriodo->corte_escuela_id)
                ->get();

            foreach ($itemPlantillas as $plantilla) {
                ItemCorteMateriaPeriodo::create([
                    'materia_periodo_id' => $this->materiaPeriodo->id,
                    'corte_periodo_id' => $cortePeriodo->id,
                    'item_plantilla_id' => $plantilla->id,
                    'tipo_item_id' => $plantilla->tipo_item_id,
                    'horario_materia_periodo_id' => $horarioMP->id, // ¡Vincular al nuevo HMP!
                    'nombre' => $plantilla->nombre,
                    'contenido' => $plantilla->contenido,
                    'visible' => $plantilla->visible_predeterminado,
                    'fecha_inicio' => $cortePeriodo->fecha_inicio,
                    'fecha_fin' => $cortePeriodo->fecha_fin,
                    'habilitar_entregable' => $plantilla->entregable_predeterminado,
                    'porcentaje' => $plantilla->porcentaje_sugerido,
                    'orden' => $plantilla->orden,
                ]);
                $itemsDuplicadosCount++;
            }
        }
        Log::info("Total de {$itemsDuplicadosCount} ítems duplicados para HMP ID {$horarioMP->id}.");
    }


    public function abrirFormularioEditar($horarioMPId)
    {
        $this->resetErrorBag();
        $this->horarioMPEditando = ModeloHorarioMateriaPeriodo::find($horarioMPId);

        if (!$this->horarioMPEditando) {
            session()->flash('mensaje_error_hmp', 'Error: No se encontró el horario del período para editar.');
            $this->dispatch('cerrarOffcanvas', nombreModal: 'offcanvasEditarHorarioMP');
            return;
        }

        $horarioBase = $this->horarioMPEditando->horarioBase()->with('aula.sede')->first();

        if ($horarioBase) {
            $this->display_hb_dia = $horarioBase->dia_semana ?? "Día {$horarioBase->dia}";
            $this->display_hb_hora_inicio = $horarioBase->hora_inicio_formato ?? $horarioBase->hora_inicio;
            $this->display_hb_hora_fin = $horarioBase->hora_fin_formato ?? $horarioBase->hora_fin;
            $this->display_hb_aula_nombre = $horarioBase->aula?->nombre ?? 'N/A'; // Null safe operator
            $this->display_hb_sede_nombre = $horarioBase->aula?->sede?->nombre ?? 'N/A'; // Null safe operator
            $this->display_hb_capacidad_base = $horarioBase->capacidad;
        } else {
            // Manejar el caso en que el horario base no exista (debería ser raro)
            session()->flash('mensaje_error_hmp', 'Advertencia: El horario base asociado a esta configuración no fue encontrado.');
            $this->display_hb_dia = 'N/A'; /* ... y así para los demás ... */
        }

        $this->hmp_capacidad = $this->horarioMPEditando->capacidad;
        $this->hmp_capacidad_limite = $this->horarioMPEditando->capacidad_limite;
        $this->hmp_ampliar_cupos_limite = (bool) $this->horarioMPEditando->ampliar_cupos_limite; // Castear a booleano

        $this->dispatch('abrirOffcanvas', nombreModal: 'offcanvasEditarHorarioMP');
    }

    public function actualizarHorario()
    {
        if (!$this->horarioMPEditando) {
            session()->flash('mensaje_error_hmp', 'No hay un horario seleccionado para actualizar.');
            return;
        }
        // Validar solo los campos de capacidad para la edición
        $this->validate([
            'hmp_capacidad' => 'required|integer|min:0',
            'hmp_capacidad_limite' => 'required|integer|min:0|gte:hmp_capacidad',
            'hmp_ampliar_cupos_limite' => 'required|boolean',
        ]);

        DB::beginTransaction();
        try {
            $this->horarioMPEditando->capacidad = $this->hmp_capacidad;
            $this->horarioMPEditando->capacidad_limite = $this->hmp_capacidad_limite;
            $this->horarioMPEditando->ampliar_cupos_limite = $this->hmp_ampliar_cupos_limite;
            $this->horarioMPEditando->cupos_disponibles = $this->hmp_ampliar_cupos_limite ? $this->hmp_capacidad_limite : $this->hmp_capacidad;
            // Aquí podrías añadir lógica para ajustar cupos_disponibles si ya hay matriculados.
            $this->horarioMPEditando->save();
            DB::commit();

            $this->resetearFormulario();
            $this->dispatch('cerrarOffcanvas', nombreModal: 'offcanvasEditarHorarioMP');
            session()->flash('mensaje_exito_hmp', 'Capacidades del horario actualizadas correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al actualizar HorarioMateriaPeriodo ID {$this->horarioMPEditando->id}: " . $e->getMessage());
            session()->flash('mensaje_error_hmp', 'No se pudo actualizar el horario. Error: ' . $e->getMessage());
        }
    }

    public function abrirFormularioNuevo()
    {
        $this->resetearFormulario();
        $this->inicializarFechasHabilitacion(); // Asegurar que las fechas se inicialicen para el nuevo formulario
        $this->horarioMPEditando = null;
        $this->dispatch('abrirOffcanvas', nombreModal: 'offcanvasNuevoHorarioMP');
    }

    public function confirmarEliminarHorarioMP($horarioMPId)
    {
        $horarioMP = ModeloHorarioMateriaPeriodo::find($horarioMPId);
        if ($horarioMP) {
            // Aquí podrías disparar un SweetAlert de confirmación en el frontend
            // y luego llamar a un método como `procesarEliminacion($horarioMPId)`
            // Por ahora, llama directamente a eliminar.
            $this->eliminarHorarioMP($horarioMP);
        } else {
            session()->flash('mensaje_error_hmp', 'No se encontró el horario para eliminar.');
        }
    }

    public function eliminarHorarioMP(ModeloHorarioMateriaPeriodo $horarioMP)
    {
        DB::beginTransaction();
        try {
            // Opcional: Antes de eliminar el HorarioMateriaPeriodo,
            // podrías necesitar eliminar las instancias de ItemCorteMateriaPeriodo asociadas,
            // especialmente si no tienes `onDelete('cascade')` en la FK de esa tabla.
            // ItemCorteMateriaPeriodo::where('horario_materia_periodo_id', $horarioMP->id)->delete();
            // Lo mismo para AlumnoRespuestaItem y otras tablas dependientes.

            $horarioMP->delete();
            DB::commit();
            session()->flash('mensaje_exito_hmp', 'Horario del período desvinculado (y sus ítems de calificación si estaban configurados para borrado en cascada).');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al eliminar HorarioMateriaPeriodo ID {$horarioMP->id}: " . $e->getMessage());
            session()->flash('mensaje_error_hmp', 'No se pudo desvincular el horario del período.');
        }
    }

    public function toggleHabilitado($horarioMPId)
    {
        $horarioMP = ModeloHorarioMateriaPeriodo::find($horarioMPId);
        if ($horarioMP) {
            try {
                $horarioMP->habilitado = !$horarioMP->habilitado;
                $horarioMP->save();
                $estado = $horarioMP->habilitado ? 'habilitado' : 'deshabilitado';
                session()->flash('mensaje_exito_hmp', "Horario del período {$estado} correctamente.");
            } catch (\Exception $e) {
                Log::error("Error al cambiar estado de HMP ID {$horarioMP->id}: " . $e->getMessage());
                session()->flash('mensaje_error_hmp', 'No se pudo cambiar el estado del horario.');
            }
        } else {
            session()->flash('mensaje_error_hmp', 'No se encontró el horario para cambiar estado.');
        }
    }

    public function resetearFormulario()
    {
        $this->resetValidation(); // También resetea los errores de validación
        $this->reset([
            'sede_id_formulario', 'aula_id_formulario', 'horario_base_id_seleccionado',
            'hmp_habilitado', 'hmp_fecha_inicio_habilitado', 'hmp_fecha_fin_habilitado',
            'hmp_capacidad', 'hmp_capacidad_limite', 'hmp_ampliar_cupos_limite',
            'display_hb_dia', 'display_hb_hora_inicio', 'display_hb_hora_fin',
            'display_hb_aula_nombre', 'display_hb_sede_nombre', 'display_hb_capacidad_base'
        ]);
        $this->hmp_habilitado = true;
        $this->hmp_ampliar_cupos_limite = false;
        $this->inicializarFechasHabilitacion(); // Reinicializar fechas
        $this->hmp_capacidad = 0; // O un valor por defecto más apropiado
        $this->hmp_capacidad_limite = 0;

        $this->horarioMPEditando = null;
        $this->aulas_formulario = new EloquentCollection();
        $this->horarios_base_formulario = new EloquentCollection();
    }

    public function cancelar()
    {
        $nombreOffcanvas = $this->horarioMPEditando ? 'offcanvasEditarHorarioMP' : 'offcanvasNuevoHorarioMP';
        $this->resetearFormulario(); // Llama a resetear antes de cerrar para limpiar el estado.
        $this->dispatch('cerrarOffcanvas', nombreModal: $nombreOffcanvas);
    }

    public function render()
    {
        $query = ModeloHorarioMateriaPeriodo::where('materia_periodo_id', $this->materiaPeriodo->id)
            ->with([
                'horarioBase.aula.tipo', // 'tipo' es la relación en Aula hacia TipoAula
                'horarioBase.aula.sede',
                // 'horarioBase.materia' // Ya tienes la materia a través de $this->materiaPeriodo->materia
            ]);

        if ($this->sedeFiltro) {
            $query->whereHas('horarioBase.aula', function($q) {
                $q->where('sede_id', $this->sedeFiltro);
            });
        }
        if ($this->tipoAulaFiltro) {
            $query->whereHas('horarioBase.aula', function($q) {
                $q->where('tipo_aula_id', $this->tipoAulaFiltro); // Asumiendo que Aula tiene tipo_aula_id
            });
        }

        $horariosMP = $query->select('horarios_materia_periodo.*')
            // Es mejor ordenar por campos de la tabla principal o de joins explícitos
            // Si HorarioBase ya tiene día y hora, no necesitas un join adicional si ya está en la relación
            ->join('horarios_base', 'horarios_materia_periodo.horario_base_id', '=', 'horarios_base.id')
            ->orderBy('horarios_base.dia', 'asc')
            ->orderBy('horarios_base.hora_inicio', 'asc')
            ->paginate(10);

        return view('livewire.escuelas.horarios-materia-periodo', [
            'horariosMP' => $horariosMP,
            'sedesFiltroLista' => $this->sedes,
            'tiposAulaFiltroLista' => $this->tiposAula
        ]);
    }
}
