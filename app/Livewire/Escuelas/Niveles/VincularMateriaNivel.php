<?php

namespace App\Livewire\Escuelas\Niveles;

use App\Models\Materia;
use App\Models\NivelAgrupacion;
use App\Models\NivelAgrupacionMateria;
use Livewire\Component;

class VincularMateriaNivel extends Component
{
    public $nivel;
    public $materia_id;
    public $es_obligatoria = true; // Por defecto true

    protected $rules = [
        'materia_id' => 'required|exists:materias,id',
        'es_obligatoria' => 'boolean',
    ];

    public function mount(NivelAgrupacion $nivel)
    {
        $this->nivel = $nivel;
    }

    public function vincular()
    {
        $this->validate();

        // Validar si ya existe
        $existe = NivelAgrupacionMateria::where('nivel_agrupacion_id', $this->nivel->id)
            ->where('materia_id', $this->materia_id)
            ->exists();

        if ($existe) {
            $this->addError('materia_id', 'Esta materia ya está vinculada al nivel.');
            return;
        }

        // Obtener el último orden
        $maxOrden = NivelAgrupacionMateria::where('nivel_agrupacion_id', $this->nivel->id)->max('orden') ?? 0;

        NivelAgrupacionMateria::create([
            'nivel_agrupacion_id' => $this->nivel->id,
            'materia_id' => $this->materia_id,
            'es_obligatoria' => $this->es_obligatoria,
            'orden' => $maxOrden + 1,
        ]);

        $this->reset(['materia_id', 'es_obligatoria']);
        $this->dispatch('materiaVinculada'); // Actualizar la lista principal
        $this->dispatch('msn', ['icon' => 'success', 'title' => 'Materia vinculada exitosamente.']);
        // Cerrar modal (requiere script en vista o dispatch browser event)
        $this->dispatch('cerrarModalVincular');
    }

    public function render()
    {
        // Obtener materias que NO están ya en el nivel
        $materiasDisponibles = Materia::where('escuela_id', $this->nivel->escuela_id)
            ->whereDoesntHave('nivelesAgrupacion', function ($query) {
                $query->where('nivel_agrupacion_id', $this->nivel->id);
            })
            ->orderBy('nombre')
            ->get();

        return view('livewire.escuelas.niveles.vincular-materia-nivel', [
            'materiasDisponibles' => $materiasDisponibles
        ]);
    }
}
