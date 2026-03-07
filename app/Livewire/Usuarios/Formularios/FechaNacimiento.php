<?php

namespace App\Livewire\Usuarios\Formularios;

use App\Models\FormularioUsuario;
use Carbon\Carbon;
use Livewire\Component;

use Livewire\Attributes\On;

class FechaNacimiento extends Component
{
  public $formulario;
  public $usuario;
  public $fechaDefault;
  public $respuesta = '';
  public $fecha = '';
  public $mostrarError = false;
  public $msnError = '';

  public $class = '';
  public $label = '';
  public $nameId = '';

  public function mount()
  {
    $this->fecha = $this->usuario ? $this->usuario->fecha_nacimiento : '' ;

    if(old($this->nameId)!='')
    $this->fecha = old($this->nameId);

    $edad = Carbon::parse($this->fecha)->age;
   /* if($this->fecha)
    {
      if($edad < $this->formulario->edad_minima || $edad > $this->formulario->edad_maxima)
      {
        $this->fecha = '';
      }
    }*/
  }

  public function validarFecha()
  {
    if($this->formulario->validar_edad)
    {
      $edad = Carbon::parse($this->fecha)->age;

      $otrosFormularios = FormularioUsuario::where('tipo_formulario_id', $this->formulario->tipo->id)
      ->where('edad_minima', '<=', $edad)
      ->where('edad_maxima', '>=', $edad)
      ->where('id','!=', $this->formulario->id)
      ->get();

      if($this->fecha)
      {
        if($otrosFormularios->count() > 0)
        {

          $html = '';
          foreach($otrosFormularios as $otroFormulario)
          {
            $html = '<div class="col-12 mb-6">
              <div class="form-check custom-option custom-option-basic">
                <a href="'.route('usuario.modificar', [$otroFormulario, $this->usuario]).'">
                  <label class="form-check-label custom-option-content">
                    <span class="custom-option-header">
                      <span class="h6 mb-0 d-flex align-items-center"><i class="ti ti-forms me-1"></i>'.$otroFormulario->titulo.'</span>
                    </span>
                    <span class="custom-option-body">
                      '.$otroFormulario->descripcion.'
                    </span>
                  </label>
                </a>
              </div>
            </div>';
          }

          $this->fecha = '';
          $this->dispatch(
            'abrirModalCambioDeFormulario',
            nombreModal: 'modalCambioDeFormulario',
            html: $html
          );
            /*$this->dispatch(
              'msn',
              msnIcono: 'info',
              msnTitulo: '¡Yes!',
              msnTexto: "si hay formularios"
            );*/
        }else{
          if($edad < $this->formulario->edad_minima || $edad > $this->formulario->edad_maxima){
            $this->fecha = '';
            $this->dispatch(
              'msn',
              msnIcono: 'info',
              msnTitulo: '¡Ups!',
              msnTexto:  $this->formulario->edad_mensaje_error
            );
          }else{
            $this->dispatch('desbloqueoBtnGuardar');
          }
        }
      }
    }
  }

  public function bloquearBtnGuardar(){
    $this->dispatch('bloqueoBtnGuardar');
  }

  #[On('mostrarMensajeError')]
  public function mostrarMensajeError($mostrarError, $msnError)
  {
    $this->mostrarError = $mostrarError;
    $this->msnError = $msnError;
  }

  public function render()
  {
      return view('livewire.usuarios.formularios.fecha-nacimiento');
  }
}
