<?php

namespace App\Livewire\Escuelas\NivelesEscuelas;

use App\Models\EstadoTareaConsolidacion;
use App\Models\NivelEscuela;
use App\Models\NivelTareaRequisito;
use App\Models\TareaConsolidacion;
use Livewire\Component;

class GestionarTareasRequisito extends Component
{
    public NivelEscuela $nivel;

    public $tareaSeleccionada = '';

    public $estadoSeleccionado = '';

    public $tareas = [];

    public $estados = [];

    public $draftMode = false;

    public $draftItems = [];

    public function mount(NivelEscuela $nivel)
    {
        $this->nivel = $nivel;
        $this->draftMode = ! $nivel->exists;
        $this->cargarDatos();
    }

    public function cargarDatos()
    {
        $this->tareas = TareaConsolidacion::orderBy('orden')->get();
        $this->estados = EstadoTareaConsolidacion::orderBy('puntaje')->get();
    }

    public function agregarTarea()
    {
        $this->validate([
            'tareaSeleccionada' => 'required|exists:tareas_consolidacion,id',
            'estadoSeleccionado' => 'required|exists:estados_tarea_consolidacion,id',
        ], [
            'tareaSeleccionada.required' => 'Debes seleccionar una tarea.',
            'estadoSeleccionado.required' => 'Debes seleccionar un estado.',
        ]);

        if ($this->draftMode) {
            $tareaModel = TareaConsolidacion::find($this->tareaSeleccionada);
            $estadoModel = EstadoTareaConsolidacion::find($this->estadoSeleccionado);

            foreach ($this->draftItems as $item) {
                if ($item['tarea_id'] == $this->tareaSeleccionada && $item['estado_id'] == $this->estadoSeleccionado) {
                    $this->dispatch('msn', msnTexto: 'Esta tarea con ese estado ya está agregada como requisito.', msnIcono: 'warning');

                    return;
                }
            }

            $this->draftItems[] = [
                'tarea_id' => $this->tareaSeleccionada,
                'tarea_nombre' => $tareaModel->nombre,
                'estado_id' => $this->estadoSeleccionado,
                'estado_nombre' => $estadoModel->nombre,
                'estado_color' => $estadoModel->color ?? 'primary',
                'temp_id' => uniqid(),
            ];
        } else {
            $existe = NivelTareaRequisito::where('nivel_id', $this->nivel->id)
                ->where('tarea_consolidacion_id', $this->tareaSeleccionada)
                ->exists();

            if ($existe) {
                $this->dispatch('msn', msnTexto: 'Esta tarea ya está agregada como requisito.', msnIcono: 'warning');

                return;
            }

            $maxIndice = NivelTareaRequisito::where('nivel_id', $this->nivel->id)->max('indice') ?? 0;

            NivelTareaRequisito::create([
                'nivel_id' => $this->nivel->id,
                'tarea_consolidacion_id' => $this->tareaSeleccionada,
                'estado_tarea_consolidacion_id' => $this->estadoSeleccionado,
                'indice' => $maxIndice + 1,
            ]);
        }

        $this->reset(['tareaSeleccionada', 'estadoSeleccionado']);
        $this->dispatch('msn', msnTitulo: '¡Éxito!', msnTexto: 'Tarea requisito agregada correctamente.', msnIcono: 'success');

        if (! $this->draftMode) {
            $this->nivel->refresh();
        }
    }

    public function eliminarTarea($id)
    {
        if ($this->draftMode) {
            $this->draftItems = array_filter($this->draftItems, function ($item) use ($id) {
                return $item['temp_id'] != $id;
            });
        } else {
            $tarea = NivelTareaRequisito::findOrFail($id);
            if ($tarea->nivel_id !== $this->nivel->id) {
                return;
            }
            $tarea->delete();
            $this->nivel->refresh();
        }

        $this->dispatch('msn', msnTitulo: 'Eliminada', msnTexto: 'Tarea requisito eliminada correctamente.', msnIcono: 'success');
    }

    public function render()
    {
        return view('livewire.escuelas.niveles-escuelas.gestionar-tareas-requisito', [
            'tareasRequisito' => $this->draftMode ? collect([]) : $this->nivel->tareasRequisito()
                ->with(['tareaConsolidacion', 'estadoTarea'])
                ->orderBy('indice')
                ->get(),
            'draftItems' => $this->draftItems,
        ]);
    }
}
