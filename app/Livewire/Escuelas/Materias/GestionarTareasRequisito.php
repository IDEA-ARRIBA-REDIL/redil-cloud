<?php

namespace App\Livewire\Escuelas\Materias;

use Livewire\Component;
use App\Models\Materia;
use App\Models\TareaConsolidacion;
use App\Models\EstadoTareaConsolidacion;
use App\Models\MateriaTareaRequisito;

class GestionarTareasRequisito extends Component
{
    public Materia $materia;

    // Propiedades para el formulario
    public $tareaSeleccionada = '';
    public $estadoSeleccionado = '';

    // Datos para los selectores
    public $tareas = [];
    public $estados = [];

    public $draftMode = false;
    public $draftItems = [];

    public function mount(Materia $materia)
    {
        $this->materia = $materia;
        $this->draftMode = !$materia->exists;
        $this->cargarDatos();
    }

    public function cargarDatos()
    {
        // Cargar todas las tareas de consolidación
        $this->tareas = TareaConsolidacion::orderBy('orden')->get();

        // Cargar todos los estados
        $this->estados = EstadoTareaConsolidacion::orderBy('puntaje')->get();

        if (!$this->draftMode) {
             // not used directly in render but good for consistency or if logic changes
        }
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
        $existe = MateriaTareaRequisito::where('materia_id', $this->materia->id)
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
        if ($this->draftMode) {
            $tareaModel = TareaConsolidacion::find($this->tareaSeleccionada);
            $estadoModel = EstadoTareaConsolidacion::find($this->estadoSeleccionado);

             // Verificar duplicados
            foreach($this->draftItems as $item) {
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
                'temp_id' => uniqid()
            ];
        } else {
            $maxIndice = MateriaTareaRequisito::where('materia_id', $this->materia->id)
                ->max('indice') ?? 0;

            // Crear el requisito
            MateriaTareaRequisito::create([
                'materia_id' => $this->materia->id,
                'tarea_consolidacion_id' => $this->tareaSeleccionada,
                'estado_tarea_consolidacion_id' => $this->estadoSeleccionado,
                'indice' => $maxIndice + 1,
            ]);
        }

        // Resetear formulario
        $this->reset(['tareaSeleccionada', 'estadoSeleccionado']);

        // Notificar éxito
        $this->dispatch('msn',
            msnTitulo: '¡Éxito!',
            msnTexto: 'Tarea requisito agregada correctamente.',
            msnIcono: 'success'
        );

        // Refrescar la lista
        $this->materia->refresh();
    }

    public function eliminarTarea($id)
    {
        if ($this->draftMode) {
             $this->draftItems = array_filter($this->draftItems, function($item) use ($id) {
                 return $item['temp_id'] != $id;
             });
        } else {
            $tarea = MateriaTareaRequisito::findOrFail($id);

            // Verificar que pertenezca a esta materia
            if ($tarea->materia_id !== $this->materia->id) {
                return;
            }

            $tarea->delete();
        }

        $this->dispatch('msn',
            msnTitulo: 'Eliminada',
            msnTexto: 'Tarea requisito eliminada correctamente.',
            msnIcono: 'success'
        );

        if (!$this->draftMode) {
            $this->materia->refresh();
        }
    }

    public function actualizarOrden($ordenes)
    {
        foreach ($ordenes as $item) {
            MateriaTareaRequisito::where('id', $item['id'])
                ->update(['indice' => $item['orden']]);
        }

        $this->materia->refresh();
    }

    public function render()
    {
        return view('livewire.escuelas.materias.gestionar-tareas-requisito', [
            'tareasRequisito' => $this->draftMode ? collect([]) : $this->materia->tareasRequisito()
                ->with(['tareaConsolidacion', 'estadoTarea'])
                ->orderBy('indice')
                ->get(),
            'draftItems' => $this->draftItems
        ]);
    }
}
