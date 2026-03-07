<?php

namespace App\Livewire\Actividad;

use Livewire\Component;
use App\Models\Actividad;
use App\Models\TareaConsolidacion;
use App\Models\EstadoTareaConsolidacion;
use App\Models\ActividadTareaRequisito;

class GestionarTareasRequisito extends Component
{
    public Actividad $actividad;
    
    // Propiedades para el formulario
    public $tareaSeleccionada = '';
    public $estadoSeleccionado = '';
    
    // Datos para los selectores
    public $tareas = [];
    public $estados = [];
    
    public function mount(Actividad $actividad)
    {
        $this->actividad = $actividad;
        $this->cargarDatos();
    }
    
    public function cargarDatos()
    {
        // Cargar todas las tareas de consolidación
        $this->tareas = TareaConsolidacion::orderBy('orden')->get();
        
        // Cargar todos los estados
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
        
        // Verificar que no exista ya esta combinación
        $existe = ActividadTareaRequisito::where('actividad_id', $this->actividad->id)
            ->where('tarea_consolidacion_id', $this->tareaSeleccionada)
            ->where('estado_tarea_consolidacion_id', $this->estadoSeleccionado)
            ->exists();
        
        if ($existe) {
            $this->dispatch('msn', 
                msnTitulo: 'Tarea Duplicada',
                msnTexto: 'Esta tarea con ese estado ya está agregada como requisito.',
                msnIcono: 'warning'
            );
            return;
        }
        
        // Obtener el siguiente índice
        $maxIndice = ActividadTareaRequisito::where('actividad_id', $this->actividad->id)
            ->max('indice') ?? 0;
        
        // Crear el requisito
        ActividadTareaRequisito::create([
            'actividad_id' => $this->actividad->id,
            'tarea_consolidacion_id' => $this->tareaSeleccionada,
            'estado_tarea_consolidacion_id' => $this->estadoSeleccionado,
            'indice' => $maxIndice + 1,
        ]);
        
        // Resetear formulario
        $this->reset(['tareaSeleccionada', 'estadoSeleccionado']);
        
        // Notificar éxito
        $this->dispatch('msn',
            msnTitulo: '¡Éxito!',
            msnTexto: 'Tarea requisito agregada correctamente.',
            msnIcono: 'success'
        );
        
        // Refrescar la lista
        $this->actividad->refresh();
    }
    
    public function eliminarTarea($id)
    {
        $tarea = ActividadTareaRequisito::findOrFail($id);
        
        // Verificar que pertenezca a esta actividad
        if ($tarea->actividad_id !== $this->actividad->id) {
            return;
        }
        
        $tarea->delete();
        
        $this->dispatch('msn',
            msnTitulo: 'Eliminada',
            msnTexto: 'Tarea requisito eliminada correctamente.',
            msnIcono: 'success'
        );
        
        $this->actividad->refresh();
    }
    
    public function actualizarOrden($ordenes)
    {
        foreach ($ordenes as $item) {
            ActividadTareaRequisito::where('id', $item['id'])
                ->update(['indice' => $item['orden']]);
        }
        
        $this->actividad->refresh();
    }

    public function render()
    {
        return view('livewire.actividad.gestionar-tareas-requisito', [
            'tareasRequisito' => $this->actividad->tareasRequisito()
                ->with(['tareaConsolidacion', 'estadoTarea'])
                ->orderBy('indice')
                ->get()
        ]);
    }
}
