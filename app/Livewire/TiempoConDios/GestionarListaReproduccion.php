<?php

namespace App\Livewire\TiempoConDios;

use App\Models\Album;
use App\Models\Cancion;
use App\Models\Configuracion;
use Livewire\Component;

use Illuminate\Support\Facades\Validator;
use Livewire\WithFileUploads;


use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;

class GestionarListaReproduccion extends Component
{
  use WithFileUploads;

  public $canciones = [], $albumes = [], $configuracion, $busqueda = '', $busquedaAlbumes, $ejemplo = "sdf";

  /* Campos para el funcionamiento de editar y crear de la cancion */
  public $nombre;
  public $artista;
  public $álbum;
  public $archivo;
  public $modoEdicionCancion = false;
  public $cancionEditando;

   /* Campos para el funcionamiento de editar y crear album */
  public $nombreÁlbum;
  public $imagen;
  public $modoEdicionAlbum = false;
  public $albumEditando;


  protected $rules = [
    'nombre' => 'required',
    'artista' => 'required',
    'archivo' => [
      'required',
      'file',
      'mimes:mp3,mp4,wav'  // Validar que el tipo MIME es MP3
    ]
  ];

  protected $rulesEditar = [
    'nombre' => 'required',
    'artista' => 'required',
    'archivo' => [
      'nullable',
      'file',
      'mimes:mp3,mp4,wav'  // Validar que el tipo MIME es MP3
    ]
  ];

   protected $rulesAlbum = [
    'nombreÁlbum' => 'required',
    'imagen' => [
        'nullable', // El campo imagen es opcional
        'image',
        'mimes:jpg,png' // Validar formato
    ]
  ];

  public function mount()
  {
    $this->configuracion = Configuracion::first();
  }

  // esta funcion prepara las variables para abrir el modal de crearCancion
  public function crearCancion()
  {
    $this->álbum = null;
    $this->modoEdicionCancion = false;
    $this->reset(['nombre', 'artista']);
    $this->dispatch('abrirModal', nombreModal: 'modalNuevaEditarCancion');
    $this->cancionEditando;
  }

   // esta funcion prepara las variables para abrir el modal de editarCancion
  public function editarCancion($cancionId)
  {
    $this->cancionEditando = Cancion::find($cancionId);
    $this->modoEdicionCancion = true;

    // formateo el formulario
    $this->reset(['nombre', 'artista']);
    $this->álbum = null;
    $this->dispatch('quitarSeleccion')->to(SelectorDeAlbumes::class);

    if($this->cancionEditando->album_id)
    $this->dispatch('seleccionarAlbum', $this->cancionEditando->album_id)->to(SelectorDeAlbumes::class);

    //fin formateo formulario

    $this->nombre= $this->cancionEditando->nombre;
    $this->artista= $this->cancionEditando->artista;
    $this->dispatch('abrirModal', nombreModal: 'modalNuevaEditarCancion');
  }

