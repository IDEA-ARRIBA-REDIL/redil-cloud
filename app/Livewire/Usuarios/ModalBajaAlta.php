<?php

namespace App\Livewire\Usuarios;

use App\Models\Configuracion;
use App\Models\IntegranteGrupo;
use App\Models\ReporteBajaAlta;
use App\Models\TipoBajaAlta;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\Attributes\Validate;

class ModalBajaAlta extends Component
{
  public $titulo = "", $respuesta = " bn";
  public $usuarioId, $tipo, $grupoId, $redirect;
  public $motivosBajasAltas;

  #[Validate('required')]
  public $motivo;
  public $observacion;

  public function mount()
  {
    $this->motivosBajasAltas = collect();
  }

  #[On('abrirModalBajaAlta')]
  public function abrirModalBajaAlta($usuarioId, $tipo)
  {
    $usuario = User::withTrashed()->find($usuarioId);
    $this->tipo = $tipo;

    $this->titulo = $tipo == 'alta' ?  "Dar de alta a <b>" . $usuario->nombre(3) . "</b>" : "Dar de baja a <b>" . $usuario->nombre(3) . "</b>";
    $this->usuarioId = $usuario->id;

    if ($this->tipo == 'alta')
      $this->motivosBajasAltas = TipoBajaAlta::where('dado_alta', true)->get();
    elseif ($this->tipo == 'baja')
      $this->motivosBajasAltas = TipoBajaAlta::where('dado_baja', true)->get();

    $this->dispatch('abrirModal', nombreModal: 'modaBajaAlta');
  }

  #[On('comprobarSiTieneRegistros')]
  public function comprobarSiTieneRegistros($usuarioId)
  {
    $usuario = User::find($usuarioId);

    $tieneRegistros = 1;
    $htmlRegistros = '
      <p>No es recomendado eliminar a <b>' . $usuario->nombre(3) . '</b> debido a que tiene registros en el sistema</p>
      <ul class="text-start">
      <li> Ha sido reportado en las reuniones </li>
      <li> Tiene matriculas </li>
      </ul>
      <p>¿Deseas darle de baja?</p>
      ';
    // Falta código para determinar si tiene ofrandas, reportes de grupo, inscripciones, matriculas etc...


    if ($tieneRegistros > 0) {
      // Recomienda dar de baja y no eliminar

      $this->dispatch(
        'msnTieneRegistros',
        msnIcono: 'warning',
        msnTitulo: '¡Precaución!',
        msnTexto: $htmlRegistros,
        id: $usuario->id
      );
    } else {
      // Preguntas si esta seguro que quiere eliminar

    }



    //$this->dispatch('abrirModal', nombreModal: 'modaBajaAlta');

  }

  #[On('confirmarEliminacion')]
  public function confirmarEliminacion($usuarioId)
  {

    $usuario = User::withTrashed()->find($usuarioId);
    $this->dispatch(
      'msnConfirmarEliminacion',
      msnIcono: 'warning',
      msnTitulo: '¿Estás seguro que deseas eliminar a <b>' . $usuario->nombre(3) . '</b>?',
      msnTexto: 'Esta acción no es reversible.',
      id: $usuario->id
    );
  }

  public function eliminacionForzada($usuarioId)
  {
    $usuario = User::withTrashed()->find($usuarioId);
    $configuracion = Configuracion::find(1);

    // Elimino las fotos y archivos
    if ($configuracion->version == 1) {
      // Elimino los archivos
      Storage::delete('public/' . $configuracion->ruta_almacenamiento . '/archivos' . '/' . $usuario->archivo_a);
      Storage::delete('public/' . $configuracion->ruta_almacenamiento . '/archivos' . '/' . $usuario->archivo_b);
      Storage::delete('public/' . $configuracion->ruta_almacenamiento . '/archivos' . '/' . $usuario->archivo_c);
      Storage::delete('public/' . $configuracion->ruta_almacenamiento . '/archivos' . '/' . $usuario->archivo_d);

      // Elimino foto
      Storage::delete('public/' . $configuracion->ruta_almacenamiento . '/img/usuarios/foto-usuario/' . $usuario->archivo_d);
    } elseif ($configuracion->version == 2) {
    }

    // Eliminar parentezco
    $usuario->parientesDelUsuario()->detach();
    $usuario->usuariosDelPariente()->detach();
    IntegranteGrupo::where("user_id", $usuario->id)->delete();
    $usuario->forceDelete();

    return redirect('/usuarios')->with('success', "<b>" . $usuario->nombre(3) . "</b> fue eliminado con éxito.");
  }

  public function editarBajaAlta($usuarioId, $tipo)
  {
    $this->validate();
    $usuario = User::withTrashed()->find($usuarioId);
    $reporte = new ReporteBajaAlta();
    $reporte->observaciones = $this->observacion;
    $reporte->tipo_baja_alta_id = $this->motivo;
    $reporte->user_id = $usuario->id;
    $reporte->fecha = Carbon::now()->format('Y-m-d');
    if ($tipo == 'baja') {
      $reporte->dado_baja = TRUE;
      $usuario->delete();
      $mensaje = "<b>" . $usuario->nombre(3) . "</b> fue dado de baja con éxito.";
    } elseif ($tipo == 'alta') {
      $reporte->dado_baja = FALSE;
      $usuario->restore();

      if (isset($this->grupoId) && $this->grupoId != "") {
        // Elimino las relaciones que haya tenido con otros grupos
        IntegranteGrupo::where('user_id', $usuario->id)->delete();

        // agrego al grupo
        $usuario->cambiarGrupo($this->grupoId);
      }

      $mensaje = "<b>" . $usuario->nombre(3) . "</b> fue dado de alta con éxito.";
    }
    $reporte->save();


    if (isset($redirect) && $redirect != "") {
    } else {
      return redirect(request()->header('Referer'))->with('success', $mensaje);
    }
  }

  public function render()
  {
    return view('livewire.usuarios.modal-baja-alta');
  }
}
