<?php

namespace App\Livewire\Generales;

use Livewire\Component;
use App\Models\Barrio;
use App\Helpers\Helpers;
use App\Models\Localidad;

use Livewire\Attributes\On;

class BarrioLocalidadBuscador extends Component
{
    public $localidadId = '';
    public $barrioId = '';
    public $busqueda = '';
    public $verListaBusqueda = false;
    public $verInputBusqueda = true;
    public $ubicacionSeleccionada = null;
    public $tipoUbicacionSeleccionada= null;
    public $mostrarError = false;
    public $msnError = '';

    public $conPreguntaAdiccional = 'no';
    public $mostrar = false;
    public $class = '';
    public $label = '';
    public $nameId = '';
    public $placeholder = '';
    public $usuario = null;

    public function mount()
    {

      if($this->conPreguntaAdiccional == 'si')
      {
        $vivesEn = old('pregunta_vives_en');
        if($vivesEn)
          $this->mostrar = true;
      }

      $oldUbicacion = old($this->nameId);
      $oldTipoUbicacion = old('tipoUbicacion');

      if($oldUbicacion)
      {
        $this->seleccionarUbicacion ($oldUbicacion, $oldTipoUbicacion);
      }else{

        if($this->usuario){
          $barrioActual = $this->usuario->barrio;
          $localidadActual = $this->usuario->localidad;

          if($this->usuario->barrio && $this->usuario->barrio->id)
          {
            $this->mostrar = true;
            $this->seleccionarUbicacion ($this->usuario->barrio->id, 'Barrio');
          }elseif($this->usuario->localidad && $this->usuario->localidad->id){
            $this->mostrar = true;
            $this->seleccionarUbicacion ($this->usuario->localidad->id, 'Localidad');
          }
        }
      }


    }

    public function desplegarListaBusqueda()
    {
      if(!$this->ubicacionSeleccionada)
      $this->verListaBusqueda = true;
    }

    public function ocultarListaBusqueda()
    {
      if(!$this->ubicacionSeleccionada)
      $this->verListaBusqueda = false;
    }

    public function seleccionarUbicacion($ubicacionId, $tipo)
    {

      if($tipo=="Localidad")
      {
        $this->ubicacionSeleccionada = Localidad::find($ubicacionId);
        $this->tipoUbicacionSeleccionada = $tipo;
      }else{
        $this->ubicacionSeleccionada = Barrio::find($ubicacionId);
        $this->tipoUbicacionSeleccionada = $tipo;
      }

      $this->verInputBusqueda = false;
      $this->verListaBusqueda = false;
    }

    public function quitarSeleccion()
    {
      $this->ubicacionSeleccionada = null;
      $this->tipoUbicacionSeleccionada = null;
      $this->verInputBusqueda = true;
      $this->verListaBusqueda = true;
      $this->busqueda = '';
    }

    #[On('mostrarBuscadorUbicacion')]
    public function mostrarBuscadorUbicacion($cambiarPor)
    {
      $this->mostrar = $cambiarPor;
      $this->quitarSeleccion();
    }

    #[On('mostrarMensajeError')]
    public function mostrarMensajeError($mostrarError, $msnError)
    {
      $this->mostrarError = $mostrarError;
      $this->msnError = $msnError;
    }

    public function render()
    {
      $ubicaciones  = null;
      if ($this->busqueda && strlen($this->busqueda) >= 3) {
        $buscar = htmlspecialchars($this->busqueda);
        $buscar = Helpers::sanearStringConEspacios($buscar);
        $buscar = str_replace(["'"], '', $buscar);

        //barrios
        $barrios = Barrio::whereRaw("translate(nombre,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜ','aeiouAEIOUaeiouAEIOU') ILIKE '%$buscar%'")
        ->orderBy('nombre','asc')
        ->take(10)
        ->select('id','nombre','localidad_id', 'municipio_id')
        ->get();

        $barrios->map(function($barrio) {
            $barrio->tipo = 'Barrio';
            $barrio->nombreMunicipio = $barrio->municipio ? $barrio->municipio->nombre : '';
            $nombreBarrio = $barrio->nombre;

            if($barrio->localidad)
            $nombreBarrio.= ", ".$barrio->localidad->nombre;

            $barrio->nombre = $nombreBarrio ;
        });
        // fin barrios

        //localidades
        $localidades = Localidad::whereRaw("translate(nombre,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜ','aeiouAEIOUaeiouAEIOU') ILIKE '%$buscar%'")
        ->orderBy('nombre','asc')
        ->take(10)
        ->select('id','nombre', 'municipio_id')
        ->get();

        $localidades->map(function($localidad) {
            $localidad->tipo = 'Localidad';
            $localidad->nombreMunicipio = $localidad->municipio ? $localidad->municipio->nombre : '';
            $localidad->nombre = $localidad->nombre ;
        });
        // fin localidades

        $ubicaciones = $barrios->concat($localidades);

      }

      return view('livewire.generales.barrio-localidad-buscador', ['ubicaciones' => $ubicaciones]);
    }
}
