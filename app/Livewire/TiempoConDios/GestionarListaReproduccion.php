<?php

namespace App\Livewire\TiempoConDios;

use App\Models\Album;
use App\Models\Cancion;
use App\Models\Configuracion;
use Livewire\Component;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;

class GestionarListaReproduccion extends Component
{
  public $canciones = [], $albumes = [], $configuracion, $busqueda = '', $busquedaAlbumes, $ejemplo = "sdf";

  /* Campos para el funcionamiento de editar y crear de la cancion */
  public $nombre;
  public $artista;
  public $álbum;
  public $archivo;
  public $modoEdicionCancion = false;
  public $cancionEditando;

  /* Propiedades de subida de audio en Base64 con Alpine */
  public $archivoBase64;
  public $archivoNombre;

   /* Campos para el funcionamiento de editar y crear album */
  public $nombreÁlbum;
  public $imagen;
  public $modoEdicionAlbum = false;
  public $albumEditando;

  /* Propiedades de subida de imagen de álbum en Base64 con Alpine */
  public $imagenBase64;
  public $imagenNombre;

  protected $rules = [
    'nombre' => 'required',
    'artista' => 'required',
  ];

  protected $rulesEditar = [
    'nombre' => 'required',
    'artista' => 'required',
  ];

   protected $rulesAlbum = [
    'nombreÁlbum' => 'required',
  ];

  public function mount(): void
  {
    $this->configuracion = Configuracion::first();
  }

  // esta funcion prepara las variables para abrir el modal de crearCancion
  public function crearCancion(): void
  {
    $this->álbum = null;
    $this->modoEdicionCancion = false;
    $this->reset(['nombre', 'artista', 'archivoBase64', 'archivoNombre']);
    $this->dispatch('quitarSeleccion')->to(SelectorDeAlbumes::class);
    $this->dispatch('abrirModal', nombreModal: 'modalNuevaEditarCancion');
    $this->cancionEditando = null;
  }

   // esta funcion prepara las variables para abrir el modal de editarCancion
  public function editarCancion(int|string $cancionId): void
  {
    $this->cancionEditando = Cancion::find($cancionId);
    $this->modoEdicionCancion = true;

    // formateo el formulario
    $this->reset(['nombre', 'artista', 'archivoBase64', 'archivoNombre']);
    $this->álbum = null;
    $this->dispatch('quitarSeleccion')->to(SelectorDeAlbumes::class);

    if ($this->cancionEditando->album_id) {
        $this->dispatch('seleccionarAlbum', $this->cancionEditando->album_id)->to(SelectorDeAlbumes::class);
    }

    //fin formateo formulario

    $this->nombre = $this->cancionEditando->nombre;
    $this->artista = $this->cancionEditando->artista;
    $this->dispatch('abrirModal', nombreModal: 'modalNuevaEditarCancion');
  } 
  
