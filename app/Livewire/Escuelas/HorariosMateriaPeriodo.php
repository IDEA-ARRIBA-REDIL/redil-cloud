<?php

namespace App\Livewire\Escuelas;

use App\Models\Aula;
use App\Models\CortePeriodo;
use App\Models\HorarioBase;
use App\Models\HorarioMateriaPeriodo as ModeloHorarioMateriaPeriodo;
use App\Models\ItemCorteMateriaPeriodo;
use App\Models\ItemPlantilla;
use App\Models\MateriaPeriodo as ModeloMateriaPeriodo;
use App\Models\Matricula;
use App\Models\MatriculaHorarioMateriaPeriodo;
use App\Models\Sede;
use App\Models\TipoAula;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;

class HorariosMateriaPeriodo extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public ModeloMateriaPeriodo $materiaPeriodo; // Instancia del modelo MateriaPeriodo actual

    public $sedes; // Colección de todas las Sede

    public $tiposAula; // Colección de todos los TipoAula

    // Para el formulario de NUEVO HorarioMateriaPeriodo
    public $sede_id_formulario = '';         // Sede seleccionada

    public $aula_id_formulario = '';         // Aula seleccionada

    public EloquentCollection $aulas_formulario; // Aulas disponibles para la sede seleccionada

    public EloquentCollection $horarios_base_formulario; // HorariosBase disponibles para el aula y materia

    public $horario_base_id_seleccionado = ''; // Compatibilidad legado

    public array $horarios_base_seleccionados = []; // Selección múltiple de HorarioBase IDs

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

        // Reglas para Crear uno o varios HorarioMateriaPeriodo
        return [
            'sede_id_formulario' => 'required|exists:sedes,id',
            'aula_id_formulario' => 'required|exists:aulas,id',
            'horarios_base_seleccionados' => 'required|array|min:1',
            'horarios_base_seleccionados.*' => 'exists:horarios_base,id',
            'hmp_habilitado' => 'required|boolean',
            'hmp_fecha_inicio_habilitado' => 'nullable|date_format:Y-m-d',
            'hmp_fecha_fin_habilitado' => 'nullable|date_format:Y-m-d|after_or_equal:hmp_fecha_inicio_habilitado',
            'hmp_ampliar_cupos_limite' => 'required|boolean',
        ];
    }

    protected $messages = [
        'sede_id_formulario.required' => 'Debes seleccionar una sede.',
        'aula_id_formulario.required' => 'Debes seleccionar un aula.',
        'horarios_base_seleccionados.required' => 'Debes seleccionar al menos un horario base.',
        'horarios_base_seleccionados.min' => 'Debes seleccionar al menos un horario base.',
        'horarios_base_seleccionados.*.exists' => 'Uno de los horarios base seleccionados no es válido.',
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
            $this->sedes = new EloquentCollection; // Vacío si no hay periodo
            Log::warning("MateriaPeriodo ID {$this->materiaPeriodo->id} no tiene un periodo asociado para cargar sedes.");
        }
        $this->tiposAula = TipoAula::orderBy('nombre')->get();
        $this->aulas_formulario = new EloquentCollection;
        $this->horarios_base_formulario = new EloquentCollection;
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
        if (! empty($sedeId)) {
            $this->aulas_formulario = Aula::where('sede_id', $sedeId)->orderBy('nombre')->get();
        } else {
            $this->aulas_formulario = new EloquentCollection;
        }
        $this->aula_id_formulario = ''; // Resetear aula
        $this->horarios_base_formulario = new EloquentCollection;
        $this->horario_base_id_seleccionado = '';
        $this->horarios_base_seleccionados = [];
    }

    public function updatedAulaIdFormulario($aulaId)
    {
        if (! empty($aulaId) && $this->materiaPeriodo->materia_id) {
            $horariosBaseYaAsignadosIds = ModeloHorarioMateriaPeriodo::where('materia_periodo_id', $this->materiaPeriodo->id)
                ->pluck('horario_base_id')->toArray();

            $this->horarios_base_formulario = HorarioBase::where('materia_id', $this->materiaPeriodo->materia_id)
                ->where('aula_id', $aulaId)
                ->where('activo', true)
                ->whereNotIn('id', $horariosBaseYaAsignadosIds)
                ->with(['aula.sede']) // Eager load para display_info
                ->orderBy('dia')
                ->orderBy('hora_inicio')
                ->get()
                ->map(function ($hb) {
                    $diaSemana = $hb->dia_semana ?? "Día {$hb->dia}";
                    $horaInicio = $hb->hora_inicio_formato ?? $hb->hora_inicio;
                    $horaFin = $hb->hora_fin_formato ?? $hb->hora_fin;
                    $hb->display_info = "{$diaSemana} {$horaInicio}-{$horaFin} | Cap.Base: {$hb->capacidad}";

                    return $hb;
                });
        } else {
            $this->horarios_base_formulario = new EloquentCollection;
        }
        $this->horario_base_id_seleccionado = '';
        $this->horarios_base_seleccionados = [];
    }

    /**
     * Alterna la selección de todos los horarios base disponibles para el aula actual.
     */
    public function toggleSeleccionarTodosHorariosBase(): void
    {
        $todosIds = $this->horarios_base_formulario
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->toArray();

        $seleccionadosActuales = array_map('strval', $this->horarios_base_seleccionados);

        if (count($todosIds) > 0 && count($seleccionadosActuales) === count($todosIds)) {
            $this->horarios_base_seleccionados = [];
        } else {
            $this->horarios_base_seleccionados = $todosIds;
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
                    'label' => 'Sede: '.$sede->nombre,
                    'field' => 'sedeFiltro',
                    'value' => $this->sedeFiltro,
                ];
            }
        }

        if ($this->tipoAulaFiltro) {
            $tipo = $this->tiposAula->firstWhere('id', $this->tipoAulaFiltro);
            if ($tipo) {
                $tags[] = [
                    'label' => 'Tipo Aula: '.$tipo->nombre,
                    'field' => 'tipoAulaFiltro',
                    'value' => $this->tipoAulaFiltro,
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
        $this->validate($this->rules());

        $idsSeleccionados = array_values(array_unique(array_filter($this->horarios_base_seleccionados)));
        if (empty($idsSeleccionados)) {
            $this->addError('horarios_base_seleccionados', 'Debes seleccionar al menos un horario base.');

            return;
        }

        DB::beginTransaction();
        try {
            $creadosCount = 0;

            foreach ($idsSeleccionados as $horarioBaseId) {
                // 1. Verificar que no esté ya vinculado a esta materia en este período
                $existente = ModeloHorarioMateriaPeriodo::where('materia_periodo_id', $this->materiaPeriodo->id)
                    ->where('horario_base_id', $horarioBaseId)
                    ->exists();

                if ($existente) {
                    continue;
                }

                // 2. Obtener el HorarioBase para tomar sus capacidades configuradas
                $horarioBase = HorarioBase::find($horarioBaseId);
                if (! $horarioBase) {
                    continue;
                }

                $capacidad = (int) ($horarioBase->capacidad ?? 0);
                $capacidadLimite = (int) (($horarioBase->capacidad_limite ?? 0) > $capacidad
                    ? $horarioBase->capacidad_limite
                    : $capacidad);

                // 3. Crear el HorarioMateriaPeriodo
                $horarioMP = new ModeloHorarioMateriaPeriodo;
                $horarioMP->materia_periodo_id = $this->materiaPeriodo->id;
                $horarioMP->horario_base_id = $horarioBase->id;
                $horarioMP->habilitado = $this->hmp_habilitado;
                $horarioMP->fecha_inicio_habilitado = $this->hmp_fecha_inicio_habilitado ?: null;
                $horarioMP->fecha_fin_habilitado = $this->hmp_fecha_fin_habilitado ?: null;
                $horarioMP->capacidad = $capacidad;
                $horarioMP->capacidad_limite = $capacidadLimite;
                $horarioMP->ampliar_cupos_limite = $this->hmp_ampliar_cupos_limite;
                $horarioMP->cupos_disponibles = $this->hmp_ampliar_cupos_limite ? $capacidadLimite : $capacidad;
                $horarioMP->save();

                // 4. Duplicar los ítems de corte para este nuevo horario
                $this->duplicarItemsParaHorario($horarioMP);
                $creadosCount++;
            }

            if ($creadosCount === 0) {
                DB::rollBack();
                $this->dispatch(
                    'notificacionHMP',
                    tipo: 'warning',
                    titulo: 'Sin cambios',
                    mensaje: 'Los horarios base seleccionados ya estaban asignados a esta materia en este período.'
                );

                return;
            }

            DB::commit();
            $this->resetearFormulario();
            $this->dispatch('cerrarOffcanvas', nombreModal: 'offcanvasNuevoHorarioMP');

            $mensajeExito = $creadosCount === 1
                ? 'Se vinculó 1 horario y se generaron sus ítems de calificación correctamente.'
                : "Se vincularon {$creadosCount} horarios y se generaron sus ítems de calificación correctamente.";

            $this->dispatch(
                'notificacionHMP',
                tipo: 'success',
                titulo: '¡Horarios vinculados!',
                mensaje: $mensajeExito
            );
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al guardar HorarioMateriaPeriodo y/o duplicar ítems: '.$e->getMessage().' en '.$e->getFile().':'.$e->getLine());
            session()->flash('mensaje_error_hmp', 'No se pudo vincular el horario. Verifique los datos o contacte soporte. Error: '.substr($e->getMessage(), 0, 100).'...');
        }
    }

    /**
     * Duplica los ItemPlantilla como ItemCorteMateriaPeriodo para un HorarioMateriaPeriodo dado.
     */
    protected function duplicarItemsParaHorario(ModeloHorarioMateriaPeriodo $horarioMP)
    {
        $materiaOriginalId = $this->materiaPeriodo->materia_id;
        $periodoActualId = $this->materiaPeriodo->periodo_id;

        if (! $this->materiaPeriodo->materia || ! $this->materiaPeriodo->materia->escuela_id) {
            Log::error("No se pudo determinar la escuela para duplicar ítems. HMP ID: {$horarioMP->id}");
            throw new \Exception('Falta información de la escuela para duplicar ítems.');
        }
        $escuelaId = $this->materiaPeriodo->materia->escuela_id;

        $cortesDelPeriodo = CortePeriodo::where('periodo_id', $periodoActualId)
            ->whereHas('corteEscuela', function ($query) use ($escuelaId) {
                $query->where('escuela_id', $escuelaId);
            })
            ->with('corteEscuela:id,orden')
            ->get();

        if ($cortesDelPeriodo->isEmpty()) {
            Log::warning("No se encontraron CortePeriodo para el Periodo ID {$periodoActualId} (Escuela ID: {$escuelaId}) para duplicar ítems para HMP ID {$horarioMP->id}.");

            return;
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
                    'horario_materia_periodo_id' => $horarioMP->id,
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

        if (! $this->horarioMPEditando) {
            session()->flash('mensaje_error_hmp', 'Error: No se encontró el horario del período para editar.');
            $this->dispatch('cerrarOffcanvas', nombreModal: 'offcanvasEditarHorarioMP');

            return;
        }

        $horarioBase = $this->horarioMPEditando->horarioBase()->with('aula.sede')->first();

        if ($horarioBase) {
            $this->display_hb_dia = $horarioBase->dia_semana ?? "Día {$horarioBase->dia}";
            $this->display_hb_hora_inicio = $horarioBase->hora_inicio_formato ?? $horarioBase->hora_inicio;
            $this->display_hb_hora_fin = $horarioBase->hora_fin_formato ?? $horarioBase->hora_fin;
            $this->display_hb_aula_nombre = $horarioBase->aula?->nombre ?? 'N/A';
            $this->display_hb_sede_nombre = $horarioBase->aula?->sede?->nombre ?? 'N/A';
            $this->display_hb_capacidad_base = $horarioBase->capacidad;
        } else {
            session()->flash('mensaje_error_hmp', 'Advertencia: El horario base asociado a esta configuración no fue encontrado.');
            $this->display_hb_dia = 'N/A';
        }

        $this->hmp_capacidad = $this->horarioMPEditando->capacidad;
        $this->hmp_capacidad_limite = $this->horarioMPEditando->capacidad_limite;
        $this->hmp_ampliar_cupos_limite = (bool) $this->horarioMPEditando->ampliar_cupos_limite;

        $this->dispatch('abrirOffcanvas', nombreModal: 'offcanvasEditarHorarioMP');
    }

    public function actualizarHorario()
    {
        if (! $this->horarioMPEditando) {
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
            $this->horarioMPEditando->save();
            DB::commit();

            $this->resetearFormulario();
            $this->dispatch('cerrarOffcanvas', nombreModal: 'offcanvasEditarHorarioMP');
            $this->dispatch(
                'notificacionHMP',
                tipo: 'success',
                titulo: '¡Actualizado!',
                mensaje: 'Capacidades del horario actualizadas correctamente.'
            );
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al actualizar HorarioMateriaPeriodo ID {$this->horarioMPEditando->id}: ".$e->getMessage());
            session()->flash('mensaje_error_hmp', 'No se pudo actualizar el horario. Error: '.$e->getMessage());
        }
    }

    public function abrirFormularioNuevo()
    {
        $this->resetearFormulario();
        $this->inicializarFechasHabilitacion();
        $this->horarioMPEditando = null;
        $this->dispatch('abrirOffcanvas', nombreModal: 'offcanvasNuevoHorarioMP');
    }

    /**
     * Verifica si el horario del período tiene matrículas registradas.
     * - Si tiene matrículas: no permite eliminar, lo deshabilita y notifica con SweetAlert2.
     * - Si no tiene matrículas: solicita confirmación mediante SweetAlert2.
     */
    public function confirmarEliminarHorarioMP($horarioMPId)
    {
        $horarioMP = ModeloHorarioMateriaPeriodo::with('horarioBase.aula.sede')->find($horarioMPId);
        if (! $horarioMP) {
            $this->dispatch(
                'notificacionHMP',
                tipo: 'error',
                titulo: 'Error',
                mensaje: 'No se encontró el horario del período para eliminar.'
            );

            return;
        }

        // 1. Verificar si existen matrículas asociadas a este horario del período
        $tieneMatriculas = Matricula::where('horario_materia_periodo_id', $horarioMP->id)->exists()
            || MatriculaHorarioMateriaPeriodo::where('horario_materia_periodo_id', $horarioMP->id)->exists();

        // 2. Si tiene matrículas, deshabilitar e informar que no se puede eliminar
        if ($tieneMatriculas) {
            $horarioMP->habilitado = false;
            $horarioMP->save();

            $this->dispatch(
                'notificacionHMP',
                tipo: 'warning',
                titulo: 'No se puede eliminar',
                mensaje: 'Este horario ya tiene matrículas registradas en el período, por lo que no se puede eliminar y ha sido deshabilitado.'
            );

            return;
        }

        // 3. Si no tiene matrículas, pedir confirmación con SweetAlert2
        $hb = $horarioMP->horarioBase;
        $dia = $hb?->dia_semana ?? 'Horario';
        $hora = $hb ? (($hb->hora_inicio_formato ?? $hb->hora_inicio).' - '.($hb->hora_fin_formato ?? $hb->hora_fin)) : '';
        $aula = $hb?->aula?->nombre ?? '';

        $this->dispatch(
            'confirmarEliminarHorarioMPEvento',
            id: $horarioMP->id,
            dia: $dia,
            hora: $hora,
            aula: $aula
        );
    }

    public function eliminarHorarioMP($horarioMPId)
    {
        $horarioMP = $horarioMPId instanceof ModeloHorarioMateriaPeriodo
            ? $horarioMPId
            : ModeloHorarioMateriaPeriodo::find($horarioMPId);

        if (! $horarioMP) {
            return;
        }

        // Validación de seguridad adicional antes de eliminar
        $tieneMatriculas = Matricula::where('horario_materia_periodo_id', $horarioMP->id)->exists()
            || MatriculaHorarioMateriaPeriodo::where('horario_materia_periodo_id', $horarioMP->id)->exists();

        if ($tieneMatriculas) {
            $horarioMP->habilitado = false;
            $horarioMP->save();

            $this->dispatch(
                'notificacionHMP',
                tipo: 'warning',
                titulo: 'No se puede eliminar',
                mensaje: 'Este horario ya tiene matrículas registradas en el período, por lo que no se puede eliminar y ha sido deshabilitado.'
            );

            return;
        }

        DB::beginTransaction();
        try {
            ItemCorteMateriaPeriodo::where('horario_materia_periodo_id', $horarioMP->id)->delete();
            $horarioMP->delete();
            DB::commit();

            $this->dispatch(
                'notificacionHMP',
                tipo: 'success',
                titulo: '¡Eliminado!',
                mensaje: 'El horario fue desvinculado del período correctamente.'
            );
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al eliminar HorarioMateriaPeriodo ID {$horarioMP->id}: ".$e->getMessage());
            $this->dispatch(
                'notificacionHMP',
                tipo: 'error',
                titulo: 'Error',
                mensaje: 'No se pudo desvincular el horario del período.'
            );
        }
    }

    public function toggleHabilitado($horarioMPId)
    {
        $horarioMP = ModeloHorarioMateriaPeriodo::find($horarioMPId);
        if ($horarioMP) {
            try {
                $horarioMP->habilitado = ! $horarioMP->habilitado;
                $horarioMP->save();
                $estado = $horarioMP->habilitado ? 'habilitado' : 'deshabilitado';
                $this->dispatch(
                    'notificacionHMP',
                    tipo: 'success',
                    titulo: 'Estado actualizado',
                    mensaje: "Horario del período {$estado} correctamente."
                );
            } catch (\Exception $e) {
                Log::error("Error al cambiar estado de HMP ID {$horarioMP->id}: ".$e->getMessage());
                session()->flash('mensaje_error_hmp', 'No se pudo cambiar el estado del horario.');
            }
        } else {
            session()->flash('mensaje_error_hmp', 'No se encontró el horario para cambiar estado.');
        }
    }

    public function resetearFormulario()
    {
        $this->resetValidation();
        $this->resetErrorBag();
        $this->reset([
            'sede_id_formulario',
            'aula_id_formulario',
            'horario_base_id_seleccionado',
            'horarios_base_seleccionados',
            'hmp_habilitado',
            'hmp_fecha_inicio_habilitado',
            'hmp_fecha_fin_habilitado',
            'hmp_capacidad',
            'hmp_capacidad_limite',
            'hmp_ampliar_cupos_limite',
            'display_hb_dia',
            'display_hb_hora_inicio',
            'display_hb_hora_fin',
            'display_hb_aula_nombre',
            'display_hb_sede_nombre',
            'display_hb_capacidad_base',
        ]);
        $this->horarios_base_seleccionados = [];
        $this->hmp_habilitado = true;
        $this->hmp_ampliar_cupos_limite = false;
        $this->inicializarFechasHabilitacion();
        $this->hmp_capacidad = 0;
        $this->hmp_capacidad_limite = 0;

        $this->horarioMPEditando = null;
        $this->aulas_formulario = new EloquentCollection;
        $this->horarios_base_formulario = new EloquentCollection;
    }

    public function cancelar()
    {
        $nombreOffcanvas = $this->horarioMPEditando ? 'offcanvasEditarHorarioMP' : 'offcanvasNuevoHorarioMP';
        $this->resetearFormulario();
        $this->dispatch('cerrarOffcanvas', nombreModal: $nombreOffcanvas);
    }

    public function render()
    {
        $query = ModeloHorarioMateriaPeriodo::where('materia_periodo_id', $this->materiaPeriodo->id)
            ->with([
                'horarioBase.aula.tipo',
                'horarioBase.aula.sede',
            ]);

        if ($this->sedeFiltro) {
            $query->whereHas('horarioBase.aula', function ($q) {
                $q->where('sede_id', $this->sedeFiltro);
            });
        }
        if ($this->tipoAulaFiltro) {
            $query->whereHas('horarioBase.aula', function ($q) {
                $q->where('tipo_aula_id', $this->tipoAulaFiltro);
            });
        }

        $horariosMP = $query->select('horarios_materia_periodo.*')
            ->join('horarios_base', 'horarios_materia_periodo.horario_base_id', '=', 'horarios_base.id')
            ->orderBy('horarios_base.dia', 'asc')
            ->orderBy('horarios_base.hora_inicio', 'asc')
            ->paginate(10);

        return view('livewire.escuelas.horarios-materia-periodo', [
            'horariosMP' => $horariosMP,
            'sedesFiltroLista' => $this->sedes,
            'tiposAulaFiltroLista' => $this->tiposAula,
        ]);
    }
}
