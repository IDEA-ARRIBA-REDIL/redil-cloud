<?php

namespace App\Livewire\ReporteGrupos;

use App\Models\Ingreso;
use App\Models\Moneda;
use App\Models\MotivoDesaprobacionReporteGrupo;
use App\Models\Ofrenda;
use App\Models\ReporteGrupo;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;

use Carbon\Carbon;

class GestionarAprobacionDesaprobacionDeReportes extends Component
{
    public $reporte;
    public $ofrendasGenericas = []; // Este es el que recorrere en la vista
    public $ofrendasGenericasExistentes = []; // Este es el que recorrere en la vista
    public $tiposOfrendasGenericas = [];

    public $ofrendasEspecificas = [];
    public $ofrendasEspecificasExistentes = []; // Este es el que recorrere en la vista

    public $moneda = [];
    public $motivos = [];
    public $seAprobo = 'si';
    public $tipoGestion = '';

    public $x;
    // formulario
    public $motivo, $descripcion;
    public $inputsOfrendasGenericas = [];
    public $inputsOfrendasEspecificas = [];

    public $ofrendas = [];

    public function mount()
    {
      $this->motivos = MotivoDesaprobacionReporteGrupo::orderBy('nombre', 'asc')->get();
      $this->moneda = Moneda::where('default', true)->first();
    }


    public function render()
    {

      return view('livewire.reporte-grupos.gestionar-aprobacion-desaprobacion-de-reportes');
    }


    #[On('abrirSupervisarReporte')]
    public function abrirSupervisarReporte( $reporteId , $tipoGestion )
    {
      $this->motivo= '';
      $this->descripcion= '';

      if ($tipoGestion == 'corregir')
        $this->seAprobo = 'no';
      else
        $this->seAprobo = 'si';

      $this->reporte = ReporteGrupo::find($reporteId);

      $this->motivo= $this->reporte->movito_desaprobacion_id;
      $this->descripcion= $this->reporte->observacion_desaprobacion;

      $this->tipoGestion = $tipoGestion;

      // 1. Obtener todos los tipos de ofrenda genéricos para el tipo de grupo del reporte
      $this->tiposOfrendasGenericas = $this->reporte->grupo->tipoGrupo->tiposOfrendas()
      ->where('generica', true)
      ->get();

      // 2. Cargar las ofrendas existentes del reporte actual para una búsqueda eficiente
      $this->ofrendasGenericasExistentes = $this->reporte->ofrendas()
      ->whereHas('tipoOfrenda', function ($query) {
          $query->where('generica', true);
      })
      ->get()
      ->keyBy('tipo_ofrenda_id'); // Clave por tipo_ofrenda_id para fácil acceso

      // 3. Preparar los datos para la vista
      $this->ofrendasGenericas = [];
      $this->inputsOfrendasGenericas = [];
      foreach ($this->tiposOfrendasGenericas as $tipoOfrenda) {
          $ofrendaTemp = $this->ofrendasGenericasExistentes->get($tipoOfrenda->id);

          $this->ofrendasGenericas[] = [
              'id_ofrenda_existente' => $ofrendaTemp ? $ofrendaTemp->id : null, // ID de la ofrenda si existe
              'tipo_ofrenda_id' => $tipoOfrenda->id, // Usaremos el ID del tipo de ofrenda para el input
              'nombre' => $tipoOfrenda->nombre,
              'tipoOfrenda' => $tipoOfrenda, // Objeto TipoOfrenda completo
              'valor' => $ofrendaTemp ? $ofrendaTemp->valor : 0, // Valor de la ofrenda existente o 0
              'valor_real' => $ofrendaTemp ? $ofrendaTemp->valor_real : 0, // Valor real o 0
          ];

          // Inicializa el valor del input para wire:model
          if (!isset($this->inputsOfrendasGenericas[$tipoOfrenda->id])) {
              $this->inputsOfrendasGenericas[$tipoOfrenda->id] = [
                  'valor' => old('ofrendaGenererica-'.$tipoOfrenda->id, ($ofrendaTemp ? $ofrendaTemp->valor_real : 0))
              ];
          }
      }




      $this->ofrendasEspecificasExistentes = $this->reporte->ofrendas()
      ->whereHas('tipoOfrenda', function ($query) {
        $query->where('generica', false);
      })
      ->orderBy('user_id', 'desc')
      ->select('valor','tipo_ofrenda_id','user_id','valor_real', 'ofrendas.id')
      ->get();

      $this->ofrendasEspecificas = [];
      $this->inputsOfrendasEspecificas = [];
      foreach ($this->ofrendasEspecificasExistentes as $ofrendaTemp)
      {
        $this->ofrendasEspecificas[] = [
          'id_ofrenda_existente' => $ofrendaTemp ? $ofrendaTemp->id : null, // ID de la ofrenda si existe
          'tipo_ofrenda_id' => $ofrendaTemp->tipo_ofrenda_id, // Usaremos el ID del tipo de ofrenda para el input
          'dador' => $ofrendaTemp->usuario->nombre(3),
          'nombre' => $ofrendaTemp->tipoOfrenda->nombre,
          'tipoOfrenda' => $ofrendaTemp->tipoOfrenda, // Objeto TipoOfrenda completo
          'valor' => $ofrendaTemp ? $ofrendaTemp->valor : 0, // Valor de la ofrenda existente o 0
          'valor_real' => $ofrendaTemp ? $ofrendaTemp->valor_real : 0, // Valor real o 0
        ];

        // Inicializa el valor del input para wire:model
        if (!isset($this->inputsOfrendasEspecificas[$ofrendaTemp->id])) {
          $this->inputsOfrendasEspecificas[$ofrendaTemp->id] = [
              'valor' => old('ofrendaEspecifica-'.$ofrendaTemp->id, ($ofrendaTemp ? $ofrendaTemp->valor_real : 0))
          ];
        }
      }



      $this->dispatch('abrirModal', nombreModal: 'modalRevisionReporte');


    }

