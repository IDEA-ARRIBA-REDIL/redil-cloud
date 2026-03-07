<?php

namespace App\Livewire\TiempoConDios;
use Illuminate\Support\Facades\Storage;

use App\Models\Cancion;
use App\Models\Configuracion;
use Livewire\Component;
use Livewire\Attributes\On;

class Reproductor extends Component
{
    public $class = "";

    public $configuracion = null;
    public $cancionActual = null;
    public $imgAlbumActual = 'album-default.png';
    //public $estaReproduciendo = false;
    public $iconoPlayPause = "play";
    public $duracionCancionActual = '0:00';
    public $entre = "no";
    public $aleatorio = false;
    public $cancionIndex = 0;
    public $listaCanciones = [];

    public function mount()
    {
      $this->configuracion = Configuracion::first();
      $this->listaCanciones = Cancion::orderBy('orden', 'asc')->get();
      $this->actualizarCancionActual();
       $this->dispatch('cambiar-cancion', src: $this->obtenerRutaAudio());
    }

    private function actualizarCancionActual()
    {
      if (count($this->listaCanciones) > 0) {
        $this->cancionActual = $this->listaCanciones[$this->cancionIndex];
        $this->actualizarImagenAlbum();
      }
    }

    public function abrirPlaylist()
    {
      $this->dispatch('abrirModal', nombreModal: 'modalPlayList');
    }

    public function modoAleatorio()
    {
      if(!$this->aleatorio)
      {
        // Activa el modo aleatorio
        $this->aleatorio = true;
        $this->listaCanciones = Cancion::inRandomOrder()->get();
      }else{
        // Desactiva el modo aleatorio
        $this->aleatorio = false;
        $this->listaCanciones = Cancion::orderBy('orden', 'asc')->get();
      }
      $this->cancionIndex = 0; // Reinicia el índice al cambiar el modo
      $this->actualizarCancionActual();
      $this->dispatch('cambiar-cancion', src: $this->obtenerRutaAudio());
    }

    public function render()
    {
        return view('livewire.tiempo-con-dios.reproductor');
    }

    public function playEspecifico($cancionId)
    {
      $this->actualizarCancionActual();

      // Encuentra el índice de la canción en la lista actual
      $this->cancionIndex = $this->listaCanciones->search(function ($item) use ($cancionId) {
        return $item->id === $cancionId;
      });
      $this->actualizarCancionActual();
      $this->iconoPlayPause = 'pause'; // Asegúrate de que el reproductor no esté en pausa
      $this->dispatch('cambiar-cancion-play', src: $this->obtenerRutaAudio()); // Emite el evento para cambiar la canción en el reproductor
      $this->dispatch('cerrarModal', nombreModal: 'modalPlayList');
    }

    public function anteriorCancion()
    {
      $this->iconoPlayPause = 'pause';
      $this->cancionIndex--;
      if ($this->cancionIndex < 0) {
          $this->cancionIndex = count($this->listaCanciones) - 1; // Volver al final
      }
      $this->actualizarCancionActual();
      $this->dispatch('cambiar-cancion-play', src: $this->obtenerRutaAudio());
    }

    public function siguienteCancion()
    {
      $this->iconoPlayPause = 'pause';
      $this->cancionIndex++;
      if ($this->cancionIndex >= count($this->listaCanciones)) {
          $this->cancionIndex = 0; // Volver al principio
      }
      $this->actualizarCancionActual();
      $this->dispatch('cambiar-cancion-play', src: $this->obtenerRutaAudio());
    }

    private function obtenerRutaAudio()
    {
        return $this->configuracion->version == 1
            ? Storage::url($this->configuracion->ruta_almacenamiento . '/reproductor-audio/audios/' . $this->cancionActual->archivo)
            : Storage::url($this->configuracion->ruta_almacenamiento . '/reproductor-audio/audios/' . $this->cancionActual->archivo);
    }

    // Método para manejar el evento
    public function actualizarDuracionCancion($event)
    {
        $this->duracionCancionActual = $event['duracion'];
    }

    #[On('pausarExterno')]
    public function pausarExterno(){
      $this->dispatch('pausar');
    }

    private function actualizarImagenAlbum()
    {
        $this->imgAlbumActual = $this->cancionActual->album ? $this->cancionActual->album->imagen : 'album-default.png';
    }
}
