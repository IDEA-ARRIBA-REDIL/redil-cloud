<?php

namespace App\Livewire\Reuniones;

use App\Helpers\Helpers;
use Livewire\Component;
use App\Models\Reunion;


class ReunionesParaBusqueda extends Component
{
  public $busqueda = '';
  // public $reuniones = [];
  public $verInputBusqueda = true;
  public $verListaBusqueda = false;
  public $reunionSeleccionada = null;

  // Variables que enviamos por parametro
  public $mostrar = true;
  public $class = '';
  public $label = '';
  public $nameId = '';
  public $placeholder = '';
  public $rolActivo;

  public function mount($reunionId = null)
  {
    $this->rolActivo = auth()
      ->user()
      ->roles()
      ->wherePivot('activo', true)
      ->first();

    if ($reunionId) {
      $this->seleccionarReunion($reunionId, false);
    } else {
      $oldReunion = old($this->nameId);
      if ($oldReunion) {
        $this->seleccionarReunion($oldReunion, false);
      }
    }

    if ($this->reunionSeleccionada) {
    }
    // $oldReunion = old($this->nameId);
    // if ($oldReunion) {
    //   $this->reunionSeleccionada = Reunion::find($oldReunion);
    //   $this->verInputBusqueda = false;
    //   $this->verListaBusqueda = false;
    // }
  }

  public function render()
  {
    $reuniones = Reunion::query();
    // Cargar las reuniones de igual manera como en reuniones controller
    if (!$this->rolActivo->hasPermissionTo('reuniones.lista_reuniones_todas')) {
      $user = auth()->user();
      $sedesEncargadasArray = $user->sedesEncargadas('array');
      $reuniones = Reunion::whereIn('sede_id', $sedesEncargadasArray)->get();
    }
    // Implementar el buscar por palabra
    if ($this->busqueda && strlen($this->busqueda) >= 3) {
      $buscar = htmlspecialchars($this->busqueda);
      $buscar = Helpers::sanearStringConEspacios($buscar);
      $buscar = str_replace(["'"], '', $buscar);

      $reuniones->whereRaw("translate(nombre,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜ','aeiouAEIOUaeiouAEIOU') ILIKE ?", ["%$buscar%"]);
    }

    // Aplicar paginación
    $reuniones = $reuniones->select('id', 'nombre', 'sede_id', 'hora', 'dia')->take(10)->get();

    return view('livewire.reuniones.reuniones-para-busqueda', [
      'reuniones' => $reuniones,
    ]);
  }

  public function desplegarListaBusqueda()
  {
    if (!$this->reunionSeleccionada)
      $this->verListaBusqueda = true;
  }

  public function ocultarListaBusqueda()
  {
    if (!$this->reunionSeleccionada)
      $this->verListaBusqueda = false;
  }

  public function quitarSeleccion()
  {
    $this->reunionSeleccionada = null;
    $this->verInputBusqueda = true;
    $this->verListaBusqueda = true;
    $this->busqueda = '';
    $this->dispatch('anularPrecargado', data: $this->reunionSeleccionada);
  }

  public function seleccionarReunion($reunionId, $informacionPrecargada = false)
  {
    $this->reunionSeleccionada = Reunion::find($reunionId);
    $this->verInputBusqueda = false;
    $this->verListaBusqueda = false;
    if ($informacionPrecargada) {
      $this->dispatch('informacionPrecargada', data: $this->reunionSeleccionada);
    }
  }
}
