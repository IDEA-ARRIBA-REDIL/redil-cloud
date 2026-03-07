<?php

namespace App\Livewire\GestionarSeccionesyPasosDeCrecimiento;

use App\Livewire\FormulariosParaUsuarios\SelectorDeCampos;
use App\Models\Configuracion;
use App\Models\PasoCrecimiento;
use App\Models\Role;
use App\Models\SeccionPasoCrecimiento;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;

class GestionarSeccionesYPasosDeCrecimiento extends Component
{
  public $formulario;
  public $secciones = [];
  public $variable = [0];

  public $seccionesActivas = [];

  public $respuesta = 'dd';

  /* Campos para el funcionamiento de editar y crear seccion  */
  public $nombre;
  public string $nombrePasoCrecimiento = '';
  public $título;
  public $icono;
  public $imagen;
  public $modoEdicion = false;
  public $seccionEditando;

  /* Campos para el funcionamiento de editar y crear pasos de crecimiento  */
  public $class;
  public $campoRequerido;
  public $pasoCrecimiento;
  public $descripcion;
  //public $título;
  public $modoEdicionPasoCrecimiento = false;
  public $pasoCrecimientoEditando;
  public $seccionPasoCrecimiento;

  // Roles
  public $roles = [];
  public $rolesSeleccionados = [];

  protected $rules = [
    'nombre' => 'required'
  ];

  protected $rulesPasosCrecimiento = [
    'nombrePasoCrecimiento' => 'required|string|min:1',
  ];

  // otros
  public $configuracion;

  public function mount()
  {
    // lista de roles
    $this->roles = Role::all(); // Listar todos los roles

    $this->configuracion = Configuracion::find(1);
  }

  // esta funcion prepara las variables para abrir el modal de crearSeccion
  public function crearSeccion()
  {
    // Resetea los campos del formulario
    $this->seccionEditando = null;
    $this->reset(['nombre']);

    $this->modoEdicion = false;

    // Emitir evento para abrir el offcanvas (opcional)
    $this->dispatch('abrirModal', nombreModal: 'modalNuevaSeccion');
  }

  // esta funcion prepara las variables para abrir el modal de editarSeccion
  public function editarSeccion($seccionId)
  {
    // Resetea los campos del formulario
    $this->seccionEditando = null;
    $this->reset(['nombre']);

    $this->modoEdicion = true;
    $this->seccionEditando = SeccionPasoCrecimiento::find($seccionId);
    $this->nombre = $this->seccionEditando->nombre;

    // Emitir evento para abrir el offcanvas (opcional)
    $this->dispatch('abrirModal', nombreModal: 'modalNuevaSeccion');
  }

  public function guardarSeccion()
  {
    $validatedData = Validator::make($this->all(), $this->rules)->validate();

    if ($this->modoEdicion) {
      // Actualiza la sección de pasos de crecimiento existente
      $this->seccionEditando->nombre = $this->nombre;
      $this->seccionEditando->save();

      $this->dispatch('cerrarModal', nombreModal: 'modalNuevaSeccion');
      $this->modoEdicion = false;
      $this->reset('seccionEditando');

      $this->dispatch(
        'msn',
        msnIcono: 'success',
        msnTitulo: '¡Muy bien!',
        msnTexto: 'La sección fue editada con éxito.'
      );
    } else {
      $seccion = new SeccionPasoCrecimiento;
      $seccion->nombre = $validatedData['nombre'];

      $ultimaSeccionActual = SeccionPasoCrecimiento::orderBy('orden', 'desc')->first();
      $seccion->orden = $ultimaSeccionActual ? $ultimaSeccionActual->orden + 1 : 1;
      $seccion->save();

      $this->dispatch('cerrarModal', nombreModal: 'modalNuevaSeccion');
      $this->modoEdicion = false;

      $this->dispatch(
        'msn',
        msnIcono: 'success',
        msnTitulo: '¡Muy bien!',
        msnTexto: 'La sección fue creada con éxito.'
      );
    }
  }

