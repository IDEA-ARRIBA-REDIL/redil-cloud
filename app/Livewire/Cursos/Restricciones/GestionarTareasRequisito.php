<?php

namespace App\Livewire\Cursos\Restricciones;

use Livewire\Component;
use App\Models\Curso;
use App\Models\TareaConsolidacion;
use App\Models\EstadoTareaConsolidacion;

class GestionarTareasRequisito extends Component
{
    public Curso $curso;
    public $tareaSeleccionada = '';
    public $estadoSeleccionado = '';

    public $tareas = [];
    public $estados = [];

    public function mount(Curso $curso)
    {
        $this->curso = $curso;
        $this->cargarDatos();
    }

    private function cargarDatos()
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

        // Verificar existencia
        $existente = $this->curso->tareasRequisito()
            ->where('tarea_consolidacion_id', $this->tareaSeleccionada)
            ->wherePivot('estado_tarea_consolidacion_id', $this->estadoSeleccionado)
            ->exists();

        if ($existente) {
             $this->dispatch('msn', [
                'msn' => 'Esta tarea con ese estado ya está agregada.',
                'icon' => 'warning'
            ]);
            return;
        }

        $maxIndice = $this->curso->tareasRequisito()->max('indice') ?? 0;

        $this->curso->tareasRequisito()->attach($this->tareaSeleccionada, [
            'estado_tarea_consolidacion_id' => $this->estadoSeleccionado,
            'indice' => $maxIndice + 1,
        ]);

        $this->reset(['tareaSeleccionada', 'estadoSeleccionado']);

         $this->dispatch('msn', [
            'msn' => 'Requisito de tarea agregado.',
            'icon' => 'success'
        ]);
    }

    public function eliminarTarea($tareaId)
    {
        $this->curso->tareasRequisito()->detach($tareaId);

        // Re-index
        $tareas = $this->curso->tareasRequisito()->orderBy('pivot_indice')->get();
        foreach ($tareas as $index => $tarea) {
            $this->curso->tareasRequisito()->updateExistingPivot($tarea->id, [
                'indice' => $index + 1
            ]);
        }

         $this->dispatch('msn', [
            'msn' => 'Requisito eliminado.',
            'icon' => 'success'
        ]);
    }

    public function render()
    {
        $tareasRequisito = $this->curso->tareasRequisito()
            ->orderBy('curso_tarea_requisito.indice')
            ->get();

        return view('livewire.cursos.restricciones.gestionar-tareas-requisito', [
            'tareasRequisito' => $tareasRequisito
        ]);
    }
}
