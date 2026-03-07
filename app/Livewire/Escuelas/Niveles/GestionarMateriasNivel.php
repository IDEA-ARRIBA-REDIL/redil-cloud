<?php

namespace App\Livewire\Escuelas\Niveles;

use App\Models\NivelAgrupacion;
use App\Models\NivelAgrupacionMateria;
use Livewire\Component;

class GestionarMateriasNivel extends Component
{
    public $nivel;

    protected $listeners = ['materiaVinculada' => '$refresh'];

    public function mount(NivelAgrupacion $nivel)
    {
        $this->nivel = $nivel;
    }

    public function desvincular($materiaId)
    {
        // Desvincular materia del nivel
        NivelAgrupacionMateria::where('nivel_agrupacion_id', $this->nivel->id)
            ->where('materia_id', $materiaId)
            ->delete();

        $this->dispatch('msn', ['icon' => 'success', 'title' => 'Materia desvinculada del nivel.']);
    }

    public function toggleObligatoria($materiaId)
    {
        $pivot = NivelAgrupacionMateria::where('nivel_agrupacion_id', $this->nivel->id)
            ->where('materia_id', $materiaId)
            ->first();

        if ($pivot) {
            $pivot->es_obligatoria = !$pivot->es_obligatoria;
            $pivot->save();
            $this->dispatch('msn', ['icon' => 'success', 'title' => 'Estado de obligatoriedad actualizado.']);
        }
    }

    // Método para actualizar el orden (si se implementa drag & drop más adelante)
    public function updateOrden($list)
    {
        foreach($list as $item) {
             NivelAgrupacionMateria::where('nivel_agrupacion_id', $this->nivel->id)
                ->where('materia_id', $item['value'])
                ->update(['orden' => $item['order']]);
        }
         $this->dispatch('msn', ['icon' => 'success', 'title' => 'Orden actualizado.']);
    }

    public function render()
    {
        // Obtener materias vinculadas a través de la relación definida en el modelo
        $materias = $this->nivel->materias()->orderBy('orden')->get();

        return view('livewire.escuelas.niveles.gestionar-materias-nivel', [
            'materias' => $materias
        ]);
    }
}