  public function eliminarSeccion($seccionId)
  {
    $seccion = SeccionPasoCrecimiento::find($seccionId);

    if ($seccion) {
      // Elimina todos los pasos relacionados con esa sección
      $seccion->pasosCrecimiento()->delete();

      // Luego elimina la sección
      $seccion->delete();

      // Si la sección eliminada era la que se estaba editando, la limpiamos del estado.
      if ($this->seccionEditando && $this->seccionEditando->id == $seccionId) {
        $this->reset('seccionEditando');
      }
    }
  }

  // esta funcion prepara las variables para abrir el modal de crearPasoCrecimiento
  public function crearPasoCrecimiento($seccionId)
  {
    $this->seccionesActivas = [$seccionId];
    $this->modoEdicionPasoCrecimiento = false;
    $this->reset(['nombre', 'descripcion']);
    $this->seccionPasoCrecimiento =  SeccionPasoCrecimiento::find($seccionId); // Almacena la sección actual
    $this->class = "col-12 col-sm-6 col-md-4 col-lg-3";
    $this->dispatch('abrirModal', nombreModal: 'modalNuevoPasoCrecimiento');

    $this->pasoCrecimiento = null;

    // ✅ Para 'crear', se inicia con los roles vacíos.
    $this->rolesSeleccionados = [];

    $this->dispatch('init-bootstrap-select');
    $this->dispatch('quitarSeleccion')->to(SelectorDeCampos::class);
  }

  // esta funcion prepara las variables para abrir el modal de editarPasoCrecimiento
  public function editarPasoCrecimiento($seccionId, $pasoCrecimientoId)
  {
    $this->seccionPasoCrecimiento = SeccionPasoCrecimiento::find($seccionId);
    $paso = $this->seccionPasoCrecimiento->pasosCrecimiento()
      ->where('id', $pasoCrecimientoId)
      ->first();

    $this->pasoCrecimientoEditando = $paso;
    $this->seccionesActivas = [$seccionId];
    $this->modoEdicionPasoCrecimiento = true;

    // Reset del formulario
    $this->reset(['nombre', 'descripcion']);
    $this->pasoCrecimiento = null;
    $this->dispatch('quitarSeleccion')->to(SelectorDeCampos::class);

    // Cargar los valores del paso en edición
    $this->nombre = $paso->nombre;
    $this->descripcion = $paso->descripcion;

    $paso = $this->seccionPasoCrecimiento
      ->pasosCrecimiento()
      ->where('id', $pasoCrecimientoId)
      ->first();

    $this->pasoCrecimientoEditando = $paso;

    $this->dispatch('abrirModal', nombreModal: 'modalNuevoPasoCrecimiento');
  }


  // esta funcion guarda o edita los datos en la BD
  public function guardarPasoCrecimiento()
  {
    $validatedData = Validator::make($this->all(), $this->rulesPasosCrecimiento)->validate();

    if ($this->modoEdicionPasoCrecimiento) {
      // Actualizar el paso de crecimiento existente
      $paso = $this->seccionPasoCrecimiento->pasosCrecimiento()
        ->where('id', $this->pasoCrecimientoEditando->id)
        ->first();

      if ($paso) {
        $paso->update([
          'nombre' => $this->nombrePasoCrecimiento,
          'descripcion' => $this->descripcion,
        ]);
      }


      $this->dispatch('cerrarModal', nombreModal: 'modalNuevoPasoCrecimiento');

      $this->dispatch(
        'msn',
        msnIcono: 'success',
        msnTitulo: '¡Muy bien!',
        msnTexto: 'La sección fue editada con éxito.'
      );
    } else {
      // Crear un nuevo paso de crecimiento
      // dd($this->pasoCrecimiento);
      if ($this->pasoCrecimiento) {
        // valido y mando el error a componente anidado
        $this->dispatch(
          'mostrarMensajeError',
          mostrarError: true,
          msnError: 'El campo es requerido.'
        )->to(SelectorDeCampos::class);

        $this->respuesta = $this->pasoCrecimiento;
      } else {
        $this->respuesta = $this->pasoCrecimiento;

        $this->dispatch(
          'mostrarMensajeError',
          mostrarError: false,
          msnError: ''
        )->to(SelectorDeCampos::class);

        PasoCrecimiento::create([
          'nombre' => $validatedData['nombrePasoCrecimiento'],
          'descripcion' => $this->descripcion,
          'orden' => $this->seccionPasoCrecimiento->pasosCrecimiento()->count() + 1,
          'seccion_paso_crecimiento_id' => $this->seccionPasoCrecimiento->id
        ]);

        $this->dispatch('cerrarModal', nombreModal: 'modalNuevoPasoCrecimiento');

        $this->dispatch(
          'msn',
          msnIcono: 'success',
          msnTitulo: '¡Muy bien!',
          msnTexto: 'El campo fue creado con éxito.'
        );
      }
    }
  }

