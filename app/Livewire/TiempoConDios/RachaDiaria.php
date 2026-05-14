<?php

namespace App\Livewire\TiempoConDios;

use Livewire\Component;

class RachaDiaria extends Component
{
    public $rachaSemanal = null;
    public $diaDeLaSemana;
    public $largoLinea = '60px';
    public $ocultarDispositivosMoviles = false;

    public function mount()
    {
        $user = auth()->user();
        $this->rachaSemanal = $user->rachaSemanalActual();
        $this->diaDeLaSemana = date('N'); // Aseguramos que tenga el valor del día actual
    }

    public function render()
    {
        return view('livewire.tiempo-con-dios.racha-diaria');
    }
}
