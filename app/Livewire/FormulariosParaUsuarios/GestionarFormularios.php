<?php

namespace App\Livewire\FormulariosParaUsuarios;

use App\Models\FormularioUsuario;
use Livewire\Component;

class GestionarFormularios extends Component
{
  public $busqueda = '';
  public $conEliminados = false;
  public $entre = 'no';

  public function mount()
  {
  }

  public function ocultarMostrar($formularioId)
  {
    $formulario = FormularioUsuario::withTrashed()->find($formularioId);

    if ($formulario->trashed()) {
      $formulario->restore();

      $this->dispatch(
        'msn',
        msnIcono: 'success',
        msnTitulo: '¡Buen trabajo!',
        msnTexto: 'El formulario se restauró con éxito.'
      );
    } else {
      $formulario->delete();

      $this->dispatch(
        'msn',
        msnIcono: 'success',
        msnTitulo: '¡Buen trabajo!',
        msnTexto: 'El formulario se ocultó con éxito.'
      );
    }

  }

  public function eliminarFormulario($formularioId)
  {
    $formulario = FormularioUsuario::withTrashed()->find($formularioId);

    foreach ($formulario->secciones as $seccion) {
      $seccion->campos()->detach();
    }
    $formulario->secciones()->delete();
    $formulario->forceDelete();
  }

  public function duplicarFormulario($formularioId)
  {
    $formularioOriginal = FormularioUsuario::withTrashed()->find($formularioId);
    $nuevoFormulario = $formularioOriginal->replicate();

    $existeFormulario = FormularioUsuario::where('id', '!=', $formularioId)
    ->where('nombre', $formularioOriginal->nombre)
    ->count();

    $nuevoFormulario->nombre = $formularioOriginal->nombre . ' copia' . $existeFormulario + 1;
    $nuevoFormulario->save();

    foreach ($formularioOriginal->secciones as $seccionOriginal) {
      $nuevaSeccion = $seccionOriginal->replicate();
      $nuevaSeccion->formulario_usuario_id = $nuevoFormulario->id;
      $nuevaSeccion->save();

      // Duplicar las relaciones de la tabla pivote
      $campos = $seccionOriginal->campos()->withPivot('orden', 'requerido', 'class')->get();
      foreach ($campos as $campo) {
          $nuevaSeccion->campos()->attach($campo->id, [
              'orden' => $campo->pivot->orden,
              'requerido' => $campo->pivot->requerido,
              'class' => $campo->pivot->class,
          ]);
      }
    }

    $this->dispatch(
      'msn',
      msnIcono: 'success',
      msnTitulo: '¡Buen trabajo!',
      msnTexto: 'El formulario ' . $formularioOriginal->nombre . ', fue duplicado con éxito.'
    );

  }

  public function render()
  {
    $formularios = FormularioUsuario::whereRaw('1=1');

    if ($this->conEliminados) {
      $formularios->withTrashed();
      $this->entre = 'si';
    }

    $formularios = $formularios->whereRaw("translate(nombre,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜ','aeiouAEIOUaeiouAEIOU') ILIKE '%$this->busqueda%'")
      ->orderBy('nombre', 'ASC')
      ->get();

      return view('livewire.formularios-para-usuarios.gestionar-formularios', [
        'formularios' => $formularios,
      ]);
  }
}
