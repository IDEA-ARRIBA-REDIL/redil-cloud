<?php

namespace App\Livewire\Escuelas;

use Livewire\Component;
use App\Models\HorarioMateriaPeriodo;
use App\Models\ItemCorteMateriaPeriodo;
use App\Models\CortePeriodo;
use App\Models\ItemPlantilla;
use App\Models\TipoItem;
use App\Models\Configuracion;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class GestionItemsCorteMateriaPeriodo extends Component
{
    public $horarioAsignado;
    public $cortes = [];
    public $itemsPorCorte = [];
    public $tiposItem = [];

    // Estado del Modal
    public $modalOpen = false;
    public $modoEdicion = false; // false = Crear, true = Editar
    public $itemEditandoId = null;

    // Campos del Formulario
    public $corte_periodo_id;
    public $tipo_item_id;
    public $nombre;
    public $contenido;
    public $visible;
    public $habilitar_entregable;
    public $porcentaje;
    public $fecha_inicio;
    public $fecha_fin;


    public $orden;
    public $calificable;
    public $minFechaCorte;
    public $maxFechaCorte;
    public $bloqueoEdicion = false; // Nueva propiedad para controlar la edición restringida

    protected $listeners = ['abrirModalCrear', 'abrirModalEditar', 'eliminarItem', 'actualizarContenidoEditor'];

    public function mount(HorarioMateriaPeriodo $horarioAsignado)
    {
        $this->horarioAsignado = $horarioAsignado;
        $this->tiposItem = TipoItem::all(); // Asumiendo que existe este modelo
        $this->cargarDatos();
    }

    public function cargarDatos()
    {
        // Cargar cortes del periodo
        $this->cortes = CortePeriodo::where('periodo_id', $this->horarioAsignado->materiaPeriodo->periodo_id)
            ->with(['corteEscuela'])
            ->get()
            ->sortBy(function($corte) {
                return $corte->corteEscuela->orden ?? 999;
            });

        // Cargar items existentes agrupados por corte
        $items = ItemCorteMateriaPeriodo::where('horario_materia_periodo_id', $this->horarioAsignado->id)
            ->orderBy('orden')
            ->get();

        $this->itemsPorCorte = [];
        foreach ($this->cortes as $corte) {
            $this->itemsPorCorte[$corte->id] = $items->where('corte_periodo_id', $corte->id);
        }
    }

    public function abrirModalCrear($corteId)
    {
        $this->resetInputFields();
        $this->corte_periodo_id = $corteId;

        $corte = CortePeriodo::find($corteId);
        if ($corte) {
            $this->minFechaCorte = $corte->fecha_inicio ? $corte->fecha_inicio->format('Y-m-d') : null;
            $this->maxFechaCorte = $corte->fecha_fin ? $corte->fecha_fin->format('Y-m-d') : null;
        }

        $this->modoEdicion = false;
        $this->calificable = true; // Default true
        $this->dispatch('abrir-modal-crear');
        $this->dispatch('limpiar-editor-crear');
    }

    public function abrirModalEditar($itemId)
    {
        $item = ItemCorteMateriaPeriodo::find($itemId);
        if (!$item) return;

        // Validar permisos si viene de plantilla
        if ($item->item_plantilla_id) {
             // Aquí podrías agregar lógica extra si ciertos items de plantilla son de solo lectura
             // Por ahora permitimos editar todo
        }

        $this->itemEditandoId = $item->id;
        $this->corte_periodo_id = $item->corte_periodo_id;

        $corte = CortePeriodo::find($this->corte_periodo_id);
        if ($corte) {
            $this->minFechaCorte = $corte->fecha_inicio ? $corte->fecha_inicio->format('Y-m-d') : null;
            $this->maxFechaCorte = $corte->fecha_fin ? $corte->fecha_fin->format('Y-m-d') : null;
        }

        $this->tipo_item_id = $item->tipo_item_id;
        $this->nombre = $item->nombre;
        $this->contenido = $item->contenido;
        $this->visible = (bool)$item->visible;
        $this->habilitar_entregable = (bool)$item->habilitar_entregable;
        $this->calificable = (bool)$item->calificable;
        $this->porcentaje = $item->porcentaje;
        $this->orden = $item->orden;
        $this->fecha_inicio = $item->fecha_inicio ? \Carbon\Carbon::parse($item->fecha_inicio)->format('Y-m-d') : null;
        $this->fecha_fin = $item->fecha_fin ? \Carbon\Carbon::parse($item->fecha_fin)->format('Y-m-d') : null;

        // Validar si tiene notas asignada
        $tieneNotas = $item->respuestas()->whereNotNull('nota_obtenida')->exists();
        $this->bloqueoEdicion = $tieneNotas;

        if ($this->bloqueoEdicion) {
            $this->dispatch('notificacion', ['mensaje' => 'Este ítem ya tiene calificaciones. Solo se permite editar fechas y contenido.']);
        }

        $this->modoEdicion = true;
        // Emitir evento para cargar contenido en Quill de Edición
        $this->dispatch('abrir-modal-editar');
        $this->dispatch('cargar-contenido-editor-editar', contenido: $this->contenido);
    }

    public function actualizarContenidoEditor($contenido)
    {
        $this->contenido = $contenido;
    }

    public function guardarItem()
    {
        $this->validate([
            'corte_periodo_id' => 'required|exists:cortes_periodo,id',
            'tipo_item_id' => 'required|exists:tipos_item,id',
            'nombre' => 'required|string|max:255',
            'contenido' => 'nullable|string',
            'visible' => 'boolean',
            'habilitar_entregable' => 'boolean',
            'calificable' => 'boolean',
            'porcentaje' => 'required|numeric|min:0|max:100',
            'orden' => 'required|integer|min:0',
            'fecha_inicio' => ['nullable', 'date',
                $this->minFechaCorte ? 'after_or_equal:'.$this->minFechaCorte : '',
                $this->maxFechaCorte ? 'before_or_equal:'.$this->maxFechaCorte : ''
            ],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio',
                $this->maxFechaCorte ? 'before_or_equal:'.$this->maxFechaCorte : ''
            ],
        ]);

        // Validar suma de porcentajes
        $sumaActual = $this->calcularSumaPorcentajes($this->corte_periodo_id, $this->itemEditandoId); // Excluir el actual si es edición
        if (($sumaActual + $this->porcentaje) > 100) {
            $this->addError('porcentaje', 'La suma de porcentajes de los items en este corte supera el 100%. Suma actual: ' . $sumaActual . '%');
            return;
        }

        if ($this->modoEdicion) {
            $item = ItemCorteMateriaPeriodo::find($this->itemEditandoId);

            if ($this->bloqueoEdicion) {
                // Solo actualizar campos no críticos
                $item->update([
                    'contenido' => $this->contenido,
                    'visible' => $this->visible,
                    'fecha_inicio' => $this->fecha_inicio,
                    'fecha_fin' => $this->fecha_fin,
                ]);
            } else {
                // Actualizar todo
                $item->update([
                    'tipo_item_id' => $this->tipo_item_id,
                    'nombre' => $this->nombre,
                    'contenido' => $this->contenido,
                    'visible' => $this->visible,
                    'habilitar_entregable' => $this->habilitar_entregable,
                    'calificable' => $this->calificable,
                    'porcentaje' => $this->porcentaje,
                    'orden' => $this->orden,
                    'fecha_inicio' => $this->fecha_inicio,
                    'fecha_fin' => $this->fecha_fin,
                ]);
            }
            session()->flash('mensaje_exito', 'Item actualizado correctamente.');
            $this->dispatch('cerrar-modal-editar');
        } else {
            ItemCorteMateriaPeriodo::create([
                'horario_materia_periodo_id' => $this->horarioAsignado->id,
                'materia_periodo_id' => $this->horarioAsignado->materia_periodo_id,
                'corte_periodo_id' => $this->corte_periodo_id,
                'tipo_item_id' => $this->tipo_item_id,
                'nombre' => $this->nombre,
                'contenido' => $this->contenido,
                'visible' => $this->visible ?? true,
                'habilitar_entregable' => $this->habilitar_entregable ?? false,
                'calificable' => $this->calificable ?? true,
                'porcentaje' => $this->porcentaje,
                'fecha_inicio' => $this->fecha_inicio,
                'fecha_fin' => $this->fecha_fin,
                'orden' => $this->orden,
            ]);
            session()->flash('mensaje_exito', 'Item creado correctamente.');
            $this->dispatch('cerrar-modal-crear');
        }

        // $this->dispatch('cerrar-modal-item'); // Ya se despacha individualmente arriba
        $this->cargarDatos();
    }

    public function eliminarItem($itemId)
    {
        $item = ItemCorteMateriaPeriodo::find($itemId);
        if (!$item) return;

        // Validar si tiene notas
        $tieneNotas = $item->respuestas()->whereNotNull('nota_obtenida')->exists();
        if ($tieneNotas) {
             // Dispatch SweetAlert error
            $this->dispatch('swal:error', [
                'title' => 'No se puede eliminar',
                'text' => 'Este ítem ya tiene calificaciones asociadas. Comuníquese con administración si necesita realizar esta acción.',
            ]);
            return;
        }

        $item->delete();
        $this->cargarDatos();
        session()->flash('mensaje_exito', 'Item eliminado correctamente.');
    }

    public function calcularSumaPorcentajes($corteId, $excluirItemId = null)
    {
        return ItemCorteMateriaPeriodo::where('horario_materia_periodo_id', $this->horarioAsignado->id)
            ->where('corte_periodo_id', $corteId)
            ->where('calificable', 1)
            ->when($excluirItemId, function($q) use ($excluirItemId) {
                $q->where('id', '!=', $excluirItemId);
            })
            ->sum('porcentaje');
    }

    private function resetInputFields()
    {
        $this->nombre = '';
        $this->contenido = '';
        $this->tipo_item_id = null;
        $this->visible = true;
        $this->habilitar_entregable = false;
        $this->calificable = true;
        $this->porcentaje = 0;
        $this->orden = 0;
        $this->fecha_inicio = null;
        $this->fecha_fin = null;
        $this->minFechaCorte = null;
        $this->maxFechaCorte = null;
        $this->itemEditandoId = null;
        $this->bloqueoEdicion = false;
        // $this->dispatch('limpiar-editor'); // Se hace explicito en abrirModalCrear
    }

    public function render()
    {
        return view('livewire.escuelas.gestion-items-corte-materia-periodo');
    }
}
