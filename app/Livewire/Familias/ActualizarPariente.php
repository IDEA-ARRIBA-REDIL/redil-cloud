<?php

namespace App\Livewire\Familias;

use Livewire\Component;

use App\Models\User;
use App\Models\Grupo;
use App\Models\IntegranteGrupo;
use App\Models\TipoUsuario;
use App\Models\ParienteUsuario;
use App\Models\Configuracion;
use App\Models\TipoParentesco;
use App\Models\CampoExtra;
use App\Models\CampoInformeExcel;
use App\Models\PasoCrecimiento;
use Carbon\Carbon;

use App\Helpers\Helpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Validation\Rules\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;

class ActualizarPariente extends Component
{
  public $relacionIdGeneral = '',
    $tiposParentesco = [], // este es un array para cargar los tipos de parentesco en el modal
    $nombreUsuario = null, /// esta es una variable que carga en el modal
    $prueba = "hola"; /// este es el id de la relacion de la tabla parientes_usuarios

  public $actualizarTipoParentesco, // esta variable guarda el valor del select del modal que se abre en la vista
    $actualizarResponsabilidad; /// esta variable guarda el valor del select del modal que se abre en la vista

  public function mount()
  {
    $this->tiposParentesco = TipoParentesco::get();
  }

  #[On('abrirModalActualizarPariente')]
  public function abrirModalActualizarPariente($relacionId, $usuarioId)
  {

    ///identifico las dos relaciones familiares la relacion1 siempre es del usario seleccionado, la relacion2 es la del pariente en el listado de relaciones familiares
    $relacion1 = ParienteUsuario::find($relacionId);
    $relacion2 = ParienteUsuario::where('user_id', $relacion1->pariente_user_id)->where('pariente_user_id', $relacion1->user_id)->first();

    /// luego identificamos el tipo de relacion y le asignamos un valor
    $responsabilidad = 1;
    if ($relacion1->es_el_responsable == true && $relacion2->es_el_responsable == false)
      $responsabilidad = 2;
    elseif ($relacion1->es_el_responsable == false && $relacion2->es_el_responsable == true)
      $responsabilidad = 3;

    /// aqui lo que hago es asignar las variables que necesito usar en el modal que voy a abrir
    $this->actualizarTipoParentesco = $relacion1->tipo_pariente_id;
    $this->actualizarResponsabilidad = $responsabilidad;
    $this->relacionIdGeneral = $relacionId;
    $this->nombreUsuario = User::find($usuarioId)->nombre(3);
    /// aqui lo que hago es ejecutar el script que abre el modal
    $this->dispatch('abrirModal', nombreModal: 'modalActualizarPariente');
  }

  ///esta es la funcion que ejecuta el formulario del modal
  public function updateParentesco()
  {

    ///identifico las dos relaciones familiares la relacion1 siempre es del usario seleccionado, la relacion2 es la del pariente en el listado de relaciones familiares
    $relacion1 = ParienteUsuario::find($this->relacionIdGeneral);
    $relacion2 = ParienteUsuario::where('user_id', $relacion1->pariente_user_id)->where('pariente_user_id', $relacion1->user_id)->first();

    /// establecemos los tipos de parentesco que hay entre los usuarios
    $tiposParentescoSeleccionado = TipoParentesco::find($this->actualizarTipoParentesco);

    $tipoParentescoPariente = $tiposParentescoSeleccionado; // hija

    if (isset($tiposParentescoSeleccionado->relacionado_con)) {
      $tipoParentescoPariente = TipoParentesco::find($tiposParentescoSeleccionado->relacionado_con); // padre
    }

    /// luego identificamos el tipo de relacion y le asignamos un valor
    $responsabilidad = $this->actualizarResponsabilidad;
    $asistenteResponsable = false;
    $parienteResponsable = false;

    if ($responsabilidad == 2)
      $asistenteResponsable = true;
    elseif ($responsabilidad == 3)
      $parienteResponsable = true;

    /// aqui actualizo ambas relaciones
    $relacion1->tipo_pariente_id = $tiposParentescoSeleccionado->id;
    $relacion1->es_el_responsable = $asistenteResponsable;
    $relacion1->save();

    $relacion2->tipo_pariente_id = $tipoParentescoPariente->id;
    $relacion2->es_el_responsable =  $parienteResponsable;
    $relacion2->save();

    return redirect(request()->header('Referer'))->with('success', 'Se actualizó correctamente tu relación familiar');
  }

  public function render()
  {
    return view('livewire.familias.actualizar-pariente');
  }
}
