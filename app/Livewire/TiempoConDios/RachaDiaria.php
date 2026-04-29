<?php

namespace App\Livewire\TiempoConDios;

use Livewire\Component;

class RachaDiaria extends Component
{
    public $rachaSemanal = null;

    public $cantidadRachaSemanal = null;

    public $diaDeLaSemana;

    public $largoLinea = '60px';

    public $ocultarDispositivosMoviles = false;

    public $cantidadRachaDiaria = 0;

    public $cantidadTotalTiempoConDios = 0;

    public function mount()
    {
        $user = auth()->user();
        $this->rachaSemanal = $user->rachaSemanalActual();
        $this->cantidadRachaDiaria = $user->cantidadRachaDiaria();
        $this->cantidadTotalTiempoConDios = $user->tiemposConDios()->count();
    }

    public function render()
    {
        return view('livewire.tiempo-con-dios.racha-diaria');
    }
}
