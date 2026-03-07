<?php

namespace App\Livewire\ActividadCategoria;

use Livewire\Component;
use App\Models\ActividadCategoria;
use App\Models\TareaConsolidacion;
use App\Models\EstadoTareaConsolidacion;
use App\Models\ActividadCategoriaTareaCulminada;

class GestionarTareasCulminadas extends Component
{
    public ActividadCategoria $categoria;
    
    // Propiedades para el formulario
    public $tareaSeleccionada = '';
    public $estadoSeleccionado = '';
    
    // Datos para los selectores
    public $tareas = [];
    public $estados = [];
    
    public function mount(ActividadCategoria $categoria)
    {
        $this->categoria = $categoria;
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
        $existe = ActividadCategoriaTareaCulminada::where('actividad_categoria_id', $this->categoria->id)
            ->where('tarea_consolidacion_id', $this->tareaSeleccionada)
            ->where('estado_tarea_consolidacion_id', $this->estadoSeleccionado)
            ->exists();
        
        if ($existe) {
            $this->dispatch('msn', 
                msnTitulo: 'Tarea Duplicada',
                msnTexto: 'Esta tarea con ese estado ya está agregada para culminar.',
                msnIcono: 'warning'
            );
            return;
        }
        
        // Obtener el siguiente índice
        $maxIndice = ActividadCategoriaTareaCulminada::where('actividad_categoria_id', $this->categoria->id)
            ->max('indice') ?? 0;
        
        // Crear la tarea a culminar
        ActividadCategoriaTareaCulminada::create([
            'actividad_categoria_id' => $this->categoria->id,
            'tarea_consolidacion_id' => $this->tareaSeleccionada,
            'estado_tarea_consolidacion_id' => $this->estadoSeleccionado,
            'indice' => $maxIndice + 1,
        ]);
        
        // Resetear formulario
        $this->reset(['tareaSeleccionada', 'estadoSeleccionado']);
        
        // Notificar éxito
        $this->dispatch('msn',
            msnTitulo: '¡Éxito!',
            msnTexto: 'Tarea a culminar agregada correctamente.',
            msnIcono: 'success'
        );
        
        // Refrescar la lista
        $this->categoria->refresh();
    }
    
    public function eliminarTarea($id)
    {
        $tarea = ActividadCategoriaTareaCulminada::findOrFail($id);
        
        // Verificar que pertenezca a esta categoría
        if ($tarea->actividad_categoria_id !== $this->categoria->id) {
            return;
        }
        
        $tarea->delete();
        
        $this->dispatch('msn',
            msnTitulo: 'Eliminada',
            msnTexto: 'Tarea a culminar eliminada correctamente.',
            msnIcono: 'success'
        );
        
        $this->categoria->refresh();
    }
    
    public function actualizarOrden($ordenes)
    {
        foreach ($ordenes as $item) {
            ActividadCategoriaTareaCulminada::where('id', $item['id'])
                ->update(['indice' => $item['orden']]);
        }
        
        $this->categoria->refresh();
    }

    public function render()
    {
        return view('livewire.actividad-categoria.gestionar-tareas-culminadas', [
            'tareasCulminadas' => $this->categoria->tareasCulminadas()
                ->with(['tareaConsolidacion', 'estadoTarea'])
                ->orderBy('indice')
                ->get()
        ]);
    }
}
