<?php

namespace App\Livewire\BloqueClasificacion;

use Livewire\Component;
use App\Models\BloqueClasificacionAsistente;
use App\Models\ClasificacionAsistente;
use Illuminate\Support\Facades\DB;

class GestionarBloques extends Component
{
    public $nombre;
    public $tipo_calculo = 'sumatoria'; // Valor por defecto
    public $bloqueSeleccionadoId = null;
    public $itemsAAsignar = []; // Para el wire:model del select múltiple

    protected $rules = [
        'nombre' => 'required|min:3|max:255|unique:bloques_clasificacion_asistente,nombre',
        'tipo_calculo' => 'required|in:sumatoria,promedio',
    ];

    public function render()
    {
        // Forzar consulta fresca de bloques con conteo de clasificaciones
        $bloques = BloqueClasificacionAsistente::withCount('clasificaciones')->get();

        $bloqueSeleccionado = null;
        $itemsAsignados = collect();
        $itemsDisponibles = collect(); 

        if ($this->bloqueSeleccionadoId) {
            $bloqueSeleccionado = BloqueClasificacionAsistente::find($this->bloqueSeleccionadoId);
            if ($bloqueSeleccionado) {
                // Items asignados al bloque actual
                $itemsAsignados = $bloqueSeleccionado->clasificaciones()->orderBy('nombre')->get();
                
                // Items disponibles: TODAS las clasificaciones que NO están en este bloque.
                // Permitimos que estén en OTROS bloques (relación muchos a muchos real).
                $itemsEnBloqueIds = $itemsAsignados->pluck('id');
                $itemsDisponibles = ClasificacionAsistente::whereNotIn('id', $itemsEnBloqueIds)->orderBy('nombre')->get();
            }
        }

        return view('livewire.bloque-clasificacion.gestionar-bloques', [
            'bloques' => $bloques,
            'bloqueSeleccionado' => $bloqueSeleccionado,
            'itemsAsignados' => $itemsAsignados,
            'itemsDisponibles' => $itemsDisponibles
        ]);
    }

    public function crearBloque()
    {
        $this->validate();

        BloqueClasificacionAsistente::create([
            'nombre' => $this->nombre,
            'tipo_calculo' => $this->tipo_calculo
        ]);

        $this->reset(['nombre', 'tipo_calculo']);
        $this->dispatch('refresh-select2');
        $this->dispatch('swal:success', ['title' => 'Éxito', 'text' => 'Bloque creado correctamente']);
    }

    public function eliminarBloque($id)
    {
        $bloque = BloqueClasificacionAsistente::find($id);
        if ($bloque) {
            $bloque->delete();
            if ($this->bloqueSeleccionadoId == $id) {
                $this->bloqueSeleccionadoId = null;
            }
            $this->dispatch('refresh-select2');
            $this->dispatch('swal:success', ['title' => 'Eliminado', 'text' => 'Bloque eliminado correctamente']);
        }
    }

    public function seleccionarBloque($id)
    {
        $this->bloqueSeleccionadoId = $id;
        $this->itemsAAsignar = [];
        $this->dispatch('refresh-select2');
    }

    public function resetSeleccion()
    {
        $this->bloqueSeleccionadoId = null;
        $this->itemsAAsignar = [];
        $this->dispatch('refresh-select2');
    }

    public function asignarItems()
    {
        if (!$this->bloqueSeleccionadoId || empty($this->itemsAAsignar)) {
            $this->dispatch('swal:error', ['title' => 'Error', 'text' => 'Debe seleccionar items']);
            return;
        }

        $bloque = BloqueClasificacionAsistente::find($this->bloqueSeleccionadoId);
        
        if ($bloque) {
            try {
                // Usamos attach para agregar relaciones N:M
                $bloque->clasificaciones()->attach($this->itemsAAsignar);
                
                $this->itemsAAsignar = []; 
                $this->dispatch('refresh-select2');
                $this->dispatch('swal:success', ['title' => 'Asignados', 'text' => 'Items asignados correctamente']);
            } catch (\Exception $e) {
                $this->dispatch('swal:error', ['title' => 'Error', 'text' => 'Error al asignar items.']);
            }
        }
    }

    public function desvincularItem($itemId)
    {
        if (!$this->bloqueSeleccionadoId) return;

        $bloque = BloqueClasificacionAsistente::find($this->bloqueSeleccionadoId);
        if ($bloque) {
            $bloque->clasificaciones()->detach($itemId);
            $this->dispatch('refresh-select2');
            $this->dispatch('swal:success', ['title' => 'Desvinculado', 'text' => 'Item desvinculado correctamente']);
        }
    }
}
