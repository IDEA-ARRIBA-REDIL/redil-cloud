<?php

namespace App\Livewire\TiempoConDios;

use App\Helpers\Helpers;
use App\Models\Album;
use App\Models\Cancion;
use App\Models\Configuracion;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class GestionarListaReproduccion extends Component
{
    use WithFileUploads;

    public $canciones = [];

    public $albumes = [];

    public $todosLosAlbumes = [];

    public $configuracion;

    public $busqueda = '';

    public $busquedaAlbumes;

    public $filtroAlbum = '';

    public $ejemplo = 'sdf';

    /* Campos para el funcionamiento de editar y crear de la cancion */
    public $nombre;

    public $artista;

    public $album;

    public $archivo;

    public $modoEdicionCancion = false;

    public $cancionEditando;

    /* Campos para el funcionamiento de editar y crear album */
    public $nombreAlbum;

    public $imagen;

    public $modoEdicionAlbum = false;

    public $albumEditando;

    protected $rules = [
        'nombre' => 'required',
        'artista' => 'required',
    ];

    protected $rulesEditar = [
        'nombre' => 'required',
        'artista' => 'required',
    ];

    protected $rulesAlbum = [
        'nombreAlbum' => 'required',
    ];

    public function mount(): void
    {
        $this->configuracion = Configuracion::first();
    }

    // esta funcion prepara las variables para abrir el modal de crearCancion
    public function crearCancion(): void
    {
        $this->album = null;
        $this->modoEdicionCancion = false;
        $this->reset(['nombre', 'artista', 'archivo']);
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
        $this->reset(['nombre', 'artista', 'archivo']);
        $this->album = null;
        $this->dispatch('quitarSeleccion')->to(SelectorDeAlbumes::class);

        if ($this->cancionEditando->album_id) {
            $this->dispatch('seleccionarAlbum', $this->cancionEditando->album_id)->to(SelectorDeAlbumes::class);
        }

        // fin formateo formulario

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
            if ($this->archivo) {
                $extension = strtolower($this->archivo->getClientOriginalExtension());
                if (! in_array($extension, ['mp3', 'wav', 'mp4'])) {
                    $this->addError('archivo', 'El formato del archivo debe ser mp3, wav o mp4.');

                    return;
                }
            }

            // Actualizar la canción existente
            $this->cancionEditando->nombre = $this->nombre;
            $this->cancionEditando->artista = $this->artista;
            $this->cancionEditando->album_id = $this->album ?: null;
            $this->cancionEditando->save();

            // Guardar audio (si se proporciona)
            if ($this->archivo) {
                $extension = strtolower($this->archivo->getClientOriginalExtension());
                $nombreArchivo = 'cancion_'.$this->cancionEditando->id.'_'.time().'.'.$extension;

                // Eliminar archivo actual
                if ($this->cancionEditando->archivo && $this->cancionEditando->archivo !== 'temporal.mp3' && Storage::disk('public')->exists('archivos/reproductor/'.$this->cancionEditando->archivo)) {
                    Storage::disk('public')->delete('archivos/reproductor/'.$this->cancionEditando->archivo);
                }

                $this->archivo->storeAs('archivos/reproductor', $nombreArchivo, 'public');

                $this->cancionEditando->archivo = $nombreArchivo;
                $this->cancionEditando->touch();
                $this->cancionEditando->save();
            }

            $this->reset(['nombre', 'artista', 'archivo', 'album']);
            $this->dispatch('quitarSeleccion')->to(SelectorDeAlbumes::class);
            $this->dispatch('cerrarModal', nombreModal: 'modalNuevaEditarCancion');
            $this->modoEdicionCancion = false;

            $this->dispatch(
                'msn',
                msnIcono: 'success',
                msnTitulo: '¡Muy bien!',
                msnTexto: 'La canción fue editada con éxito.'
            );
        } else {
            // Valido campos de texto
            $validatedData = Validator::make($this->all(), $this->rules)->validate();

            // Validar presencia y extensión del archivo obligatoriamente al crear
            if (! $this->archivo) {
                $this->addError('archivo', 'El archivo de audio es obligatorio.');

                return;
            }

            $extension = strtolower($this->archivo->getClientOriginalExtension());
            if (! in_array($extension, ['mp3', 'wav', 'mp4'])) {
                $this->addError('archivo', 'El formato del archivo debe ser mp3, wav o mp4.');

                return;
            }

            $cancion = new Cancion;
            $cancion->nombre = $validatedData['nombre'];
            $cancion->artista = $validatedData['artista'];

            if ($this->album) {
                $cancion->album_id = $this->album;
            }

            $ultimaCancion = Cancion::orderBy('orden', 'desc')->first();
            $cancion->orden = $ultimaCancion ? $ultimaCancion->orden + 1 : 1;
            $cancion->archivo = 'temporal.mp3';
            $cancion->save();

            $nombreArchivo = 'cancion_'.$cancion->id.'_'.time().'.'.$extension;

            $this->archivo->storeAs('archivos/reproductor', $nombreArchivo, 'public');

            $cancion->archivo = $nombreArchivo;
            $cancion->save();

            $this->reset(['nombre', 'artista', 'archivo', 'album']);
            $this->dispatch('quitarSeleccion')->to(SelectorDeAlbumes::class);
            $this->dispatch('cerrarModal', nombreModal: 'modalNuevaEditarCancion');
            $this->modoEdicionCancion = false;

            $this->dispatch(
                'msn',
                msnIcono: 'success',
                msnTitulo: '¡Muy bien!',
                msnTexto: 'La canción fue creada con éxito.'
            );
        }
    }

    public function eliminarCancion(int|string $cancionId): void
    {
        $cancion = Cancion::find($cancionId);

        if ($cancion) {
            if ($cancion->archivo && $cancion->archivo !== 'temporal.mp3' && Storage::disk('public')->exists('archivos/reproductor/'.$cancion->archivo)) {
                Storage::disk('public')->delete('archivos/reproductor/'.$cancion->archivo);
            }
            $cancion->delete();
        }
    }

    #[On('obtenerAlbumSeleccionado')]
    public function obtenerAlbumSeleccionado(mixed $id): void
    {
        $this->album = $id;
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
        $this->reset(['nombreAlbum', 'imagen']);
        $this->albumEditando = null;
        $this->dispatch('cerrarModal', nombreModal: 'modalGestionarAlbum');
        $this->dispatch('abrirModal', nombreModal: 'modalNuevaEditarAlbum');
    }

    //  esta funcion prepara las variables para abrir el modal para editar album
    public function editarAlbum(int|string $albumId): void
    {
        $this->modoEdicionAlbum = true;
        $this->reset(['nombreAlbum', 'imagen']);
        $this->albumEditando = Album::find($albumId);
        $this->nombreAlbum = $this->albumEditando->nombre;
        $this->dispatch('cerrarModal', nombreModal: 'modalGestionarAlbum');
        $this->dispatch('abrirModal', nombreModal: 'modalNuevaEditarAlbum');
    }

    // esta funcion guarda o edita los datos del album en la BD
    public function guardarAlbum(): void
    {
        $validatedData = Validator::make($this->all(), $this->rulesAlbum)->validate();

        if ($this->imagen) {
            $extension = strtolower($this->imagen->getClientOriginalExtension());
            if (! in_array($extension, ['jpg', 'png', 'jpeg'])) {
                $this->addError('imagen', 'El formato de la imagen debe ser jpg, jpeg o png.');

                return;
            }
        }

        if ($this->modoEdicionAlbum) {
            // Actualizar el álbum existente
            $this->albumEditando->nombre = $this->nombreAlbum;
            $this->albumEditando->touch();
            $this->albumEditando->save();

            // Guardar imagen (si se proporciona)
            if ($this->imagen) {
                $extension = strtolower($this->imagen->getClientOriginalExtension());
                $nombreArchivo = 'album_'.$this->albumEditando->id.'_'.time().'.'.$extension;

                // elimino el archivo actual
                if ($this->albumEditando->imagen && $this->albumEditando->imagen !== 'temporal.png' && $this->albumEditando->imagen !== 'album-default.png' && Storage::disk('public')->exists('img/reproductor/'.$this->albumEditando->imagen)) {
                    Storage::disk('public')->delete('img/reproductor/'.$this->albumEditando->imagen);
                }

                $this->imagen->storeAs('img/reproductor', $nombreArchivo, 'public');

                $this->albumEditando->imagen = $nombreArchivo;
                $this->albumEditando->touch();
                $this->albumEditando->save();
            }

            $this->reset(['nombreAlbum', 'imagen']);
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
            $album->nombre = $validatedData['nombreAlbum'];
            $album->imagen = 'temporal.png';
            $album->save();

            if ($this->imagen) {
                $extension = strtolower($this->imagen->getClientOriginalExtension());
                $nombreArchivo = 'album_'.$album->id.'_'.time().'.'.$extension;

                $this->imagen->storeAs('img/reproductor', $nombreArchivo, 'public');

                $album->imagen = $nombreArchivo;
            } else {
                $album->imagen = null;
            }
            $album->touch();
            $album->save();

            $this->reset(['nombreAlbum', 'imagen']);
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
            if ($album->imagen && $album->imagen !== 'temporal.png' && $album->imagen !== 'album-default.png' && Storage::disk('public')->exists('img/reproductor/'.$album->imagen)) {
                Storage::disk('public')->delete('img/reproductor/'.$album->imagen);
            }

            foreach ($album->canciones as $cancion) {
                $cancion->album_id = null;
                $cancion->save();
            }

            $album->delete();
        }
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        $this->todosLosAlbumes = Album::orderBy('nombre', 'asc')->get();

        $cancionesQuery = Cancion::with('album');

        // 1. Filtro por Álbum
        if ($this->filtroAlbum !== '' && $this->filtroAlbum !== null) {
            if ($this->filtroAlbum === 'sin-album') {
                $cancionesQuery->whereNull('album_id');
            } else {
                $cancionesQuery->where('album_id', $this->filtroAlbum);
            }
        }

        // 2. Buscador insensible a acentos, mayúsculas y minúsculas en canción, artista y álbum
        if (! empty(trim($this->busqueda))) {
            $busquedaLimpia = trim($this->busqueda);
            $busquedaSaneada = Helpers::sanearStringConEspacios($busquedaLimpia);
            $busquedaSaneada = str_replace(["'"], '', $busquedaSaneada);
            $palabras = array_filter(explode(' ', $busquedaSaneada));

            foreach ($palabras as $palabra) {
                $palabraSafe = addslashes($palabra);
                $cancionesQuery->where(function ($q) use ($palabraSafe) {
                    $q->whereRaw("translate(canciones.nombre, 'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜ', 'aeiouAEIOUaeiouAEIOU') ILIKE '%$palabraSafe%'")
                        ->orWhereRaw("translate(canciones.artista, 'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜ', 'aeiouAEIOUaeiouAEIOU') ILIKE '%$palabraSafe%'")
                        ->orWhereHas('album', function ($qAlbum) use ($palabraSafe) {
                            $qAlbum->whereRaw("translate(albumes.nombre, 'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜ', 'aeiouAEIOUaeiouAEIOU') ILIKE '%$palabraSafe%'");
                        });
                });
            }
        }

        $this->canciones = $cancionesQuery->orderBy('orden')->get();

        // 3. Consulta de Álbumes para el modal de Gestión de Álbumes
        $albumesQuery = Album::withCount('canciones');
        if ($this->busquedaAlbumes) {
            $busquedaAlbumSaneada = Helpers::sanearStringConEspacios(trim($this->busquedaAlbumes));
            $busquedaAlbumSaneada = str_replace(["'"], '', $busquedaAlbumSaneada);
            $albumesQuery->whereRaw("translate(nombre,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜ','aeiouAEIOUaeiouAEIOU') ILIKE '%$busquedaAlbumSaneada%'");
        }
        $this->albumes = $albumesQuery->orderBy('updated_at', 'desc')
            ->orderBy('nombre', 'asc')
            ->get();

        return view('livewire.tiempo-con-dios.gestionar-lista-reproduccion');
    }
}
