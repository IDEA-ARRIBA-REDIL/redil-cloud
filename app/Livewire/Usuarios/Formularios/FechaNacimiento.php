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
    if ($this->formulario->validar_edad) {
      if (!$this->fecha) {
        return;
      }

      $edad = Carbon::parse($this->fecha)->age;

      // 1. Validar si la edad está dentro del rango del formulario actual
      $esEdadValida = true;
      if ($this->formulario->edad_minima !== null && $edad < $this->formulario->edad_minima) {
        $esEdadValida = false;
      }
      if ($this->formulario->edad_maxima !== null && $edad > $this->formulario->edad_maxima) {
        $esEdadValida = false;
      }

      if ($esEdadValida) {
        $this->dispatch('desbloqueoBtnGuardar');
        return;
      }

      // 2. Si la edad está fuera de rango, buscar si existen otros formularios compatibles
      $otrosFormularios = FormularioUsuario::where('tipo_formulario_id', $this->formulario->tipo->id)
        ->where('edad_minima', '<=', $edad)
        ->where('edad_maxima', '>=', $edad)
        ->where('id', '!=', $this->formulario->id)
        ->get();

      if ($otrosFormularios->count() > 0) {
        $html = '';
        foreach ($otrosFormularios as $otroFormulario) {
          if ($this->usuario) {
            $ruta = route('usuario.modificar', [$otroFormulario, $this->usuario]);
          } elseif ($this->formulario->tipo && $this->formulario->tipo->es_formulario_exterior) {
            $ruta = route('usuario.nuevoExterior', $otroFormulario);
          } else {
            $ruta = route('usuario.nuevo', $otroFormulario);
          }

          $color = $otroFormulario->color ?: '#138848';
          $icono = $otroFormulario->icono ?: 'ti ti-forms';
          $titulo = e($otroFormulario->label ?? $otroFormulario->titulo);
          $descripcion = $otroFormulario->descripcion ? e($otroFormulario->descripcion) : '<span class="text-muted fst-italic">Sin descripción disponible.</span>';

          $html .= '<div class="col-12">
            <div class="card border p-3 p-md-4 shadow-none" style="
              border-radius: 16px;
              background: #f8fafc;
              border-color: #e2e8f0 !important;
              transition: all 0.25s ease;
            ">
              <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap flex-md-nowrap">
                <div class="d-flex align-items-center gap-3 gap-md-4">
                  <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0" style="
                    width: 58px;
                    height: 58px;
                    background-color: '.$color.'22;
                    color: '.$color.';
                  ">
                    <i class="'.$icono.'" style="font-size: 1.85rem;"></i>
                  </div>
                  <div>
                    <h5 class="fw-semibold text-black mb-1" style="font-size: 1.1rem; letter-spacing: -0.3px;">
                      '.$titulo.'
                    </h5>
                    <p class="mb-0 text-black small">
                      '.$descripcion.'
                    </p>
                  </div>
                </div>
                <div class="flex-shrink-0 ms-auto ms-md-0">
                  <a href="'.$ruta.'" class="btn rounded-pill px-4 py-2 fw-semibold d-inline-flex align-items-center gap-2 text-white" style="
                    background-color: #138848;
                    border: none;
                    font-size: 0.88rem;
                    box-shadow: 0 4px 12px rgba(19, 136, 72, 0.25);
                  ">
                    Continuar <i class="ti ti-arrow-right ti-xs"></i>
                  </a>
                </div>
              </div>
            </div>
          </div>';
        }

        $this->fecha = '';
        $this->dispatch(
          'abrirModalCambioDeFormulario',
          nombreModal: 'modalCambioDeFormulario',
          html: $html
        );
      } else {
        $this->fecha = '';
        $this->dispatch(
          'msn',
          msnIcono: 'info',
          msnTitulo: '¡Ups!',
          msnTexto: $this->formulario->edad_mensaje_error ?: 'La edad ingresada no es permitida para este formulario.'
        );
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
