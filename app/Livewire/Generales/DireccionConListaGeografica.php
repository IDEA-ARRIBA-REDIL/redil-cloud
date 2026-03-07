<?php

namespace App\Livewire\Generales;

use App\Models\Barrio;
use App\Models\Configuracion;
use App\Models\Iglesia;
use App\Models\Localidad;
use App\Models\Municipio;
use App\Models\Pais;
use App\Models\Region;
use App\Models\Sede;
use App\Models\TipoFormatoDireccion;
use Livewire\Component;

class DireccionConListaGeografica extends Component
{
    public $formulario, $modulo, $usuario, $grupo, $sede;
    public $classDireccion, $obligatorioDireccion, $labelDireccion;
    public $paisSelect = '', $barrioSelect = '', $ciudadSelect = '';
    public $paises, $barrios, $ciudades, $tiposFormatoDireccion;
    public $respuesta = 'sdfsdf';
    public $otroBarrio = '', $direccion = '';
    public $direccionParte1 = '', $direccionParte2 = '', $direccionParte3 = '', $direccionParte4 = '', $direccionParte5 = '';

    public function mount()
    {
      $this->ciudades = collect();
      $this->barrios = collect();
      $this->tiposFormatoDireccion = TipoFormatoDireccion::orderBy('nombre','ASC')->get();

      if(auth()->user() && auth()->user()->sede_id)
      {
        $sede = Sede::find(auth()->user()->sede_id);
        $this->paisSelect= $sede->pais_id ? $sede->pais_id : '';
        $this->ciudadSelect= $sede->municipio_id ? $sede->municipio_id : '';

      }else{
        $iglesia= Iglesia::find(1);
        $this->paisSelect= $iglesia->pais_id ? $iglesia->pais_id : '';
        $this->ciudadSelect= $iglesia->municipio_id ? $iglesia->municipio_id : '';
      }

      if($this->modulo == 'usuarios')
      {
        $this->classDireccion = $this->formulario->obligatorio_direccion;
        $this->obligatorioDireccion = $this->formulario->obligatorio_direccion;
        $this->labelDireccion = $this->formulario->class_direccion;

        if($this->usuario)
        {
          $this->otroBarrio = $this->usuario->barrio_auxiliar;
          $this->direccion = $this->usuario->direccion;
          $this->barrioSelect = $this->usuario->barrio_id;
          $this->respuesta = $this->usuario->primer_nombre;
        }
      }elseif($this->modulo == 'grupos')
      {
        $configuracion = Configuracion::find(1);
        $this->obligatorioDireccion = $configuracion->direccion_grupo_obligatorio;
        $this->labelDireccion = $configuracion->label_direccion_grupo;

        if($this->grupo)
        {
          $this->otroBarrio = $this->grupo->barrio_auxiliar;
          $this->direccion = $this->grupo->direccion;
          $this->barrioSelect = $this->grupo->barrio_id;
        }
      }elseif($this->modulo == 'sedes')
      {
        $configuracion = Configuracion::find(1);
        $this->classDireccion = 'col-12 col-md-6';
        $this->obligatorioDireccion = false;
        $this->labelDireccion = $configuracion->label_direccion_grupo;

        if($this->sede)
        {
          $this->otroBarrio = $this->sede->barrio_auxiliar;
          $this->direccion = $this->sede->direccion;
          $this->barrioSelect = $this->sede->barrio_id;
        }
      }

    }

    public function hydrate()
    {
      $this->dispatch('render-select2');
    }

    public function updatedPaisSelect()
    {
      $this->ciudades = $this->paisSelect
      ? Region::where('pais_id', $this->paisSelect)
      ->leftJoin('departamentos','regiones.id','=','departamentos.region_id')
      ->leftJoin('municipios','departamentos.id','=','municipios.departamento_id')
      ->selectRaw("CONCAT(municipios.nombre,', ',departamentos.nombre) as nombre, municipios.id")
      ->orderBy('nombre','ASC')
      ->get()
      : collect();

      $this->ciudadSelect = '';
      $this->barrioSelect = '';
      $this->hydrate();
    }

    public function updatedCiudadSelect()
    {
      $this->barrios = $this->ciudadSelect ? Localidad::where('municipio_id', $this->ciudadSelect)
      ->whereNotNull('barrios.id')
      ->leftJoin('barrios','localidades.id','=','barrios.localidad_id')
      ->selectRaw("CONCAT(barrios.nombre,', ',localidades.nombre) as nombre, barrios.id")
      ->orderBy('nombre','ASC')
      ->get()
      : collect();

      $this->barrioSelect = '';
      $this->hydrate();

    }

    public function addDireccion()
    {
      $this->direccion= '';

      $this->direccionParte1 !=''
      ? $this->direccion.= ' '.$this->direccionParte1
      : '';

      $this->direccionParte2 !=''
      ? $this->direccion.= ' '.$this->direccionParte2
      : '';

      $this->direccionParte3 !=''
      ? $this->direccion.= ' N° '.$this->direccionParte3
      : '';

      $this->direccionParte4 !=''
      ? $this->direccion.= ' '.$this->direccionParte4
      : '';

      $this->direccionParte5 !=''
      ? $this->direccion.= ' '.$this->direccionParte5
      : '';

      if($this->barrioSelect !='')
      {
        $this->barrioSelect == 'otro'
        ?  $this->direccion.= ', '.$this->otroBarrio
        : $this->direccion.= ', '.Barrio::find($this->barrioSelect)->nombre;
      }

      $this->ciudadSelect
      ?  $this->direccion.= ', '.Municipio::find($this->ciudadSelect)->nombre
      : '';
    }

    public function btnEliminar()
    {
      $this->otroBarrio = '';
      $this->direccion = '';
      $this->barrioSelect = '';
    }

    public function render()
    {
      $this->paises = Pais::leftJoin('continentes','paises.continente_id','=','continentes.id')
      ->selectRaw("CONCAT(paises.nombre,', ',continentes.nombre) as nombre, paises.id")
      ->orderBy('nombre','ASC')
      ->get();

      $this->ciudades = $this->paisSelect
      ? Region::where('pais_id', $this->paisSelect)
      ->leftJoin('departamentos','regiones.id','=','departamentos.region_id')
      ->leftJoin('municipios','departamentos.id','=','municipios.departamento_id')
      ->selectRaw("CONCAT(municipios.nombre,', ',departamentos.nombre, ', ', regiones.nombre) as nombre, municipios.id")
      ->orderBy('nombre','ASC')
      ->get()
      : collect();

      $this->barrios = $this->ciudadSelect ? Localidad::where('municipio_id', $this->ciudadSelect)
      ->whereNotNull('barrios.id')
      ->leftJoin('barrios','localidades.id','=','barrios.localidad_id')
      ->selectRaw("CONCAT(barrios.nombre,', ',localidades.nombre) as nombre, barrios.id")
      ->orderBy('nombre','ASC')
      ->get()
      : collect();

      return view('livewire.generales.direccion-con-lista-geografica');
    }
}