    public function copiarValorGenerico($tipoOfrendaId, $valorParaCopiar)
    {
        // Valida que el tipoOfrendaId exista como clave para evitar errores
        if (array_key_exists($tipoOfrendaId, $this->inputsOfrendasGenericas)) {
            $this->inputsOfrendasGenericas[$tipoOfrendaId]['valor'] = $valorParaCopiar;
        } else {
            // Si la clave no existe (lo cual sería raro si mount() está bien), la crea.
            $this->inputsOfrendasGenericas[$tipoOfrendaId] = ['valor' => $valorParaCopiar];
        }
    }

    public function copiarValorEspecifico($ofrendaId, $valorParaCopiar)
    {
        // Valida que el tipoOfrendaId exista como clave para evitar errores
        if (array_key_exists($ofrendaId, $this->inputsOfrendasEspecificas)) {
            $this->inputsOfrendasEspecificas[$ofrendaId]['valor'] = $valorParaCopiar;
        } else {
            // Si la clave no existe (lo cual sería raro si mount() está bien), la crea.
            $this->inputsOfrendasEspecificas[$ofrendaId] = ['valor' => $valorParaCopiar];
        }
    }

    /**
     * Calcula la suma total de los valores reales ingresados.
     * Esta es nuestra propiedad computada.
     */
    #[Computed]
    public function totalValorReal()
    {
        $total = 0;
        if (is_array($this->inputsOfrendasGenericas)) {
            foreach ($this->inputsOfrendasGenericas as $inputData) {
                // Asegurarse de que 'valor' existe y es numérico
                if (isset($inputData['valor']) && is_numeric($inputData['valor'])) {
                    $total += (float) $inputData['valor'];
                }
            }
        }

        if (is_array($this->inputsOfrendasEspecificas)) {
          foreach ($this->inputsOfrendasEspecificas as $inputData) {
              // Asegurarse de que 'valor' existe y es numérico
              if (isset($inputData['valor']) && is_numeric($inputData['valor'])) {
                  $total += (float) $inputData['valor'];
              }
          }
        }

        return $total;
    }