  public function actualizarOrdenPasoCrecimiento($pasoCrecimientoId, $seccionOrigenId, $seccionDestinoId, $ordenOrigen, $ordenDestino)
  {
    $this->seccionesActivas = [$seccionDestinoId, $seccionOrigenId];
    $seccionDestinoId = (int) $seccionDestinoId;

    // Actualizar orden en la sección de destino
    $seccionDestino = SeccionPasoCrecimiento::find($seccionDestinoId);

    $ordenDestino = array_filter($ordenDestino);
    foreach ($ordenDestino as $index => $id) {
      PasoCrecimiento::where('id', $id)
        ->where('seccion_paso_crecimiento_id', $seccionDestino->id) // seguridad opcional
        ->update(['orden' => $index + 1]);
    }


    if ($seccionOrigenId !== $seccionDestinoId) {
      $seccionOrigen = SeccionPasoCrecimiento::find($seccionOrigenId);
      $ordenOrigen = array_filter($ordenOrigen);

      foreach ($ordenOrigen as $index => $id) {
        PasoCrecimiento::where('id', $id)
          ->where('seccion_paso_crecimiento_id', $seccionOrigen->id)
          ->update(['orden' => $index + 1]);
      }
    }
  }

  public function subirSeccion($id)
  {
    $this->seccionesActivas = [];
    $seccionActual = SeccionPasoCrecimiento::find($id);
    $seccionAnterior = SeccionPasoCrecimiento::where('orden', '<', $seccionActual->orden)->orderBy('orden', 'desc')->first();

    if ($seccionAnterior) {
      $ordenAnterior = $seccionAnterior->orden;
      $seccionAnterior->orden = $seccionActual->orden;
      $seccionActual->orden = $ordenAnterior;

      $seccionActual->save();
      $seccionAnterior->save();
    }
  }

  public function bajarSeccion($id)
  {
    $this->seccionesActivas = [];
    $seccionActual = SeccionPasoCrecimiento::find($id);
    $seccionSiguiente = SeccionPasoCrecimiento::where('orden', '>', $seccionActual->orden)->orderBy('orden')->first();

    if ($seccionSiguiente) {
      $ordenSiguiente = $seccionSiguiente->orden;
      $seccionSiguiente->orden = $seccionActual->orden;
      $seccionActual->orden = $ordenSiguiente;

      $seccionActual->save();
      $seccionSiguiente->save();
    }
  }

  public function eliminarPasoCrecimiento($pasoCrecimientoId, $seccionId)
  {
    $seccion = SeccionPasoCrecimiento::find($seccionId);

    // Asegúrate de que el paso le pertenece a esa sección
    $paso = $seccion->pasosCrecimiento()->where('id', $pasoCrecimientoId)->first();

    if ($paso) {
      $paso->delete(); // Elimina el registro
    }
  }

  public function render()
  {
    $this->secciones = SeccionPasoCrecimiento::orderBy('orden', 'asc')->get();
    return view('livewire.gestionar-seccionesy-pasos-de-crecimiento.gestionar-secciones-y-pasos-de-crecimiento', [
      'secciones' => $this->secciones
    ]);
  }
}
