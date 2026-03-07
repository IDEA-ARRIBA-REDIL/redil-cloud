<?php

namespace App\Livewire\Reuniones;

use Livewire\Component;
use App\Models\Configuracion;
use App\Models\IntegranteGrupo;
use App\Models\ReporteBajaAlta;
use App\Models\Reunion;
use App\Models\TipoBajaAlta;
use Carbon\Carbon;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Storage;

class ModalDarBaja extends Component
{
  public $titulo = "", $respuesta = " bn";
  public $reunionId, $tipo, $redirect;
  public $motivosBajasAltas;

  #[Validate('required')]
  public $motivo;
  public $observacion;

  public function mount()
  {
    $this->motivosBajasAltas = collect();
  }

  #[On('confirmacionBajaAlta')]
  public function confirmacionBajaAlta($reunionId, $tipo)
  {
    if (!$reunion = Reunion::withTrashed()->find($reunionId)) {
      return;
    }

    if ($tipo == 'baja') {
      $htmlRegistros = '
        <p>Estas seguro que deseas dar de baja a la reunión <b>' . $reunion->nombre . '</b></p>
        <p>¿Deseas darle de baja?</p>';
      $this->dispatch(
        'msnDarDeBajaAlta',
        msnIcono: 'warning',
        msnTitulo: '¡Precaución!',
        msnTexto: $htmlRegistros,
        id: $reunion->id,
        tipo: $tipo
      );
    } else if ($tipo == 'alta') {
      $htmlRegistros = '
        <p>Estas seguro que deseas dar de alta a la reunión <b>' . $reunion->nombre . '</b></p>
        <p>¿Deseas darle de alta?</p>';
      $this->dispatch(
        'msnDarDeBajaAlta',
        msnIcono: 'warning',
        msnTitulo: '¡Precaución!',
        msnTexto: $htmlRegistros,
        id: $reunion->id,
        tipo: $tipo
      );
    }
  }

  public function darBajaAlta($reunionId, $tipo)
  {
    // Aqui le voy a dar de baja
    $reunion = Reunion::withTrashed()->find($reunionId);

    if ($tipo == 'baja') {
      $reunion->delete();
      return redirect('/reuniones/lista/todos')->with('success', "<b>" . $reunion->nombre . "</b> fue dado de baja con éxito.");
    } else if ($tipo == 'alta') {
      $reunion->restore();
      return redirect('/reuniones/lista/todos')->with('success', "<b>" . $reunion->nombre . "</b> fue dado de alta con éxito.");
    }
  }

  #[On('comprobarSiTieneRegistros')]
  public function comprobarSiTieneRegistros($reunionId)
  {
    $reunion = Reunion::find($reunionId);

    $tieneRegistros = $reunion->reportes->count();
    $htmlRegistros = '
      <p>No es recomendado eliminar a <b>' . $reunion->nombre . '</b> debido a que tiene reportes creados</p>
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
        id: $reunion->id
      );
    } else {
      $this->confirmarEliminacion($reunion->id);
    }
  }

  #[On('confirmarEliminacion')]
  public function confirmarEliminacion($reunionId)
  {

    $reunion = Reunion::withTrashed()->find($reunionId);
    $this->dispatch(
      'msnConfirmarEliminacion',
      msnIcono: 'warning',
      msnTitulo: '¿Estás seguro que deseas eliminar a <b>' . $reunion->nombre . '</b>?',
      msnTexto: 'Esta acción no es reversible.',
      id: $reunion->id
    );
  }

  #[On('confirmarDarDeBajaAlta')]
  public function confirmarDarDeBajaAlta($reunionId, $tipo)
  {
    if (true) {
      $reunion = Reunion::withTrashed()->find($reunionId);
      $this->dispatch(
        'msnConfirmarDarDeBaja',
        msnIcono: 'warning',
        msnTitulo: '¿Estás seguro que deseas eliminar a <b>' . $reunion->nombre . '</b>?',
        msnTexto: 'Esta acción no es reversible.',
        id: $reunion->id
      );
    } else {
    }
  }

  public function eliminacionForzada($reunionId)
  {
    $reunion = Reunion::withTrashed()->find($reunionId);
    $reunion->forceDelete();
    return redirect('/reuniones/lista/todos')->with('success', "<b>" . $reunion->nombre . "</b> fue eliminado con éxito.");
  }

  public function render()
  {
    return view('livewire.reuniones.modal-dar-baja');
  }
}
