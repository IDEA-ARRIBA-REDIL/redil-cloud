<?php

namespace App\Livewire\FormulariosParaUsuarios;

use App\Models\Configuracion;
use App\Models\SeccionFormularioUsuario;
use Livewire\Component;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;


use Illuminate\Support\Facades\Storage;

use Livewire\WithFileUploads;

class GestionarSeccionesYCampos extends Component
{
  use WithFileUploads;

  public $formulario;
  public $secciones = [];
  public $variable = [0];

  public $seccionesActivas = [];

  public $respuesta = 'dd';
  public $campos = [];

  /* Campos para el funcionamiento de editar y crear seccion  */
  public $nombre;
  public $título;
  public $icono;
  public $imagen;
  public $modoEdicion = false;
  public $seccionEditando;

  /* Campos para el funcionamiento de editar y crear campos  */
  public $class;
  public $campoRequerido;
  public $campo;
  public $informacionDeApoyo;
  //public $título;
  public $modoEdicionCampo = false;
  public $campoEditando;
  public $seccionCampo;


  // otros
  public $configuracion ;

  protected $rules = [
    'nombre' => 'required',
    'título' => 'required',
    'imagen' => [
        'nullable', // El campo imagen es opcional
        'image',
        'mimes:jpg,png' // Validar formato
    ]
  ];

  protected $rulesCampos = [
    'class' => 'required'
  ];

  public function mount()
  {
     $this->configuracion = Configuracion::find(1);
  }

  // esta funcion prepara las variables para abrir el modal de crearCampo
  public function crearCampo($seccionId)
  {
    $this->seccionesActivas = [$seccionId];
    $this->modoEdicionCampo = false;
    $this->reset(['class', 'campoRequerido', 'informacionDeApoyo']);
    $this->seccionCampo =  SeccionFormularioUsuario::find($seccionId); // Almacena la sección actual de campo
    $this->class="col-12 col-sm-6 col-md-4 col-lg-3";
    $this->dispatch('abrirModal', nombreModal: 'modalNuevoCampo');

    $this->campo = null;
    $this->dispatch('quitarSeleccion')->to(SelectorDeCampos::class);
  }

   // esta funcion prepara las variables para abrir el modal de editarCampo
  public function editarCampo($seccionId, $campoId)
  {
    $this->seccionCampo =  SeccionFormularioUsuario::find($seccionId); // Almacena la sección actual de campo
    $this->campoEditando =  $this->seccionCampo->campos()->where('campos_formulario_usuario.id', $campoId)->first();
    $this->seccionesActivas = [$seccionId];
    $this->modoEdicionCampo = true;

    // formateo el formulario
    $this->reset(['class', 'campoRequerido', 'informacionDeApoyo']);
    $this->campo = null;
    $this->dispatch('quitarSeleccion')->to(SelectorDeCampos::class);
    //fin formateo formulario

    $this->class= $this->campoEditando->pivot->class;
    $this->informacionDeApoyo = $this->campoEditando->pivot->informacion_de_apoyo;
    $this->campoRequerido= $this->campoEditando->pivot->requerido;
    $this->dispatch('abrirModal', nombreModal: 'modalNuevoCampo');
  }

   // esta funcion guarda o edita los datos en la BD
  public function guardarCampo()
  {
    $validatedData = Validator::make($this->all(), $this->rulesCampos)->validate();

    if ($this->modoEdicionCampo) {
        // Actualizar el campo existente
        $this->seccionCampo->campos()->updateExistingPivot(
          $this->campoEditando->id, // Usa el ID del campo a editar
          [
            'requerido' => $this->campoRequerido,
            'class' => $this->class,
            'informacion_de_apoyo' => $this->informacionDeApoyo
          ]
        );

        $this->dispatch('cerrarModal', nombreModal: 'modalNuevoCampo');
        $this->reset('campoEditando', 'modoEdicionCampo'); // <-- AÑADIR ESTO

        $this->dispatch(
          'msn',
          msnIcono: 'success',
          msnTitulo: '¡Muy bien!',
          msnTexto: 'La sección fue editada con éxito.'
        );

    } else {
        // Crear un nuevo campo
        if(!$this->campo)
        {
          // valido y mando el error a componente anidado
          $this->dispatch('mostrarMensajeError',
          mostrarError: true,
          msnError: 'El campo es requerido.'
          )->to(SelectorDeCampos::class);

          $this->respuesta = $this->campo;
        }else{
          $this->respuesta = $this->campo;

          $this->dispatch('mostrarMensajeError',
          mostrarError: false,
          msnError: ''
          )->to(SelectorDeCampos::class);

          $this->seccionCampo->campos()->attach( $this->campo, [
            'class' => $this->class,
            'requerido' => $this->campoRequerido ? true : false,
            'orden' => $this->seccionCampo->campos()->count() + 1,
            'informacion_de_apoyo' => $this->informacionDeApoyo
          ]);

          $this->dispatch('cerrarModal', nombreModal: 'modalNuevoCampo');

          $this->dispatch(
            'msn',
            msnIcono: 'success',
            msnTitulo: '¡Muy bien!',
            msnTexto: 'El campo fue creado con éxito.'
          );
        }


    }

  }

