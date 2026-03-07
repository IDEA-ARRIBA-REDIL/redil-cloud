<?php

namespace App\Livewire\TiempoConDios;

use App\Models\CampoTiempoConDios;
use App\Models\TipoCampoTiempoConDios;
use Livewire\Component;

use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\On;

class ValidarFormulario extends Component
{
  public $ejemplo = "hola";
  public $errores;


   #[On('validar')]
    public function validar($seccionId, $dataSeccion)
    {

      $validacion = [];
      $camposTipoInputIds = TipoCampoTiempoConDios::where('es_input',true)
      ->select('id')
      ->pluck('id')
      ->toArray();

      $campos = CampoTiempoConDios::where('seccion_tiempo_con_dios_id','=', $seccionId)
      ->whereIn('tipo_campo_tiempo_con_dios_id', $camposTipoInputIds)
      ->select('campos_tiempo_con_dios.*')
      ->get();


      // seccion comprobacion campos
      foreach ($campos as $campo) {
        $validarCampo = [];
        $campo->requerido ? array_push($validarCampo, 'required') : '';
        $validacion = array_merge($validacion, [$campo->name_id => $validarCampo]);
      }

      $this->ejemplo = $validacion;
      $validator = Validator::make($dataSeccion, $validacion);

      if ($validator->fails()) {
        $this->errores = $validator->errors()->toArray();
        $this->dispatch('validacionFormulario', resultado: false, errores:$this->errores, data: $dataSeccion );
      } else {
        $this->errores = "";
        $this->dispatch('validacionFormulario', resultado: true, errores:$this->errores, data: $dataSeccion,  seccionId: $seccionId);
      }
    }


    public function render()
    {
        return view('livewire.tiempo-con-dios.validar-formulario');
    }
}
