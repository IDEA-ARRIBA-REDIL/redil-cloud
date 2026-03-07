<?php

namespace App\Livewire\TiempoConDios;

use App\Models\Album;
use Livewire\Component;

use App\Helpers\Helpers;
use App\Models\Configuracion;
use Livewire\Attributes\On;

class SelectorDeAlbumes extends Component
{
  public $busqueda = '';
  public $verListaBusqueda = false;
  public $verInputBusqueda = true;
  public $albumSeleccionado = null;
  public $mostrarError = false;
  public $msnError = '';

  public $formulario;
  public $prueba ="no";
  public $configuracion = null;

  public function mount()
  {
    $this->configuracion = Configuracion::first();
  }

  #[On('seleccionarAlbum')]
  public function seleccionarAlbum($albumId)
  {
    $this->albumSeleccionado = Album::find($albumId);
    $this->verInputBusqueda = false;
    $this->verListaBusqueda = false;

    $this->dispatch('obtenerAlbumSeleccionado', $this->albumSeleccionado->id)->to(GestionarListaReproduccion::class);
  }

  #[On('quitarSeleccion')]
  public function quitarSeleccion()
  {
    $this->albumSeleccionado = null;
    $this->verInputBusqueda = true;
    $this->verListaBusqueda = false;
    $this->busqueda = '';
    $this->dispatch('obtenerAlbumSeleccionado', null)->to(GestionarListaReproduccion::class);
  }

  public function desplegarListaBusqueda()
  {
    if(!$this->albumSeleccionado)
    $this->verListaBusqueda = true;
  }

  public function ocultarListaBusqueda()
  {
    if(!$this->albumSeleccionado)
    $this->verListaBusqueda = false;
  }

  public function render()
  {
    $albumes  = null;
    $buscar = htmlspecialchars($this->busqueda);
    $buscar = Helpers::sanearStringConEspacios($buscar);
    $buscar = str_replace(["'"], '', $buscar);
    $buscar_array = explode(' ', $buscar);

    $c = 0;
    $sql_buscar = '';
    foreach ($buscar_array as $palabra) {
      if ($c != 0) {
        $sql_buscar .= ' AND ';
      }
      $sql_buscar .= "(translate (nombre,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜ','aeiouAEIOUaeiouAEIOU') ILIKE '%$palabra%'";
      $sql_buscar .= ')';
      $c++;
    }

    $albumes = Album::whereRaw($sql_buscar)
    ->orderBy('nombre','asc')
    ->take(10)
    ->select('id','nombre','imagen')
    ->get();

    return view('livewire.tiempo-con-dios.selector-de-albumes', [
      'albumes' => $albumes
    ]);
  }
}
