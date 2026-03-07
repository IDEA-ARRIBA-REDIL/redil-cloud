<?php

namespace App\Livewire\Grupos;

use App\Helpers\Helpers;
use App\Models\Configuracion;
use App\Models\GrupoExcluido;
use App\Models\User;
use Livewire\Component;

class ListadoExclusionesGrupo extends Component
{
  public $configuracion, $rolActivo, $queUsuariosCargar;
  public $busqueda;

  public function mount()
  {
    $this->configuracion = Configuracion::find(1);

    $this->rolActivo = auth()
      ->user()
      ->roles()
      ->wherePivot('activo', true)
      ->first();
  }

  public function eliminar( $idExclusion )
  {
    $exclusion = GrupoExcluido::find($idExclusion);
    $exclusion->delete();

    $this->dispatch(
      'msn',
      msnIcono: 'success',
      msnTitulo: '¡Muy bien!',
      msnTexto: 'La exclusión fue eliminada con exito.'
    );

  }

  public function limpiarBusqueda()
  {
     $this->busqueda = '';
  }

  public function render()
  {
    $exclusiones = User::leftJoin('grupos_excluidos','users.id','=','grupos_excluidos.user_id')
    ->leftJoin('grupos','grupos_excluidos.grupo_id','=','grupos.id')
    ->whereNotNull('grupos_excluidos.id')
    ->select(
      'users.id as userId','users.primer_nombre', 'users.segundo_nombre', 'users.primer_apellido', 'users.segundo_apellido', 'users.tipo_usuario_id', 'users.foto', 'users.fecha_nacimiento',
      'grupos.nombre as nombreGrupo',
      'grupos_excluidos.id'
    )->get();

    if ($this->busqueda) {
      $buscar = htmlspecialchars($this->busqueda);
      $buscar = Helpers::sanearStringConEspacios($buscar);
      $buscar = str_replace(["'"], '', $buscar);
      $buscar_array = explode(' ', $buscar);

      foreach ($buscar_array as $palabra) {
        $exclusiones = $exclusiones->filter(function ($exclusiones) use ($palabra) {
            $respuesta  = false !== stristr(Helpers::sanearStringConEspacios($exclusiones->primer_nombre), $palabra) ||
            false !== stristr(Helpers::sanearStringConEspacios($exclusiones->segundo_nombre), $palabra) ||
            false !== stristr(Helpers::sanearStringConEspacios($exclusiones->primer_apellido), $palabra) ||
            false !== stristr(Helpers::sanearStringConEspacios($exclusiones->segundo_apellido), $palabra) ||
            false !== stristr(Helpers::sanearStringConEspacios($exclusiones->identificacion), $palabra) ||
            false !== stristr(Helpers::sanearStringConEspacios($exclusiones->email), $palabra) ||
            false !== stristr(Helpers::sanearStringConEspacios($exclusiones->id), $palabra);

            return $respuesta;
        });
      }
    }

    return view('livewire.grupos.listado-exclusiones-grupo', [ 'exclusiones' => $exclusiones ]);
  }
}
