<?php

namespace App\Livewire\TiempoConDios;

use Livewire\Component;

class RachaAnimacion extends Component
{
    public $cantidadRachaDiaria = 0;
    public $cantidadTotalTiempoConDios = 0;
    public $ancho = '180px';
    public $alto = '180px';

    public function mount()
    {
        $user = auth()->user();
        $this->cantidadRachaDiaria = $user->cantidadRachaDiaria();
        $this->cantidadTotalTiempoConDios = $user->tiemposConDios()->where('estado', 'completado')->count();
    }

    public function render()
    {
        return view('livewire.tiempo-con-dios.racha-animacion');
    }
}
