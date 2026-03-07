<?php

namespace App\Livewire\TiempoConDios;

use Livewire\Component;

class RachaSemanal extends Component
{
  public $rachaSemanal = 0;
  public $tamaño = '80px';
  public $formato = 'basico';

  public function mount()
  {
    $usuario =  auth()->user();
    $this->rachaSemanal = $usuario->obtenerRachaSemanalActual();
  }

  public function render()
  {
    return view('livewire.tiempo-con-dios.racha-semanal');
  }
}
