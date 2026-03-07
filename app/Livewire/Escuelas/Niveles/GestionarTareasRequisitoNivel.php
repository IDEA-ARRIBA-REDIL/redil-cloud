<?php

namespace App\Livewire\Escuelas\Niveles;

use Livewire\Component;
use App\Models\NivelAgrupacion;
use App\Models\TareaConsolidacion;
use App\Models\EstadoTareaConsolidacion;
use Illuminate\Support\Collection;

class GestionarTareasRequisitoNivel extends Component
{
    public $nivel;
    public $tareasRequisito;
    public $tareaSeleccionada;
    public $estadoSeleccionado;

    // Draft Mode Properties
    public $draftMode = false;
    public $draftItems = [];

    protected $listeners = ['eliminarNivelTareaRequisito'];

    public function mount(NivelAgrupacion $nivel)
    {
        $this->nivel = $nivel;
        $this->draftMode = !$nivel->exists;

        $this->cargarDatos();
    }

    public function cargarDatos()
    {
        if (!$this->draftMode) {
            $this->tareasRequisito = $this->nivel->tareasRequisito()
                ->with(['tareaConsolidacion', 'estadoTareaConsolidacion'])
                ->orderBy('indice')
                ->get();
        } else {
            $this->tareasRequisito = collect();
        }
    }

    public function agregarTarea()
    {
        $this->validate([
            'tareaSeleccionada' => 'required',
            'estadoSeleccionado' => 'required'
        ]);

        if ($this->draftMode) {
            $tareaModel = TareaConsolidacion::find($this->tareaSeleccionada);
            $estadoModel = EstadoTareaConsolidacion::find($this->estadoSeleccionado);

            foreach($this->draftItems as $item) {
                if ($item['tarea_id'] == $this->tareaSeleccionada && $item['estado_id'] == $this->estadoSeleccionado) {
                    $this->dispatch('msn', msnTexto: 'Esta tarea ya está agregada como requisito', msnIcono: 'warning');
                    return;
                }
            }

            $this->draftItems[] = [
                'tarea_id' => $this->tareaSeleccionada,
                'tarea_nombre' => $tareaModel->nombre,
                'estado_id' => $this->estadoSeleccionado,
                'estado_nombre' => $estadoModel->nombre,
                'estado_color' => $estadoModel->color ?? 'primary',
                'temp_id' => uniqid()
            ];

        } else {
             $exists = $this->nivel->tareasRequisito()
                ->where('tarea_consolidacion_id', $this->tareaSeleccionada)
                ->where('estado_tarea_consolidacion_id', $this->estadoSeleccionado)
                ->exists();

            if ($exists) {
                $this->dispatch('msn', msnTexto: 'Esta tarea ya está agregada como requisito', msnIcono: 'warning');
                return;
            }

            $maxIndice = $this->nivel->tareasRequisito()->max('indice') ?? 0;

            $this->nivel->tareasRequisito()->create([
                'tarea_consolidacion_id' => $this->tareaSeleccionada,
                'estado_tarea_consolidacion_id' => $this->estadoSeleccionado,
                'indice' => $maxIndice + 1,
            ]);

            $this->cargarDatos();
        }

        $this->reset(['tareaSeleccionada', 'estadoSeleccionado']);
        $this->dispatch('msn', msnTitulo: 'Agregado', msnTexto: 'Tarea requisito agregada.', msnIcono: 'success');
    }

    public function confirmarEliminacionNivelTareaRequisito($tareaId)
    {
        $this->dispatch('confirmarEliminacion',
            id: $tareaId,
            titulo: '¿Eliminar tarea requisito?',
            texto: 'Esta tarea dejará de ser requerida para el grado.',
            metodo: 'eliminarNivelTareaRequisito'
        );
    }

    public function eliminarNivelTareaRequisito($tareaId)
    {
        if ($this->draftMode) {
             $this->draftItems = array_filter($this->draftItems, function($item) use ($tareaId) {
                 return $item['temp_id'] != $tareaId;
             });
        } else {
            $this->nivel->tareasRequisito()->where('id', $tareaId)->delete();
            $this->cargarDatos();
        }

        $this->dispatch('msn', msnTitulo: 'Eliminado', msnTexto: 'Tarea requisito eliminada.', msnIcono: 'success');
    }

    public function render()
    {
        return view('livewire.escuelas.niveles.gestionar-tareas-requisito-nivel', [
            'tareasDisponibles' => TareaConsolidacion::orderBy('nombre')->get(),
            'estadosDisponibles' => EstadoTareaConsolidacion::orderBy('nombre')->get(),
            'tareasRequisito' => $this->tareasRequisito,
            'draftItems' => $this->draftItems
        ]);
    }
}
