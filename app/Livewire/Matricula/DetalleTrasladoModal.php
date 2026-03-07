<?php

namespace App\Livewire\Matricula;

use Livewire\Component;
use App\Models\TrasladoMatriculaLog;
use Livewire\Attributes\On;

class DetalleTrasladoModal extends Component
{
    public $showModal = false;
    public ?TrasladoMatriculaLog $traslado = null;

    #[On('abrirModalDetalleTraslado')]
    public function openModal($logId)
    {
        // Cargamos el registro del log con todas las relaciones que necesitamos para mostrar los detalles
        $this->traslado = TrasladoMatriculaLog::with([
            'user:id,primer_nombre,primer_apellido',
            'horarioOrigen.horarioBase.aula.sede:id,nombre',
            'horarioDestino.horarioBase.aula.sede:id,nombre'
        ])->find($logId);

        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->traslado = null;
    }

    public function render()
    {
        return view('livewire.matricula.detalle-traslado-modal');
    }
}