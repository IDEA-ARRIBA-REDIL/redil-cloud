<?php

namespace App\Livewire\TiempoConDios;

use App\Models\CampoTiempoConDios;
use App\Models\TipoCampoTiempoConDios;
use Livewire\Component;
use App\Models\TiempoConDios;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;

use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\On;

class ValidarFormulario extends Component
{
  public $ejemplo = "hola";
  public $errores;


   #[On('validar')]
    public function validar($seccionId, $dataSeccion, $pasoActual = 1)
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

        // Autoguardado del borrador
        $user = auth()->user();
        $fechaHoy = Carbon::now()->format('Y-m-d');

        // Buscar o crear el registro en progreso para hoy
        $tiempoConDios = TiempoConDios::firstOrCreate(
            ['user_id' => $user->id, 'fecha' => $fechaHoy, 'estado' => 'en_progreso'],
            ['paso_actual' => 1]
        );

        // Guardar las respuestas del paso actual
        foreach ($campos as $campo) {
            if (isset($dataSeccion[$campo->name_id])) {
                $valorEncriptado = Crypt::encryptString($dataSeccion[$campo->name_id]);
                // Remover el valor anterior si existe, para no duplicar en pivot
                $tiempoConDios->campos()->detach($campo->id);
                $tiempoConDios->campos()->attach($campo->id, ['valor' => $valorEncriptado]);
            }
        }

        // Actualizar el paso actual en BD si el usuario está avanzando
        if ($pasoActual >= $tiempoConDios->paso_actual) {
            $tiempoConDios->update(['paso_actual' => $pasoActual + 1]);
        }

        $this->dispatch('validacionFormulario', resultado: true, errores:$this->errores, data: $dataSeccion,  seccionId: $seccionId);
      }
    }


    public function render()
    {
        return view('livewire.tiempo-con-dios.validar-formulario');
    }
}
