<?php

namespace App\Livewire\TiempoConDios;

use Livewire\Component;

class RachaDiaria extends Component
{
  public $rachaSemanal = null, $cantidadRachaSemanal = null, $diaDeLaSemana, $largoLinea = "60px", $ocultarDispositivosMoviles = false;

  public function mount()
  {
    $user = auth()->user();
    $this->rachaSemanal = $user->rachaSemanalActual();

  }
    public function render()
    {
        return view('livewire.tiempo-con-dios.racha-diaria');
    }
}
