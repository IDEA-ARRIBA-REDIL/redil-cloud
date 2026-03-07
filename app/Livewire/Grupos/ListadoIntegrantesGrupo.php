<?php

namespace App\Livewire\Grupos;

use App\Helpers\Helpers;
use App\Models\Configuracion;
use App\Models\User;
use Livewire\Component;

class ListadoIntegrantesGrupo extends Component
{
  public $grupo;
  public $configuracion, $rolActivo;
  public $busqueda;

  public function mount()
  {
    $this->configuracion = Configuracion::find(1);

    $this->rolActivo = auth()
      ->user()
      ->roles()
      ->wherePivot('activo', true)
      ->first();
  }

  public function render()
  {

    $integrantes = $this->grupo->asistentes()->get();

    if ($this->busqueda) {
      $buscar = htmlspecialchars($this->busqueda);
      $buscar = Helpers::sanearStringConEspacios($buscar);
      $buscar = str_replace(["'"], '', $buscar);
      $buscar_array = explode(' ', $buscar);

      foreach ($buscar_array as $palabra) {
        $integrantes = $integrantes->filter(function ($integrantes) use ($palabra) {
            $respuesta  = false !== stristr(Helpers::sanearStringConEspacios($integrantes->primer_nombre), $palabra) ||
            false !== stristr(Helpers::sanearStringConEspacios($integrantes->segundo_nombre), $palabra) ||
            false !== stristr(Helpers::sanearStringConEspacios($integrantes->primer_apellido), $palabra) ||
            false !== stristr(Helpers::sanearStringConEspacios($integrantes->segundo_apellido), $palabra) ||
            false !== stristr(Helpers::sanearStringConEspacios($integrantes->identificacion), $palabra) ||
            false !== stristr(Helpers::sanearStringConEspacios($integrantes->email), $palabra) ||
            false !== stristr(Helpers::sanearStringConEspacios($integrantes->id), $palabra);

            return $respuesta;
        });
      }
    }

    return view('livewire.grupos.listado-integrantes-grupo',
    [
      'integrantes' => $integrantes
    ]);
  }
}