  // esta funcion guarda o edita los datos en la BD
  public function guardarCancion()
  {
    //$this->ejemplo = $this->álbum;

    if ($this->modoEdicionCancion) {
      // //Valido campos
      $validatedData = Validator::make($this->all(), $this->rulesEditar)->validate();

      // Actualizar la sección existente
      $this->cancionEditando->nombre = $this->nombre;
      $this->cancionEditando->artista = $this->artista;
      $this->cancionEditando->album_id = $this->álbum ? $this->álbum : null;
      $this->cancionEditando->save();

      // Guardar  (si se proporciona)
      if ($this->archivo) {

        // creo la carpeta si no exite
        $path = public_path('storage/' . $this->configuracion->ruta_almacenamiento . '/reproductor-audio' . '/audios' . '/');
        !is_dir($path) && mkdir($path, 0777, true);

        $extension = $this->archivo->extension();
        $nombreArchivo = 'cancion' . $this->cancionEditando->id . '.' . $extension;

        if ($this->configuracion->version == 1) {

          // elimino el archivo actual
          Storage::delete('public/' . $this->configuracion->ruta_almacenamiento .  '/reproductor-audio' . '/audios' . '/' . $this->cancionEditando->archivo);

          $this->archivo->storeAs(
            $this->configuracion->ruta_almacenamiento .  '/reproductor-audio' . '/audios' . '/',
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

         $this->cancionEditando->archivo = $nombreArchivo;
         $this->cancionEditando->save();
      }

      $this->dispatch('cerrarModal', nombreModal: 'modalNuevaEditarCancion');
      $this->modoEdicionCancion = false;

      $this->dispatch(
        'msn',
        msnIcono: 'success',
        msnTitulo: '¡Muy bien!',
        msnTexto: 'La sección fue editada con exito.'
      );
    }else{
      //Valido campos
      $validatedData = Validator::make($this->all(), $this->rules)->validate();

      $cancion = new Cancion;
      $cancion->nombre = $validatedData['nombre'];
      $cancion->artista = $validatedData['artista'];

      if($this->álbum)
      $cancion->album_id = $this->álbum;

      $ultimaCancion = Cancion::orderBy('orden', 'desc')->first();
      $cancion->orden = $ultimaCancion ? $ultimaCancion->orden + 1 : 1;
      $cancion->archivo = 'termporal.mp3';
      $cancion->save();


      // creo la carpeta si no exite
      $path = public_path('storage/' . $this->configuracion->ruta_almacenamiento . '/reproductor-audio' . '/audios' . '/');
      !is_dir($path) && mkdir($path, 0777, true);

      $extension = $this->archivo->extension();
      $nombreArchivo = 'cancion' . $cancion->id . '.' . $extension;

      if ($this->configuracion->version == 1) {

        $this->archivo->storeAs(
          $this->configuracion->ruta_almacenamiento .  '/reproductor-audio' . '/audios' . '/',
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

      $cancion->archivo = $nombreArchivo;
      $cancion->save();

      $this->dispatch('cerrarModal', nombreModal: 'modalNuevaEditarCancion');
      $this->modoEdicionCancion = false;

      $this->dispatch(
        'msn',
        msnIcono: 'success',
        msnTitulo: '¡Muy bien!',
        msnTexto: 'La sección fue creada con exito.'
      );
    }
  }

  public function eliminarCancion($cancioId)
  {
    $cancion = Cancion::find($cancioId);

    // elimino el archivo actual
    if ($this->configuracion->version == 1) {
      // elimino el archivo actual
      Storage::delete('public/' . $this->configuracion->ruta_almacenamiento .  '/reproductor-audio' . '/audios' . '/' . $cancion->archivo);
    } elseif ($this->configuracion->version == 2) {

    }

    // Elimina la sección
    if ($cancion) {
      $cancion->delete();
    }
  }

  #[On('obtenerAlbumSeleccionado')]
  public function obtenerAlbumSeleccionado($id)
  {
    //$this->ejemplo = "seleccione : ". $id;
    $this->álbum = $id;
  }

  public function actualizarOrden($nuevaOrden)
  {
    // 1. Decodificar la data recibida
    $ordenes = json_decode($nuevaOrden, true);

    // 2. Iterar sobre el array de orden y actualizar la base de datos
    foreach ($ordenes as $orden) {
        $cancion = Cancion::find($orden['id']);
        $cancion->orden = $orden['orden'];
        $cancion->save();
    }
  }

  // estapara abrir el modal de modalGestionarAlbum
  public function abrirGestionarAlbum()
  {
    $this->dispatch('abrirModal', nombreModal: 'modalGestionarAlbum');
  }

  //  esta funcion prepara las variables para abrir el modal para crear album
  public function crearAlbum()
  {
    $this->modoEdicionAlbum= false;
    $this->reset(['nombreÁlbum', 'imagen']);
    $this->albumEditando = null;
    $this->dispatch('cerrarModal', nombreModal: 'modalGestionarAlbum');
    $this->dispatch('abrirModal', nombreModal: 'modalNuevaEditarAlbum');
  }

  //  esta funcion prepara las variables para abrir el modal para editar album
  public function editarAlbum($albumId)
  {
    $this->modoEdicionAlbum= true;
    $this->reset(['nombreÁlbum', 'imagen']);
    $this->albumEditando = Album::find($albumId);
    $this->nombreÁlbum = $this->albumEditando->nombre;
    $this->dispatch('cerrarModal', nombreModal: 'modalGestionarAlbum');
    $this->dispatch('abrirModal', nombreModal: 'modalNuevaEditarAlbum');
  }

  // esta funcion guarda o edita los datos del album en la BD
  public function guardarAlbum()
  {
    $validatedData = Validator::make($this->all(), $this->rulesAlbum)->validate();

    // Validar dimensiones de la imagen si se proporciona
    if ($this->imagen) {
      // Usar sometimes() en lugar de validate sometimes
      Validator::make($this->all(), [
        'imagen' => Rule::dimensions()->maxWidth(300)->maxHeight(300)
      ])->validate();
    }

    if ($this->modoEdicionAlbum) {

      // Actualizar la sección existente
      $this->albumEditando->nombre = $this->nombreÁlbum;
      $this->albumEditando->save();

      // Guardar  (si se proporciona)
      if ($this->imagen) {

        // creo la carpeta si no exite
        $path = public_path('storage/' . $this->configuracion->ruta_almacenamiento . '/reproductor-audio' . '/imagenes' . '/');
        !is_dir($path) && mkdir($path, 0777, true);

        $extension = $this->imagen->extension();
        $nombreArchivo = 'cancion' . $this->albumEditando->id . '.' . $extension;

        if ($this->configuracion->version == 1) {

          // elimino el archivo actual
          Storage::delete('public/' . $this->configuracion->ruta_almacenamiento .  '/reproductor-audio' . '/imagenes' . '/' . $this->albumEditando->imagen);

          $this->imagen->storeAs(
            $this->configuracion->ruta_almacenamiento .  '/reproductor-audio' . '/imagenes' . '/',
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

        $this->albumEditando->imagen = $nombreArchivo;
        $this->albumEditando->save();
      }

      $this->dispatch('cerrarModal', nombreModal: 'modalNuevaEditarAlbum');
      $this->dispatch('abrirModal', nombreModal: 'modalGestionarAlbum');
      $this->modoEdicionAlbum = false;

      $this->dispatch(
        'msn',
        msnIcono: 'success',
        msnTitulo: '¡Muy bien!',
        msnTexto: 'El álbum fue editado con éxito.'
      );
    }else{

      $album = new Album;
      $album->nombre = $validatedData['nombreÁlbum'];
      $album->imagen = 'temporal.png';
      $album->save();

      // creo la carpeta si no exite
      $path = public_path('storage/' . $this->configuracion->ruta_almacenamiento . '/reproductor-audio' . '/imagenes' . '/');
      !is_dir($path) && mkdir($path, 0777, true);

      $extension = $this->imagen->extension();
      $nombreArchivo = 'cancion' . $album->id . '.' . $extension;

      if ($this->configuracion->version == 1) {

        $this->imagen->storeAs(
          $this->configuracion->ruta_almacenamiento .  '/reproductor-audio' . '/imagenes' . '/',
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

      $album->imagen = $nombreArchivo;
      $album->save();

      $this->dispatch('cerrarModal', nombreModal: 'modalNuevaEditarAlbum');
      $this->dispatch('abrirModal', nombreModal: 'modalGestionarAlbum');
      $this->modoEdicionCancion = false;

      $this->dispatch(
        'msn',
        msnIcono: 'success',
        msnTitulo: '¡Muy bien!',
        msnTexto: 'El álbum fue creado con éxito.'
      );
    }
  }

  public function eliminarAlbum($albumId)
  {
    $album = Album::find($albumId);

    if ($album) {
      // elimino el archivo actual
      if ($this->configuracion->version == 1) {
        // elimino la imagen
        if ($album->imagen) {
          $rutaImagen = 'public/' . $this->configuracion->ruta_almacenamiento . '/reproductor-audio' . '/imagenes' . '/' . $album->imagen;
          if (Storage::exists($rutaImagen)) {
              Storage::delete($rutaImagen);
          }
        }
      } elseif ($this->configuracion->version == 2) {

      }

      foreach ($album->canciones as $cancion)
      {
        $cancion->album_id = null;
        $cancion->save();
      }

      $album->delete();
    }
  }



  public function render()
  {
    $canciones = Cancion::whereRaw('1=1');
    $this->canciones = $canciones->whereRaw("translate(nombre,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜ','aeiouAEIOUaeiouAEIOU') ILIKE '%$this->busqueda%'")
    ->orderBy('orden')
    ->get();

    $albumes = Album::whereRaw('1=1');
    $this->albumes = $albumes->whereRaw("translate(nombre,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜ','aeiouAEIOUaeiouAEIOU') ILIKE '%$this->busquedaAlbumes%'")
    ->orderBy('updated_at','desc')
    ->orderBy('nombre','asc')
    ->get();

    return view('livewire.tiempo-con-dios.gestionar-lista-reproduccion');
  }
}
