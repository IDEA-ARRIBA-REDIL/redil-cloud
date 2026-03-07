<?php

namespace App\Livewire\FormulariosParaUsuarios;

use App\Models\CampoFormularioUsuario;
use Livewire\Component;

use App\Helpers\Helpers;
use Livewire\Attributes\On;

class SelectorDeCampos extends Component
{

  public $busqueda = '';
  public $verListaBusqueda = false;
  public $verInputBusqueda = true;
  public $campoSeleccionado = null;
  public $mostrarError = false;
  public $msnError = '';

  public $formulario;
  public $prueba ="no";


  public function seleccionarCampo($campoId)
  {
    $this->campoSeleccionado = CampoFormularioUsuario::find($campoId);
    $this->verInputBusqueda = false;
    $this->verListaBusqueda = false;
    $this->dispatch('obtenerCampoSeleccionado', $this->campoSeleccionado->id)->to(GestionarSeccionesYCampos::class);

    $this->prueba = $this->campoSeleccionado->id;
  }

  #[On('quitarSeleccion')]
  public function quitarSeleccion()
  {
    $this->campoSeleccionado = null;
    $this->verInputBusqueda = true;
    $this->verListaBusqueda = false;
    $this->busqueda = '';
    $this->dispatch('obtenerCampoSeleccionado', null)->to(GestionarSeccionesYCampos::class);
  }

  public function desplegarListaBusqueda()
  {
    if(!$this->campoSeleccionado)
    $this->verListaBusqueda = true;
  }

  public function ocultarListaBusqueda()
  {
    if(!$this->campoSeleccionado)
    $this->verListaBusqueda = false;
  }

  #[On('mostrarMensajeError')]
  public function mostrarMensajeError($mostrarError, $msnError)
  {
    $this->mostrarError = $mostrarError;
    $this->msnError = $msnError;
  }

  public function render()
  {
    $campos  = null;
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

      $arrayIdsCamposUsados = CampoFormularioUsuario::leftJoin('campo_seccion_formulario_usuario', 'campos_formulario_usuario.id', '=', 'campo_seccion_formulario_usuario.campo_id')
      ->leftJoin('secciones_formulario_usuario', 'campo_seccion_formulario_usuario.seccion_id', '=', 'secciones_formulario_usuario.id')
      ->where('secciones_formulario_usuario.formulario_usuario_id', '=', $this->formulario->id)
      ->select('campos_formulario_usuario.id')
      ->pluck('campos_formulario_usuario.id')
      ->toArray();

      $campos = CampoFormularioUsuario::whereRaw($sql_buscar)
      ->whereNotIn('id', $arrayIdsCamposUsados)
      ->orderBy('nombre','asc')
      ->take(10)
      ->select('id','nombre')
      ->get();

    return view('livewire.formularios-para-usuarios.selector-de-campos', ['campos' => $campos]);
  }
}
