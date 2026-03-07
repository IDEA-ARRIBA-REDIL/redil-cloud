<?php

namespace App\Livewire\TiempoConDios;

use App\Helpers\Helpers;
use Livewire\Component;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class Biblia extends Component
{
    public $class="", $name_id;
    public $libros;
    public $librosAt;
    public $librosNt;
    public $versiones;
    public $capituloSeleccionado, $libroSeleccionado, $versionSeleccionada;
    public $selectCapitulo, $selectLibro, $selectVersion;
    public $capitulos = [];

    public $subrayadosTemp = [];

    public $subrayados = [];
    public $subrayadosData = [];

    public $versiculosResaltados = [];
    public $despacharEvento = false;



    public function mount()
    {
      $this->libros = Collection::make(Helpers::libros2());
      $this->librosAt = $this->libros->where('testament','Antiguo Testamento');
      $this->librosNt = $this->libros->where('testament','Nuevo Testamento');
      //$this->capituloSeleccionado = collect();

      try {
        $responseVersion = Http::withOptions([
            'timeout' => 3, // Establece un tiempo de espera de 3 segundos
        ])->get('https://bible-api.deno.dev/api/versions');

        if ($responseVersion->failed()) {
           $this->dispatch(
                'msn',
                msnIcono: 'error',
                msnTitulo: '¡Ups!',
                msnTexto: 'La Biblia no responde, por favor intente de nuevo.'
            );
            $this->versiones = collect(); // Limpiar las versiones anteriores si existen
        } else {
          $this->versiones = collect($responseVersion->json());
        }
      } catch (\Exception $e) {
         $this->dispatch(
              'msn',
              msnIcono: 'error',
              msnTitulo: '¡Ups!',
              msnTexto: 'La Biblia no responde, por favor intente de nuevo.'
          );
        $this->versiones = collect();; // Limpiar las versiones anteriores si existen
      }

      $this->updatedSelectLibro('');
      $this->updatedSelectCapitulo('');
    }

    public function abrirBiblia()
    {
      $this->subrayadosTemp = [];
      $this->dispatch('abrirModal', nombreModal: 'modalBiblia');
    }

    public function updatedSelectLibro($value)
    {
      if($value)
      {
        $this->subrayadosTemp = [];
        $this->libroSeleccionado = $this->libros->firstWhere('libro', $value);
        $this->capitulos = range(1, $this->libroSeleccionado ? $this->libroSeleccionado->chapters : 0);

        $this->versionSeleccionada = $this->versiones->first();
        $this->updatedSelectCapitulo(1);
      }else {
         $this->libroSeleccionado = null;
         $this->capituloSeleccionado = null;
         $this->capitulos = [];
      }
    }

    public function updatedSelectCapitulo($value)
    {
      if($value)
      {
        $this->subrayadosTemp = [];
        $url = 'https://bible-api.deno.dev'.$this->versionSeleccionada['uri'].'/'.$this->libroSeleccionado->names[0].'/'.$value;

        try {
          $response = Http::withOptions([
            'timeout' => 3, // Establece un tiempo de espera de 3 segundos
          ])->get($url);

          if ($response->failed()) {
            $this->dispatch(
              'msn',
              msnIcono: 'error',
              msnTitulo: '¡Ups!',
              msnTexto: 'La Biblia no responde, por favor intente de nuevo.'
            );
            $this->capituloSeleccionado = collect();
          } else {
            $this->capituloSeleccionado = collect($response->json()) ;
          }
        } catch (\Exception $e) {
          $this->dispatch(
            'msn',
            msnIcono: 'error',
            msnTitulo: '¡Ups!',
            msnTexto: 'La Biblia no responde, por favor intente de nuevo.'
          );
          $this->capituloSeleccionado = collect();
        }
      }
    }

    public function updatedSelectVersion($value)
    {
      $this->subrayadosTemp = [];
      $this->subrayados = [];
      $this->versionSeleccionada = $this->versiones->firstWhere('name', $value);
      $this->updatedSelectCapitulo($this->capituloSeleccionado['chapter']);
    }

    // funcion para cuando doy click en el texto para subrayarlo
    public function toggleSubrayado($numeroVersiculo)
    {
      if (in_array($numeroVersiculo, $this->subrayados)) {
        // Si el versículo ya está subrayado, quítalo
        $this->subrayados = array_diff($this->subrayados, [$numeroVersiculo]);
      } else {
        // Si el versículo no está subrayado, añádelo
        $this->subrayados[] = $numeroVersiculo;

      }

      // Ordena el array de forma ascendente
      sort($this->subrayados);

      // Actualiza el array de versículos subrayados
      $this->actualizarSubrayadosData();
    }

    private function actualizarSubrayadosData()
    {
      if(count($this->subrayados) > 0)
      {
        $rangos = [];
        $start = null;
        $end = null;

        foreach ($this->subrayados as $numero) {
            if ($start === null) {
                $start = $numero;
                $end = $numero;
            } elseif ($numero === $end + 1) {
                $end = $numero;
            } else {
                $rangos[] = ($start === $end) ? (string)$start : (string)$start . '-' . (string)$end;
                $start = $numero;
                $end = $numero;
            }
        }
        
        // Agregar el último rango después del bucle
        if ($start !== null) {
            $rangos[] = ($start === $end) ? (string)$start : (string)$start . '-' . (string)$end;
        }

        $cita = $this->libroSeleccionado->abrev." ".$this->capituloSeleccionado['chapter'].": ".implode('; ', $rangos)." (".strtoupper($this->versionSeleccionada['version']).")";
        $cita_larga = $this->libroSeleccionado->libro." ".$this->capituloSeleccionado['chapter'].": ".implode('; ', $rangos)." (".strtoupper($this->versionSeleccionada['version']).")";

        $this->subrayadosData = [
            'libro' => $this->libroSeleccionado->libro,
            'capitulo' => $this->capituloSeleccionado['chapter'],
            'version' => $this->versionSeleccionada['name'],
            'cita' => $cita,
            'cita_larga' => $cita_larga,
            'versiculos' => []
        ];

        foreach ($this->subrayados as $numeroVersiculo) {
          // Encuentra el versículo correspondiente en $capituloSeleccionado
          foreach ($this->capituloSeleccionado['vers'] as $versiculo) {
              if ($versiculo['number'] == $numeroVersiculo) {
                  $this->subrayadosData['versiculos'][] = [
                      'numero' => $numeroVersiculo,
                      'texto' => $versiculo['verse']
                  ];
                  break; // Detiene el bucle interno cuando se encuentra el versículo
              }
            }
          }
        }else{
          $this->subrayadosData = [];
        }

        if ($this->despacharEvento) {
            $this->dispatch('bibliaSeleccionada', $this->subrayadosData);
        }
    }

    public function resaltar()
    {
      $this->versiculosResaltados[] =  $this->subrayadosData;
      $this->subrayados = [];
    }

    public function verVersiculoResaltado($index)
    {
       // Verifica que el índice sea válido
        if (isset($this->versiculosResaltados[$index])) {
          $x = $this->versiculosResaltados[$index];

          $this->selectLibro = $x['libro'];
          $this->selectCapitulo = $x['capitulo'];
          $this->selectVersion = $x['version'];

          $this->updatedSelectLibro($x['libro']);
          $this->updatedSelectCapitulo($x['capitulo']);
          $this->updatedSelectVersion($x['version']);
          $this->dispatch('abrirModal',  nombreModal: 'modalBiblia');


          foreach ($x['versiculos'] as $versiculo)
          {
            $this->subrayadosTemp[] = $versiculo['numero'];
          }

        }
    }

    public function desmarcar($index)
    {
        // Verifica que el índice sea válido
        if (isset($this->versiculosResaltados[$index])) {
            // Elimina el elemento del array usando el índice
            unset($this->versiculosResaltados[$index]);
            // Reindexa el array para evitar índices faltantes
            $this->versiculosResaltados = array_values($this->versiculosResaltados);
        }
         $this->subrayadosTemp = [];
    }





    public function render()
    {
        return view('livewire.tiempo-con-dios.biblia');
    }
}