    public function submitFormulario()
    {
      $rules = [];

      if($this->seAprobo == 'no')
      $rules['motivo'] = 'required';

      if($rules)
      $this->validate($rules);


      if(($this->seAprobo == 'no' && $this->reporte->aprobado === false) || ($this->seAprobo == 'si' && $this->reporte->aprobado === true))
      {
        return redirect(request()->header('Referer'))->with('info', '¡Ups! el reporte N° '.$this->reporte->id.' ya habia sido gestionado, por favor revisalo nuevamente.');
      }


      if ($this->seAprobo == 'si')
      {

        // Actualizo el reporte
        $this->reporte->aprobado = TRUE;
        $this->reporte->autor_aprobacion = auth()->user()->id;
        $this->reporte->fecha_aprobacion= Carbon::now()->format("Y-m-d h:i:s");
        $this->reporte->movito_desaprobacion_id = null;
        $this->reporte->observacion_desaprobacion = '';
        $this->reporte->save();

        // actualizo las ofrendas genericas existentes
        foreach ($this->ofrendasGenericasExistentes as $ofrenda)
        {
          $ofrenda->valor_real=$ofrenda->valor;
          $ofrenda->save();

          // actualizo los ingresos genericos existentes
          $ingreso = Ingreso::where('ofrenda_id',$ofrenda->id)->first();
          if($ingreso)
          {
            $ingreso->valor_real = $ofrenda->valor;
            $ingreso->save();
          }
        }

        // actualizo las ofrendas especificas existentes
        foreach ($this->ofrendasEspecificasExistentes as $ofrenda)
        {
          $ofrenda->valor_real=$ofrenda->valor;
          $ofrenda->save();

          // actualizo los ingresos genericos existentes
          $ingreso = Ingreso::where('ofrenda_id',$ofrenda->id)->first();
          if($ingreso)
          {
            $ingreso->valor_real = $ofrenda->valor;
            $ingreso->save();
          }
        }

        return redirect(request()->header('Referer'))->with('success', 'El reporte N° '.$this->reporte->id.' fue aprobado con éxito.');

      }else{

        // Guardo el motivo y la descripcion
        $this->reporte->aprobado = FALSE;
        $this->reporte->autor_aprobacion = auth()->user()->id;
        $this->reporte->fecha_aprobacion= Carbon::now()->format("Y-m-d h:i:s");
        $this->reporte->movito_desaprobacion_id = $this->motivo;
        $this->reporte->observacion_desaprobacion = $this->descripcion;
        $this->reporte->save();

        foreach ($this->tiposOfrendasGenericas as $tipoOfrenda)
        {
            $valorRealEditado = $this->inputsOfrendasGenericas[$tipoOfrenda->id]['valor'] ?? 0;

            // Buscar si ya existe una ofrenda para este tipo en el reporte
            $ofrenda = $this->ofrendasGenericasExistentes->get($tipoOfrenda->id);

            if ($ofrenda) {
                // La ofrenda ya existe, actualizarla
                $ofrenda->valor_real = $valorRealEditado;
                $ofrenda->save();

                // si el ingreso ya existe, actualizara
                $ingreso = Ingreso::where('ofrenda_id',$ofrenda->id)->first();
                if($ingreso)
                {
                  $ingreso->valor_real = $valorRealEditado;
                  $ingreso->save();
                }
            } else {

                // La ofrenda no existe, hay que crearla si el valor es mayor a 0
                if($valorRealEditado > 0)
                {
                  $ofrendaGenericaNueva= new Ofrenda;
                  $ofrendaGenericaNueva->tipo_ofrenda_id = $tipoOfrenda->id;
                  $ofrendaGenericaNueva->valor = 0;
                  $ofrendaGenericaNueva->valor_real = $valorRealEditado;
                  $ofrendaGenericaNueva->fecha = $this->reporte->fecha;
                  $ofrendaGenericaNueva->ingresada_por = 1; // Por grupos
                  $ofrendaGenericaNueva->moneda_id = $this->moneda->id;
                  $ofrendaGenericaNueva->observacion = 'Registro ofrenda de ofrenda por '.$this->reporte->motivoDesaprobacion->nombre;
                  $ofrendaGenericaNueva->save();

                  //guardo en la tabla pivot
                  $this->reporte->ofrendas()->attach($ofrendaGenericaNueva->id);

                  // creo el ingreso
                  $ingreso = new Ingreso;
                  $ingreso->fecha = $this->reporte->fecha;
                  $ingreso->nombre = 'Ingreso como '.$ofrendaGenericaNueva->tipoOfrenda->nombre.' reporte ofrenda general';
                  $ingreso->identificacion = 'Grupo: '.$this->reporte->grupo->nombre;
                  $ingreso->tipo_identificacion = 'Grupo: '.$this->reporte->grupo->nombre;
                  $ingreso->telefono = 'Grupo: '.$this->reporte->grupo->nombre;
                  $ingreso->direccion = 'Grupo: '.$this->reporte->grupo->nombre;
                  $ingreso->valor = 0;
                  $ingreso->valor_real = $valorRealEditado;
                  $ingreso->descripcion ='Registro ofrenda general reporte grupo '.$this->reporte->id.' - por '.$this->reporte->motivoDesaprobacion->nombre;
                  $ingreso->tipo_ofrenda_id = $ofrendaGenericaNueva->tipo_ofrenda_id;
                  $ingreso->caja_finanzas_id = 1;
                  $ingreso->sede_id = $this->reporte->grupo->sede_id;
                  $ingreso->user_id = 0;
                  $ingreso->ofrenda_id = $ofrendaGenericaNueva->id;
                  $ingreso->moneda_id = $this->moneda->id;
                  $ingreso->save();
                }
            }
        }

        foreach ($this->ofrendasEspecificasExistentes as $ofrenda)
        {
          $valorRealEditado = $this->inputsOfrendasEspecificas[$ofrenda->id]['valor'] ?? 0;

          // La ofrenda ya existe, actualizarla
          $ofrenda->valor_real = $valorRealEditado;
          $ofrenda->save();

          // si el ingreso ya existe, actualizara
          $ingreso = Ingreso::where('ofrenda_id',$ofrenda->id)->first();
          if($ingreso)
          {
            $ingreso->valor_real = $valorRealEditado;
            $ingreso->save();
          }
        }
        return redirect(request()->header('Referer'))->with('success', 'El reporte N° '.$this->reporte->id.' fue corregido con éxito.');
      }
    }



}
