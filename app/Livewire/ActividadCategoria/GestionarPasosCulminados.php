<?php

namespace App\Livewire\ActividadCategoria;

use App\Models\ActividadCategoria;
use App\Models\PasoCrecimiento;
use App\Models\EstadoPasoCrecimientoUsuario;
use Livewire\Component;

class GestionarPasosCulminados extends Component
{
    public ActividadCategoria $categoria;
    public $pasoSeleccionado = '';
    public $estadoSeleccionado = '';
    
    public $pasos = [];
    public $estados = [];
    public $pasosCulminados = [];

    public function mount(ActividadCategoria $categoria)
    {
        $this->categoria = $categoria;
        $this->cargarDatos();
    }

    private function cargarDatos()
    {
        $this->pasos = PasoCrecimiento::orderBy('nombre')->get();
        $this->estados = EstadoPasoCrecimientoUsuario::orderBy('nombre')->get();
        $this->pasosCulminados = $this->categoria->procesosCulminados()
            ->orderBy('actividad_categoria_procesos_culminados.indice')
            ->get();
    }

    public function agregarPaso()
    {
        $this->validate([
            'pasoSeleccionado' => 'required|exists:pasos_crecimiento,id',
            'estadoSeleccionado' => 'required|exists:estados_pasos_crecimiento_usuario,id',
        ], [
            'pasoSeleccionado.required' => 'Debes seleccionar un paso',
            'pasoSeleccionado.exists' => 'El paso seleccionado no existe',
            'estadoSeleccionado.required' => 'Debes seleccionar un estado',
            'estadoSeleccionado.exists' => 'El estado seleccionado no existe',
        ]);

        // Verificar si ya existe esta combinación
        $existente = $this->categoria->procesosCulminados()
            ->where('paso_crecimiento_id', $this->pasoSeleccionado)
            ->exists();

        if ($existente) {
            $this->dispatch('msn', msn: 'Este paso ya está agregado para culminar', icon: 'warning');
            return;
        }

        // Obtener el máximo índice
        $maxIndice = $this->categoria->procesosCulminados()->max('indice') ?? 0;

        // Agregar el paso con el nuevo campo FK
        $this->categoria->procesosCulminados()->attach($this->pasoSeleccionado, [
            'estado_paso_crecimiento_usuario_id' => $this->estadoSeleccionado,
            'estado' => $this->estadoSeleccionado, // Mantener por compatibilidad temporal
            'indice' => $maxIndice + 1,
        ]);

        $this->reset(['pasoSeleccionado', 'estadoSeleccionado']);
        $this->cargarDatos();
        $this->dispatch('msn', msn: 'Paso agregado correctamente', icon: 'success');
    }

    public function eliminarPaso($pasoId)
    {
        $this->categoria->procesosCulminados()->detach($pasoId);
        
        // Reordenar índices
        $pasos = $this->categoria->procesosCulminados()->orderBy('indice')->get();
        foreach ($pasos as $index => $paso) {
            $this->categoria->procesosCulminados()->updateExistingPivot($paso->id, [
                'indice' => $index + 1
            ]);
        }

        $this->cargarDatos();
        $this->dispatch('msn', msn: 'Paso eliminado correctamente', icon: 'success');
    }

    public function actualizarOrden($ordenes)
    {
        foreach ($ordenes as $orden) {
            $this->categoria->procesosCulminados()->updateExistingPivot($orden['id'], [
                'indice' => $orden['orden']
            ]);
        }
        
        $this->cargarDatos();
        $this->dispatch('msn', msn: 'Orden actualizado correctamente', icon: 'success');
    }

    public function render()
    {
        return view('livewire.actividad-categoria.gestionar-pasos-culminados');
    }
}
