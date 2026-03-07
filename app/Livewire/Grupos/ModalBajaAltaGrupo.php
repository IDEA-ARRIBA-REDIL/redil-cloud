<?php

namespace App\Livewire\Grupos;

use App\Models\Configuracion;
use App\Models\Grupo;
use App\Models\IntegranteGrupo;
use App\Models\ReporteGrupoBajaAlta;
use App\Models\ServidorGrupo;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Carbon\Carbon;

use Illuminate\Support\Facades\Storage;

class ModalBajaAltaGrupo extends Component
{
  public $titulo = "", $respuesta =" bn";
  public $grupo;
  public $grupoId, $tipo;
  public $rolActivo;

  #[Validate('required')]
  public $motivo;
  public $observaciones, $grupoTraspasoId;

  public function mount()
  {
    $this->rolActivo = auth()
      ->user()
      ->roles()
      ->wherePivot('activo', true)
      ->first();
  }

  #[On('abrirModalBajaAlta')]
  public function abrirModalBajaAlta($grupoId, $tipo){
    $this->grupo = Grupo::find($grupoId);
    $this->tipo = $tipo;

    $this->titulo = $tipo == 'alta' ?  "Dar de alta a <b>".$this->grupo->nombre."</b>" : "Dar de baja a <b>".$this->grupo->nombre."</b>";
    $this->grupoId = $this->grupo->id;

    $this->dispatch('abrirModal', nombreModal: 'modaBajaAlta');

  }

  #[On('grupo-id-anidado')]
  public function getGrupoTraspasoId($grupoId)
  {
    $this->grupoTraspasoId = $grupoId;
  }

  public function crearBajaAlta($grupoId, $tipo)
  {
    $this->validate();

    $reporte = new ReporteGrupoBajaAlta;
    $reporte->motivo= $this->motivo;
		$reporte->observaciones= $this->observaciones;

    $grupo = Grupo::find($grupoId);

    if($grupo->dado_baja == 0 && $tipo =='baja' )
    {
      //se le da da baja
      $reporte->dado_baja= 1;

      // traslado a los integrantes al nuevo grupo
      if($this->grupoTraspasoId)
      {
        $integrantes= $grupo->asistentes()->get();

				foreach ($integrantes as  $integrante) {
          // Lo desvinculo del grupo actual
          $integrante->desvincularDeGrupo($grupo->id);

					// Le asiguno al integrante a su nuevo grupo
					$integrante->cambiarGrupo($this->grupoTraspasoId);
					$integrante->save();
				}
      }

      // codigo para eliminar los servidores del grupo al que se esta dando de baja
			foreach ($grupo->servidores as $usuario) {
				$servidor=ServidorGrupo::where('grupo_id', $grupo->id)->where('user_id', $usuario->id)->first();
				if(isset($servidor->id))
				{
					$servicios=$servidor->tipoServicioGrupo()->detach();
				}
			}
			$grupo->servidores()->detach();

      $status = 'success';
      $mensaje = '¡Muy bien!, el grupo <b>'.$grupo->nombre.'</b> fue dado de baja con éxito.';

      $grupo->dado_baja= 1;
    }elseif($grupo->dado_baja == 1 && $tipo =='alta')
    {
      // se le da de alta
      $reporte->dado_baja= 0;

      $status = 'success';
      $mensaje = '¡Muy bien!, el grupo <b>'.$grupo->nombre.'</b> fue dado de alta con éxito.';
      $grupo->dado_baja= 0;
    }else{
      $status = 'danger';
      $mensaje = '¡Ups! Hubo un error, por favor, intenta nuevamente.';
    }

    if($status == 'success')
    {
      $reporte->grupo_id= $grupo->id;
      $reporte->fecha= Carbon::now()->format('Y-m-d');

      $reporte->save();
      $grupo->save();
    }

    return redirect(request()->header('Referer'))->with($status, $mensaje);
  }

  #[On('confirmarEliminacion')]
  public function confirmarEliminacion($grupoId){

    $grupo = Grupo::find($grupoId);
    $cantidadIntegrantes = $grupo->asistentes()->select('users.id')->count();

    $tienePermiso = 'si';

    if($grupo->asistentes()->select('users.id')->count() > 0)
    {

      $tienePermiso = $this->rolActivo->hasPermissionTo('grupos.pestana_anadir_integrantes_grupo')
      ? 'si'
      : 'no';

      $this->dispatch(
        'msnConfirmarEliminacion',
        msnIcono: 'warning',
        msnTitulo: 'No es posible eliminar',
        msnTexto:  '<b>'.$grupo->nombre.'</b> todavía posee <b>'.$cantidadIntegrantes.' integrante(s)</b>,  por favor desvincúlalos para poder eliminar el grupo, ¿Deseas gestionar los integrantes? ',
        confirmButtonText: 'Si, gestionar integrantes',
        id: $grupo->id,
        cantidadIntegrantes: $cantidadIntegrantes,
        tienePermiso: $tienePermiso
      );
    }else{
      $this->dispatch(
        'msnConfirmarEliminacion',
        msnIcono: 'warning',
        msnTitulo: '¡Precaución!',
        msnTexto:  '¿Está seguro que desea eliminar el grupo <b>'.$grupo->nombre.'</b>? ',
        confirmButtonText: 'Si, eliminar',
        id: $grupo->id,
        cantidadIntegrantes: $cantidadIntegrantes,
        tienePermiso: $tienePermiso
      );
    }

  }

  public function eliminacion($grupoId)
  {
    $configuracion=Configuracion::find(1);
    $grupo = Grupo::find($grupoId);

    $cantidadIntegrantes = $grupo->asistentes()->select('users.id')->count();
    if($cantidadIntegrantes > 0)
    {
      $this->redirect("/grupo/$grupoId/gestionar-integrantes");
    }else{
      //Elimino el grupo
      $grupo->delete();
      IntegranteGrupo::where("grupo_id",$grupo->id)->delete();

      if($grupo->portada != 'default.png')
      Storage::delete('public/' . $configuracion->ruta_almacenamiento . '/img/grupos' . '/' . $grupo->portada);

      return redirect('/grupos')->with('success', ' ¡Muy bien! el grupo <b>'.$grupo->nombre.'</b> fue eliminado de manera éxitosa.');
    }

  }

  public function render()
  {
    return view('livewire.grupos.modal-baja-alta-grupo');
  }
}
