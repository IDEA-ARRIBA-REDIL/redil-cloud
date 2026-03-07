<?php

namespace App\Livewire\Consolidacion;

use Livewire\Component;
use App\Models\BloqueDashboardConsolidacion;
use App\Models\Sede;
use Illuminate\Support\Facades\DB;

class GestionarBloques extends Component
{
    public $nombre;
    public $bloqueSeleccionadoId = null;
    public $sedesAAsignar = []; // Para el wire:model del select múltiple

    protected $rules = [
        'nombre' => 'required|min:3|max:255|unique:bloques_dashboard_consolidacion,nombre',
    ];

    public function render()
    {
        // Forzar consulta fresca de bloques con conteo de sedes
        $bloques = BloqueDashboardConsolidacion::withCount('sedes')->get();

        $bloqueSeleccionado = null;
        $sedesAsignadas = collect();
        $sedesDisponibles = collect();

        if ($this->bloqueSeleccionadoId) {
            $bloqueSeleccionado = BloqueDashboardConsolidacion::find($this->bloqueSeleccionadoId);
            if ($bloqueSeleccionado) {
                // Usamos ->get() para asegurar que traemos los datos actuales de la DB
                $sedesAsignadas = $bloqueSeleccionado->sedes()->orderBy('nombre')->get();
                
                // Sedes disponibles: sedes que no están asignadas a ningún bloque
                $sedesOcupadasIds = DB::table('bloque_dashboard_consolidacion_sede')->pluck('sede_id');
                $sedesDisponibles = Sede::whereNotIn('id', $sedesOcupadasIds)->orderBy('nombre')->get();
            }
        }

        return view('livewire.consolidacion.gestionar-bloques', [
            'bloques' => $bloques,
            'bloqueSeleccionado' => $bloqueSeleccionado,
            'sedesAsignadas' => $sedesAsignadas,
            'sedesDisponibles' => $sedesDisponibles
        ]);
    }

    public function crearBloque()
    {
        $this->validate();

        BloqueDashboardConsolidacion::create(['nombre' => $this->nombre]);

        $this->reset('nombre');
        $this->dispatch('refresh-select2');
        $this->dispatch('swal:success', ['title' => 'Éxito', 'text' => 'Bloque creado correctamente']);
    }

    public function eliminarBloque($id)
    {
        $bloque = BloqueDashboardConsolidacion::find($id);
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
        $this->sedesAAsignar = [];
        $this->dispatch('refresh-select2');
    }

    public function resetSeleccion()
    {
        $this->bloqueSeleccionadoId = null;
        $this->sedesAAsignar = [];
        $this->dispatch('refresh-select2');
    }

    public function asignarSedes()
    {
        if (!$this->bloqueSeleccionadoId || empty($this->sedesAAsignar)) {
            $this->dispatch('swal:error', ['title' => 'Error', 'text' => 'Debe seleccionar sedes']);
            return;
        }

        $bloque = BloqueDashboardConsolidacion::find($this->bloqueSeleccionadoId);
        
        if ($bloque) {
            try {
                // Sincronizar sin desvincular las existentes (usando false en el segundo parámetro de sync o simplemente attach)
                // Usamos attach porque queremos agregar a las que ya están
                $bloque->sedes()->attach($this->sedesAAsignar);
                
                $this->sedesAAsignar = []; 
                $this->dispatch('refresh-select2');
                $this->dispatch('swal:success', ['title' => 'Asignadas', 'text' => 'Sedes asignadas correctamente']);
            } catch (\Exception $e) {
                $this->dispatch('swal:error', ['title' => 'Error', 'text' => 'Error al asignar sedes.']);
            }
        }
    }

    public function desvincularSede($sedeId)
    {
        if (!$this->bloqueSeleccionadoId) return;

        $bloque = BloqueDashboardConsolidacion::find($this->bloqueSeleccionadoId);
        if ($bloque) {
            $bloque->sedes()->detach($sedeId);
            $this->dispatch('refresh-select2');
            $this->dispatch('swal:success', ['title' => 'Desvinculada', 'text' => 'Sede desvinculada correctamente']);
        }
    }
}