  // esta funcion guarda o edita los datos en la BD
  public function guardarCancion(): void
  {
    if ($this->modoEdicionCancion) {
      // Valido campos de texto
      $validatedData = Validator::make($this->all(), $this->rulesEditar)->validate();

      // Validar extensión del archivo si se proporciona uno nuevo
      if ($this->archivoBase64) {
        $extension = pathinfo($this->archivoNombre, PATHINFO_EXTENSION);
        if (!in_array(strtolower($extension), ['mp3', 'wav', 'mp4'])) {
            $this->addError('archivo', 'El formato del archivo debe ser mp3, wav o mp4.');
            return;
        }
      }

      // Actualizar la sección existente
      $this->cancionEditando->nombre = $this->nombre;
      $this->cancionEditando->artista = $this->artista;
      $this->cancionEditando->album_id = $this->álbum ?: null;
      $this->cancionEditando->save();

      // Guardar audio (si se proporciona)
      if ($this->archivoBase64) {
        $extension = pathinfo($this->archivoNombre, PATHINFO_EXTENSION);
        $nombreArchivo = 'cancion' . $this->cancionEditando->id . '.' . strtolower($extension);

        // Eliminar archivo actual
        if ($this->cancionEditando->archivo && $this->cancionEditando->archivo !== 'temporal.mp3' && Storage::disk('public')->exists('archivos/reproductor/' . $this->cancionEditando->archivo)) {
            Storage::disk('public')->delete('archivos/reproductor/' . $this->cancionEditando->archivo);
        }

        $this->guardarArchivoBase64($this->archivoBase64, 'archivos/reproductor/' . $nombreArchivo);

        $this->cancionEditando->archivo = $nombreArchivo;
        $this->cancionEditando->save();
      }

      $this->reset(['nombre', 'artista', 'archivoBase64', 'archivoNombre', 'álbum']);
      $this->dispatch('quitarSeleccion')->to(SelectorDeAlbumes::class);
      $this->dispatch('cerrarModal', nombreModal: 'modalNuevaEditarCancion');
      $this->modoEdicionCancion = false;

      $this->dispatch(
        'msn',
        msnIcono: 'success',
        msnTitulo: '¡Muy bien!',
        msnTexto: 'La sección fue editada con exito.'
      );
    } else {
      // Valido campos de texto
      $validatedData = Validator::make($this->all(), $this->rules)->validate();

      // Validar presencia y extensión del archivo obligatoriamente al crear
      if (!$this->archivoBase64) {
        $this->addError('archivo', 'El archivo de audio es obligatorio.');
        return;
      }

      $extension = pathinfo($this->archivoNombre, PATHINFO_EXTENSION);
      if (!in_array(strtolower($extension), ['mp3', 'wav', 'mp4'])) {
        $this->addError('archivo', 'El formato del archivo debe ser mp3, wav o mp4.');
        return;
      }

      $cancion = new Cancion;
      $cancion->nombre = $validatedData['nombre'];
      $cancion->artista = $validatedData['artista'];

      if ($this->álbum) {
        $cancion->album_id = $this->álbum;
      }

      $ultimaCancion = Cancion::orderBy('orden', 'desc')->first();
      $cancion->orden = $ultimaCancion ? $ultimaCancion->orden + 1 : 1;
      $cancion->archivo = 'temporal.mp3';
      $cancion->save();

      $nombreArchivo = 'cancion' . $cancion->id . '.' . strtolower($extension);

      $this->guardarArchivoBase64($this->archivoBase64, 'archivos/reproductor/' . $nombreArchivo);

      $cancion->archivo = $nombreArchivo;
      $cancion->save();

      $this->reset(['nombre', 'artista', 'archivoBase64', 'archivoNombre', 'álbum']);
      $this->dispatch('quitarSeleccion')->to(SelectorDeAlbumes::class);
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

  public function eliminarCancion(int|string $cancioId): void
  {
    $cancion = Cancion::find($cancioId);

    if ($cancion) {
      if ($cancion->archivo && $cancion->archivo !== 'temporal.mp3' && Storage::disk('public')->exists('archivos/reproductor/' . $cancion->archivo)) {
          Storage::disk('public')->delete('archivos/reproductor/' . $cancion->archivo);
      }
      $cancion->delete();
    }
  }

  #[On('obtenerAlbumSeleccionado')]
  public function obtenerAlbumSeleccionado(mixed $id): void
  {
    $this->álbum = $id;
  }

  public function actualizarOrden(string $nuevaOrden): void
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
  public function abrirGestionarAlbum(): void
  {
    $this->dispatch('abrirModal', nombreModal: 'modalGestionarAlbum');
  }

  //  esta funcion prepara las variables para abrir el modal para crear album
  public function crearAlbum(): void
  {
    $this->modoEdicionAlbum = false;
    $this->reset(['nombreÁlbum', 'imagen', 'imagenBase64', 'imagenNombre']);
    $this->albumEditando = null;
    $this->dispatch('cerrarModal', nombreModal: 'modalGestionarAlbum');
    $this->dispatch('abrirModal', nombreModal: 'modalNuevaEditarAlbum');
  }

  //  esta funcion prepara las variables para abrir el modal para editar album
  public function editarAlbum(int|string $albumId): void
  {
    $this->modoEdicionAlbum = true;
    $this->reset(['nombreÁlbum', 'imagen', 'imagenBase64', 'imagenNombre']);
    $this->albumEditando = Album::find($albumId);
    $this->nombreÁlbum = $this->albumEditando->nombre;
    $this->dispatch('cerrarModal', nombreModal: 'modalGestionarAlbum');
    $this->dispatch('abrirModal', nombreModal: 'modalNuevaEditarAlbum');
  }

  // esta funcion guarda o edita los datos del album en la BD
  public function guardarAlbum(): void
  {
    $validatedData = Validator::make($this->all(), $this->rulesAlbum)->validate();

    if ($this->imagenBase64) {
      $extension = pathinfo($this->imagenNombre, PATHINFO_EXTENSION);
      if (!in_array(strtolower($extension), ['jpg', 'png', 'jpeg'])) {
          $this->addError('imagen', 'El formato de la imagen debe ser jpg, jpeg o png.');
          return;
      }
    }

    if ($this->modoEdicionAlbum) {
      // Actualizar la sección existente
      $this->albumEditando->nombre = $this->nombreÁlbum;
      $this->albumEditando->save();

      // Guardar imagen (si se proporciona)
      if ($this->imagenBase64) {
        $extension = pathinfo($this->imagenNombre, PATHINFO_EXTENSION);
        $nombreArchivo = 'album' . $this->albumEditando->id . '.' . strtolower($extension);

        // elimino el archivo actual
        if ($this->albumEditando->imagen && $this->albumEditando->imagen !== 'temporal.png' && $this->albumEditando->imagen !== 'album-default.png' && Storage::disk('public')->exists('img/reproductor/' . $this->albumEditando->imagen)) {
            Storage::disk('public')->delete('img/reproductor/' . $this->albumEditando->imagen);
        }

        $this->guardarArchivoBase64($this->imagenBase64, 'img/reproductor/' . $nombreArchivo);

        $this->albumEditando->imagen = $nombreArchivo;
        $this->albumEditando->save();
      }

      $this->reset(['imagenBase64', 'imagenNombre']);
      $this->dispatch('cerrarModal', nombreModal: 'modalNuevaEditarAlbum');
      $this->dispatch('abrirModal', nombreModal: 'modalGestionarAlbum');
      $this->modoEdicionAlbum = false;

      $this->dispatch(
        'msn',
        msnIcono: 'success',
        msnTitulo: '¡Muy bien!',
        msnTexto: 'El álbum fue editado con éxito.'
      );
    } else {
      $album = new Album;
      $album->nombre = $validatedData['nombreÁlbum'];
      $album->imagen = 'temporal.png';
      $album->save();

      if ($this->imagenBase64) {
        $extension = pathinfo($this->imagenNombre, PATHINFO_EXTENSION);
        $nombreArchivo = 'album' . $album->id . '.' . strtolower($extension);

        $this->guardarArchivoBase64($this->imagenBase64, 'img/reproductor/' . $nombreArchivo);

        $album->imagen = $nombreArchivo;
      } else {
        $album->imagen = null;
      }
      $album->save();

      $this->reset(['imagenBase64', 'imagenNombre']);
      $this->dispatch('cerrarModal', nombreModal: 'modalNuevaEditarAlbum');
      $this->dispatch('abrirModal', nombreModal: 'modalGestionarAlbum');
      $this->modoEdicionAlbum = false;

      $this->dispatch(
        'msn',
        msnIcono: 'success',
        msnTitulo: '¡Muy bien!',
        msnTexto: 'El álbum fue creado con éxito.'
      );
    }
  }

  public function eliminarAlbum(int|string $albumId): void
  {
    $album = Album::find($albumId);

    if ($album) {
      if ($album->imagen && $album->imagen !== 'temporal.png' && $album->imagen !== 'album-default.png' && Storage::disk('public')->exists('img/reproductor/' . $album->imagen)) {
          Storage::disk('public')->delete('img/reproductor/' . $album->imagen);
      }

      foreach ($album->canciones as $cancion) {
        $cancion->album_id = null;
        $cancion->save();
      }

      $album->delete();
    }
  }

  private function guardarArchivoBase64(string $base64String, string $rutaDestino): void
  {
    $datos = explode(',', $base64String);
    $decodificado = base64_decode(end($datos));
    Storage::disk('public')->put($rutaDestino, $decodificado);
  }

  public function render(): \Illuminate\Contracts\View\View
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
