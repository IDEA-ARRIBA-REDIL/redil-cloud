<?php

namespace App\Livewire\Cursos\Restricciones;

use Livewire\Component;
use App\Models\Curso;
use App\Models\TareaConsolidacion;
use App\Models\EstadoTareaConsolidacion;

class GestionarTareasCulminar extends Component
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
        $existente = $this->curso->tareasCulminar()
            ->where('tarea_consolidacion_id', $this->tareaSeleccionada)
            ->exists();

        if ($existente) {
             $this->dispatch('msn', [
                'msn' => 'Esta tarea ya está configurada para culminar.',
                'icon' => 'warning'
            ]);
            return;
        }

        $maxIndice = $this->curso->tareasCulminar()->max('indice') ?? 0;

        $this->curso->tareasCulminar()->attach($this->tareaSeleccionada, [
            'estado_tarea_consolidacion_id' => $this->estadoSeleccionado,
            'indice' => $maxIndice + 1,
        ]);

        $this->reset(['tareaSeleccionada', 'estadoSeleccionado']);

         $this->dispatch('msn', [
            'msn' => 'Tarea a culminar agregada.',
            'icon' => 'success'
        ]);
    }

    public function eliminarTarea($tareaId)
    {
        $this->curso->tareasCulminar()->detach($tareaId);

        // Re-index
        $tareas = $this->curso->tareasCulminar()->orderBy('indice')->get();
        foreach ($tareas as $index => $tarea) {
            $this->curso->tareasCulminar()->updateExistingPivot($tarea->id, [
                'indice' => $index + 1
            ]);
        }

         $this->dispatch('msn', [
            'msn' => 'Tarea eliminada.',
            'icon' => 'success'
        ]);
    }

    public function render()
    {
        $tareasCulminar = $this->curso->tareasCulminar()
            ->orderBy('curso_tarea_culminar.indice')
            ->get();

        return view('livewire.cursos.restricciones.gestionar-tareas-culminar', [
            'tareasCulminar' => $tareasCulminar
        ]);
    }
}