  // esta funcion prepara las variables para abrir el modal de crearSeccion
  public function crearSeccion()
  {
    // Resetea los campos del formulario
    $this->seccionEditando = null;
    $this->reset(['nombre', 'título', 'icono', 'imagen']);

    $this->modoEdicion = false;

    // Emitir evento para abrir el offcanvas (opcional)
    $this->dispatch('abrirModal', nombreModal: 'modalNuevaSeccion');
  }

  // esta funcion prepara las variables para abrir el modal de editarSeccion
  public function editarSeccion($seccionId)
  {
      // Resetea los campos del formulario
      $this->seccionEditando = null;
      $this->reset(['nombre', 'título', 'icono', 'imagen']);

      $this->modoEdicion = true;
      $this->seccionEditando = SeccionFormularioUsuario::find($seccionId);
      $this->nombre = $this->seccionEditando->nombre;
      $this->título = $this->seccionEditando->titulo;
      $this->icono = $this->seccionEditando->icono;

      // Emitir evento para abrir el offcanvas (opcional)
      $this->dispatch('abrirModal', nombreModal: 'modalNuevaSeccion');
  }

  public function guardarSeccion()
  {
    $validatedData = Validator::make($this->all(), $this->rules)->validate();

    // Validar dimensiones de la imagen si se proporciona
    if ($this->imagen) {
      // Usar sometimes() en lugar de validate sometimes
      Validator::make($this->all(), [
        'imagen' => Rule::dimensions()->maxWidth(100)->maxHeight(100)
      ])->validate();
    }

    if ($this->modoEdicion) {
      // Actualizar la sección existente
      $this->seccionEditando->nombre = $this->nombre;
      $this->seccionEditando->titulo = $this->título;
      $this->seccionEditando->icono = $this->icono;
      $this->seccionEditando->save();

       // Guardar la imagen (si se proporciona)
       if ($this->imagen) {

        // creo la carpeta si no exite
        $path = public_path('storage/' . $this->configuracion->ruta_almacenamiento . '/img' . '/secciones-formulario' . '/');
        !is_dir($path) && mkdir($path, 0777, true);

        $extension = $this->imagen->extension();
        $nombreArchivo = 'img-seccion' . $this->seccionEditando->id . '.' . $extension;

        if ($this->configuracion->version == 1) {

          // elimino el archivo actual
          Storage::delete('public/' . $this->configuracion->ruta_almacenamiento . '/img' . '/secciones-formulario' . '/' . $this->seccionEditando->logo);

          $this->imagen->storeAs(
            $this->configuracion->ruta_almacenamiento .  '/img' . '/secciones-formulario' . '/',
            $nombreArchivo,
            'public'
          );
        } elseif ($this->configuracion->version == 2) {
          /*
            $s3 = AWS::get('s3');
            $s3->putObject(array(
            'Bucket'     => $_ENV['aws_bucket'],
            'Key'        => $_ENV['aws_carpeta']."/archivos"."/".$nombreArchivo,
            'SourceFile' => "img/temp/archivo-a-temp-".$asistente->id.".".$extension,
            ));*/
        }

        $this->seccionEditando->logo = $nombreArchivo;
        $this->seccionEditando->save();
      }



      $this->dispatch('cerrarModal', nombreModal: 'modalNuevaSeccion');
      $this->modoEdicion = false;
      $this->reset('seccionEditando');

      $this->dispatch(
        'msn',
        msnIcono: 'success',
        msnTitulo: '¡Muy bien!',
        msnTexto: 'La sección fue editada con éxito.'
      );
    }else{
      $seccion = new SeccionFormularioUsuario;
      $seccion->nombre = $validatedData['nombre'];
      $seccion->titulo = $validatedData['título'];
      $seccion->formulario_usuario_id = $this->formulario->id;
      $seccion->icono = $this->icono; // Guardar el icono

      $ultimaSeccionActual = SeccionFormularioUsuario::where('formulario_usuario_id', $this->formulario->id)->orderBy('orden', 'desc')->first();
      $seccion->orden = $ultimaSeccionActual ? $ultimaSeccionActual->orden + 1 : 1;
      $seccion->save();

      // Guardar la imagen (si se proporciona)
      if ($this->imagen) {

        // creo la carpeta si no exite
        $path = public_path('storage/' . $this->configuracion->ruta_almacenamiento . '/img' . '/secciones-formulario' . '/');
        !is_dir($path) && mkdir($path, 0777, true);

        $extension = $this->imagen->extension();
        $nombreArchivo = 'img-seccion' . $seccion->id . '.' . $extension;

        if ($this->configuracion->version == 1) {

          $this->imagen->storeAs(
            $this->configuracion->ruta_almacenamiento .  '/img' . '/secciones-formulario' . '/',
            $nombreArchivo,
            'public'
          );
        } elseif ($this->configuracion->version == 2) {
          /*
            $s3 = AWS::get('s3');
            $s3->putObject(array(
            'Bucket'     => $_ENV['aws_bucket'],
            'Key'        => $_ENV['aws_carpeta']."/archivos"."/".$nombreArchivo,
            'SourceFile' => "img/temp/archivo-a-temp-".$asistente->id.".".$extension,
            ));*/
        }

        $seccion->logo = $nombreArchivo;
        $seccion->save();
      } else {
        // Si no se proporciona imagen, puedes asignar un valor por defecto o dejarlo en blanco
        $seccion->logo = null; // o una ruta por defecto si la tienes
        $seccion->save();
      }

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

  #[On('imagenSeleccionada')]
  public function actualizarImagen($file)
  {
    $this->imagen = $file; // Actualizar la propiedad $imagen
  }

  public function actualizarOrdenCampos($campoId, $seccionOrigenId, $seccionDestinoId, $ordenOrigen, $ordenDestino)
  {
      $this->seccionesActivas = [$seccionDestinoId,$seccionOrigenId];

      $seccionDestinoId = (int) $seccionDestinoId;

      // Obtener el registro pivot actual
      $campoPivot = DB::table('campo_seccion_formulario_usuario')
      ->where('campo_id', $campoId)
      ->where('seccion_id', $seccionOrigenId)
      ->update(['seccion_id' => $seccionDestinoId]);

      // Actualizar orden en la sección de destino
      $seccionDestino = SeccionFormularioUsuario::find($seccionDestinoId);

      $ordenDestino = array_filter($ordenDestino);
      foreach ($ordenDestino as $index => $id) {
        $this->respuesta = $seccionDestino->campos()->get();
        $seccionDestino->campos()->updateExistingPivot($id, ['orden' => $index + 1]);
      }

      //Si la sección de origen es diferente a la de destino
      if ($seccionOrigenId!== $seccionDestinoId) {
        $seccionOrigen = SeccionFormularioUsuario::find($seccionOrigenId);
        $ordenOrigen = array_filter($ordenOrigen);
        foreach ($ordenOrigen as $index => $id) {
          $seccionOrigen->campos()->updateExistingPivot($id, ['orden' => $index + 1]);
        }
      }
  }

  public function subirSeccion($id)
  {
    $this->seccionesActivas = [];
    $seccionActual = SeccionFormularioUsuario::where('formulario_usuario_id', $this->formulario->id)->find($id);
    $seccionAnterior = SeccionFormularioUsuario::where('formulario_usuario_id', $this->formulario->id)->where('orden', '<', $seccionActual->orden)->orderBy('orden', 'desc')->first();

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
    $seccionActual = SeccionFormularioUsuario::where('formulario_usuario_id', $this->formulario->id)->find($id);
    $seccionSiguiente = SeccionFormularioUsuario::where('formulario_usuario_id', $this->formulario->id)->where('orden', '>', $seccionActual->orden)->orderBy('orden')->first();

    if ($seccionSiguiente) {
        $ordenSiguiente = $seccionSiguiente->orden;
        $seccionSiguiente->orden = $seccionActual->orden;
        $seccionActual->orden = $ordenSiguiente;

        $seccionActual->save();
        $seccionSiguiente->save();
    }
  }

  public function eliminarCampo($campoId, $seccionId)
  {

    $seccion = SeccionFormularioUsuario::find($seccionId);
    $seccion->campos()->detach($campoId);

    // Si el campo eliminado era el que se estaba editando, lo limpiamos del estado.
    if ($this->campoEditando && $this->campoEditando->id == $campoId) {
        $this->reset('campoEditando', 'modoEdicionCampo');
    }
  }

  public function eliminarSeccion($seccionId)
  {
    $seccion = SeccionFormularioUsuario::find($seccionId);

    if ($seccion) {
        $formularioId = $seccion->formulario_usuario_id;
        $ordenEliminado = $seccion->orden;

        // elimino el archivo actual
        if ($this->configuracion->version == 1) {
            Storage::delete('public/' . $this->configuracion->ruta_almacenamiento . '/img' . '/secciones-formulario' . '/' . $seccion->logo);
        } elseif ($this->configuracion->version == 2) {


        }

        //Desvincula todos los campos
        $seccion->campos()->detach();

        // Si la sección eliminada era la que se estaba editando, la limpiamos del estado.
        if ($this->seccionEditando && $this->seccionEditando->id == $seccionId) {
            $this->reset('seccionEditando');
        }

        $seccion->delete();

        // Reordenar secciones restantes
        $seccionesRestantes = SeccionFormularioUsuario::where('formulario_usuario_id', $formularioId)
            ->where('orden', '>', $ordenEliminado)
            ->orderBy('orden')
            ->get();

        foreach ($seccionesRestantes as $s) {
            $s->orden = $s->orden - 1;
            $s->save();
        }
    }
  }

  #[On('obtenerCampoSeleccionado')]
  public function obtenerCampoSeleccionado($id)
  {
    $this->campo = $id;
  }

  public function render()
  {
    $this->secciones = $this->formulario->secciones()->orderBy('orden','asc')->get();
      return view('livewire.formularios-para-usuarios.gestionar-secciones-y-campos');
  }
}
