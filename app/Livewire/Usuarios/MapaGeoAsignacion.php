<?php

namespace App\Livewire\Usuarios;

use App\Models\Grupo;
use App\Models\InformeGrupo;
use App\Models\IntegranteGrupo;
use App\Models\TipoAsignacion;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Livewire\Attributes\On;
use Livewire\Component;

class MapaGeoAsignacion extends Component
{

  public $verListaBusqueda = false;
  public $busqueda = '', $respuesta = 'no', $resultadosBusqueda = [];
  public $rolActivo;

  public function mount()
  {
    $this->rolActivo = auth()
      ->user()
      ->roles()
      ->wherePivot('activo', true)
      ->first();
  }

  public function desplegarListaBusqueda()
  {
    $this->verListaBusqueda = true;
  }

  public function ocultarListaBusqueda()
  {
    $this->verListaBusqueda = false;
  }

  public function verEnMapa($latitud, $longitud, $tipo)
  {
    $zoom = $tipo == 'road' ? 16 : 14;
    $this->verListaBusqueda = false;
    $this->dispatch('verEnElMapa', latitud: $latitud, longitud: $longitud, zoom: $zoom);
  }

  #[On('asignar-al-grupo')]
  public function asignarAlGrupo($grupoId, $idUsuario)
  {

    $usuario = User::find($idUsuario);
    //Verifico si ya se encontraba agregado al grupo

    if (IntegranteGrupo::where("integrantes_grupo.user_id", $idUsuario)->where("grupo_id", $grupoId)->count() == 0)
    {
      $creoAsignacion = true;
      $grupo = Grupo::find($grupoId);
      $usuario = User::find($idUsuario);

      $tiposGruposPrivilegioAsignar = $this->rolActivo->privilegiosTiposGrupo()
      ->wherePivot("asignar_asistente", "=", FALSE)
      ->select('tipo_grupos.id')
      ->pluck('tipo_grupos.id')
      ->toArray();

      if(in_array($grupo->tipoGrupo->id, $tiposGruposPrivilegioAsignar))
      {
        $creoAsignacion = false;
        $this->dispatch(
          'msn',
          msnIcono: 'warning',
          msnTitulo: '¡Ups!',
          msnTexto: 'No tienes los privilegios suficientes para asignar a la persona de este grupo. Por favor, consulte a su administrador.'
        );
      }

      if(!$this->rolActivo->hasPermissionTo('grupos.privilegio_asignar_asistente_todo_tipo_asistente_a_un_grupo'))
      {
        $tipoGrupo = $grupo->tipoGrupo;
        $usuarioPermintidos= $tipoGrupo->tipoUsuariosPermitidos()
        ->wherePivot('para_asistentes', '=', TRUE)
        ->where('tipo_usuario_id','=',$usuario->tipoUsuario->id)
        ->count();

        if($usuarioPermintidos<=0)
        {
          $creoAsignacion = false;
          $this->dispatch(
            'msn',
            msnIcono: 'warning',
            msnTitulo: '¡Ups!',
            msnTexto: 'No es posible asignar un <b>'.$usuario->tipoUsuario->nombre.'</b> a un grupo tipo <b>'.$grupo->tipoGrupo->nombre.'</b>. Por favor, consulte a su administrador.'
          );
        }
      }

      if($creoAsignacion == TRUE)
      {

        // lo asigno al grupo IntegranteGrupo
        $usuario->cambiarGrupo($grupoId);

        // creo la bitacora por defecto
        $tipoAsignacionDefault = TipoAsignacion::where('default', true)->first();

        InformeGrupo::create([
          'user_id' => $usuario->id,
          'grupo_id' => $grupo->id,
          'observaciones' => 'Creado por Geo asignación',
          'tipo_asignacion_id' => $tipoAsignacionDefault->id,
          'tipo_informe' => 2, // (1) "Asignación de líder" (2) "Asignación de asistente" (3) "Desvinculacion de líder" (4) "Desvinculacion del asistente"
          'user_autor_asignacion' => auth()->user()->id
        ]);

        // actualizo la sede del asistente a la sede del grupo
        $usuario->asignarSede($grupoId);

        $this->dispatch(
          'msn',
          msnIcono: 'success',
          msnTitulo: '¡Muy bien!',
          msnTexto: '<b>'.$usuario->nombre(3).'</b> fue agregado al grupo <b>'.$grupo->nombre.'</b> de manera éxitosa.'
        );

      }




    }else{
      $this->dispatch(
        'msn',
        msnIcono: 'warning',
        msnTitulo: '¡Ups!',
        msnTexto: '<b>'.$usuario->nombre(3).'</b> ya se encuentra asignado, por lo tanto, no fue posible hacer esta acción.'
      );
    }




  }

  #[On('asignar-georreferencia-al-grupo')]
  public function asignarGeorreferenciaAlGrupo($grupoId, $lat, $lon)
  {
    $grupo = Grupo::find($grupoId);
    $grupo->latitud = $lat;
    $grupo->longitud = $lon;
    $grupo->save();

    $this->dispatch(
      'msn',
      msnIcono: 'success',
      msnTitulo: '¡Muy bien!',
      msnTexto: 'El grupo <b>"'.$grupo->nombre.'"</b> quedo asignado correctamente.'
    );
  }

  public function render()
  {

    if ($this->busqueda && Strlen($this->busqueda) > 3) {

      $this->resultadosBusqueda = [];
      $response = Http::get("https://nominatim.openstreetmap.org/search?q=$this->busqueda$&format=json");

      $this->resultadosBusqueda = json_decode($response);
    }
    return view('livewire.usuarios.mapa-geo-asignacion');
  }
}
