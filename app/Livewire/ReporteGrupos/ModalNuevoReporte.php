<?php

namespace App\Livewire\ReporteGrupos;

use App\Models\Configuracion;
use App\Models\Grupo;
use App\Models\MotivoNoReporteGrupo;
use App\Models\ReporteGrupo;
use Livewire\Component;
use Livewire\Attributes\On;

use Carbon\Carbon;

class ModalNuevoReporte extends Component
{
    public  $motivosNoReporte, $x;
    public $grupoId = 0;

    public $configuracion, $rolActivo, $grupo, $tipoGrupo;

    public $fechaAutomatica = false;
    // campos del formulario
    public $seRealizo = 'si'; // Valor inicial, puede ser 'si' o 'no'
    public $fecha;
    public $tema;
    public $motivo;
    public $descripcionAdicionalMotivo;
    public $requiereDescripcionAdicional = false;


    #[On('abrirModalNuevoReporte')]
    public function abrirModalNuevoReporte( $fechaAutomatica, $grupoId )
    {
      $this->fechaAutomatica = $fechaAutomatica;
      $this->grupoId = $grupoId;
      $this->grupo = Grupo::find($grupoId);
      $this->tipoGrupo = $this->grupo->tipoGrupo;

      $diaReunion = null;
      if(!$this->fechaAutomatica && $this->configuracion->reportar_grupo_cualquier_dia == false)
      {
        $diaReunion = $this->grupo->dia > 0 ? $this->grupo->dia-1 : $this->grupo->dia; // 0 domingo y 6 sabado
      }

      $this->dispatch('abrirModal', nombreModal: 'modalNuevoReporte', diaReunion: $diaReunion);
    }


    public function mount()
    {
      $this->motivosNoReporte = MotivoNoReporteGrupo::orderBy('nombre', 'asc')->get();
      $this->configuracion = Configuracion::find(1);
      $this->rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    }

    public function updatedMotivo($value)
    {
        $this->requiereDescripcionAdicional = false;
        if($value)
        {
            $motivoSeleccionado = MotivoNoReporteGrupo::find($value);
            if($motivoSeleccionado && $motivoSeleccionado->descripcion_adicional)
            {
                $this->requiereDescripcionAdicional = true;
            }
        }
    }


    public function submitFormulario()
    {
      $rules = [];
      if ($this->seRealizo == 'si')
      $rules['tema'] = 'required';

      if(!$this->fechaAutomatica)
      $rules['fecha'] = 'required|date|before_or_equal:today';

      if($this->seRealizo == 'no')
      {
          $rules['motivo'] = 'required';
          if($this->requiereDescripcionAdicional)
          {
              $rules['descripcionAdicionalMotivo'] = 'required';
          }
      }

      if($rules)
      $this->validate($rules);

      $crearReporte = true;

      // Aquí obtengo la fecha del reporte
      if( $this->tipoGrupo->cantidad_maxima_reportes_semana == 1 && $this->configuracion->reportar_grupo_cualquier_dia == false)
      {
        if($this->rolActivo->hasPermissionTo('reportes_grupos.privilegio_reportar_grupo_cualquier_fecha'))
        {
          $fechaReporte = $this->fecha;
        }else {

          // 1: Domingo, 2: Lunes, ... 7: Sábado
          $diaGrupoUser = $this->grupo->dia;
          $diaGrupoCarbon = $diaGrupoUser - 1; // 0=Domingo, 6=Sábado
          // Calculamos el offset para startOfWeek(MONDAY)
          // Si es Lunes (2 -> 1): Shift 0.
          // Si es Domingo (1 -> 0): Shift 6.
          $daysToAdd = ($diaGrupoCarbon + 6) % 7;

          $fechaReporte = Carbon::now()->startOfWeek(Carbon::MONDAY)->addDays($daysToAdd)->format('Y-m-d');
        }

        // comparo el dia del reporte con el dia en que se hace el grupo
        $fechaCarbonReporte = Carbon::parse($fechaReporte);
        // Validamos que el día de la semana coincida
        // dayOfWeekIso returns 1 (Mon) to 7 (Sun)
        // grupo->dia: 1 (Sun), 2 (Mon)...
        // Map Iso to Group: Mon(1)->2, Sun(7)->1.
        
        $diaIso = $fechaCarbonReporte->dayOfWeekIso; // 1=Mon, 7=Sun
        $diaGrupoEsperado = ($diaIso % 7) + 1; // 1->2, 7->1
        
        if ($diaGrupoEsperado != $this->grupo->dia) {
          $this->addError('fecha', "La fecha no corresponde al día de reunión de grupo");
          $crearReporte = false;
        }

      }else{
        $fechaReporte = $this->fecha;
      }

      // Con este codigo se determina cuantos reportes se ha realizado en la semana y si excede el maximo permitido por tipo de grupo
      $fechaCarbon = Carbon::parse($fechaReporte); // Convertimos la fecha a un objeto Carbon
      $fechaRangoInferior = $fechaCarbon->copy()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
      $fechaRangoSuperior = $fechaCarbon->copy()->endOfWeek(Carbon::SUNDAY)->format('Y-m-d');

      $cantidadReporteSemanales = ReporteGrupo::where('grupo_id', $this->grupo->id)
          ->whereDate('fecha', '>=', $fechaRangoInferior)
          ->whereDate('fecha', '<=', $fechaRangoSuperior)
          ->select('id')
          ->count();

        $this->x = $cantidadReporteSemanales;
      if ($cantidadReporteSemanales >= $this->tipoGrupo->cantidad_maxima_reportes_semana) {
          $this->addError('errorGeneral', "Excedió máximo de reporte permitido por semana");
          $crearReporte = false;
      }

      // COMPARACIÓN SI YA EXÍSTE UN REPORTE EN ESE DÍA
      $reporteActual = ReporteGrupo::where('fecha', '=', $fechaReporte)
      ->where('grupo_id', '=', $this->grupo->id)
      ->first();

      if(isset($reporteActual))
      {
        $this->addError('errorGeneral', "No es posible crear el reporte, debido a que ya existe uno en esta fecha: ".$fechaReporte);
        $crearReporte = false;
      }

      // AQUI VOY A VALIDAR EL RANGO SEGUN LA CONFIGURACION
      if( !$this->rolActivo->hasPermissionTo('reportes_grupos.privilegio_reportar_grupo_cualquier_fecha') )
      {
        if (isset($this->configuracion->dias_plazo_reporte_grupo))
        {
          $rangoDias = $this->configuracion->dias_plazo_reporte_grupo;

          $fechaMax = Carbon::now()->format('Y-m-d'); // Obtiene la fecha actual con Carbon y la formatea
          $fechaMin = Carbon::now()->subDays($rangoDias)->format('Y-m-d'); // Resta los días de plazo y formatea

          if (Carbon::parse($fechaReporte)->isBefore($fechaMin) || Carbon::parse($fechaReporte)->isAfter($fechaMax)) {
            $this->addError('errorGeneral', "No es posible crear el reporte, esta fuera del rango de fechas permitidas");
            $crearReporte = false;
          }
        }else{

          //Si el campo dias_plazo_reporte_grupo no esta lleno, es porque se va a manejar por dia de corte, es decir que el campo dia_corte_reportes_grupos
          if (isset($this->configuracion->dia_corte_reportes_grupos))
          {
            $diaCorteUser = $this->configuracion->dia_corte_reportes_grupos; // 1=Domingo, 7=Sábado
            $diaCorteCarbon = $diaCorteUser - 1;
            $daysToAddCorte = ($diaCorteCarbon + 6) % 7;
            
            $fechaHoy = Carbon::now()->format('Y-m-d');
            
            // Calculamos la fecha de corte para la semana del reporte
            /* 
              Si la semana es Lunes-Domingo.
              La ventana es desde el Lunes de esa semana hasta el día de corte de esa semana.
            */
            $fechaReporteCarbon = Carbon::parse($fechaReporte);
            $inicioSemanaReporte = $fechaReporteCarbon->copy()->startOfWeek(Carbon::MONDAY);
            $fechaCorteSemana = $inicioSemanaReporte->copy()->addDays($daysToAddCorte);

            // Validar:
            // 1. Que no sea posterior al día de corte (ej. Hoy es Domingo, corte era Sábado).
            // 2. Que no sea anterior al inicio de la semana (ej. reportar antes de tiempo? - esto se cubre con la fecha del reporte vs hoy generalmente)
            
            // Si HOY es mayor que la fecha de corte limite, ya no se puede reportar para esa semana.
            if ($fechaHoy > $fechaCorteSemana->format('Y-m-d')) {
                 $this->addError('errorGeneral', "No es posible crear el reporte, el plazo venció el " . $fechaCorteSemana->locale('es')->dayName);
                 $crearReporte = false;
            }
          }
        }
      }


      if ($this->getErrorBag()->isNotEmpty()) {
        return; // Detiene la ejecución si hay algún error (estándar o personalizado)
      }

      if($this->rolActivo->hasPermissionTo('reportes_grupos.privilegio_reportar_grupo_cualquier_fecha') || $crearReporte==true)
      {

        $reporte = new ReporteGrupo;
        $reporte->fecha = $fechaReporte;
        $reporte->invitados = 0;
        $reporte->grupo_id = $this->grupo->id;
        $reporte->autor_creacion = auth()->user()->id;
        $reporte->informacion_del_grupo= Grupo::select('nombre', 'codigo', 'dia', 'tipo_grupo_id', 'telefono', 'direccion')->find($this->grupo->id);
        $arrayIdsGruposAscendentes =  $this->grupo->gruposAscendentes('array');
        $arrayIdsGruposAscendentes[] = $this->grupo->id;
        $reporte->ids_grupos_ascendentes = $arrayIdsGruposAscendentes;

        if($this->seRealizo == 'no')
        {
          $reporte->tema="No realizado";
          $reporte->no_reporte=TRUE;
          $reporte->finalizado=TRUE;
          $reporte->aprobado=TRUE;
          $reporte->motivo_no_reporte_grupo_id = $this->motivo;
          if($this->requiereDescripcionAdicional)
          {
              $reporte->descripcion_adicional_motivo = $this->descripcionAdicionalMotivo;
          }
          $reporte->save();

          return redirect()->route('reporteGrupo.mensajeExitoso', $reporte);
        }else{
          $reporte->tema = $this->tema;
          $reporte->no_reporte=FALSE;
          $reporte->finalizado=FALSE;
          $reporte->aprobado=NULL;

          if(!$this->configuracion->tiene_sistema_aprobacion_de_reporte)
          {
            $reporte->aprobado=TRUE;
          }

          $reporte->save();


          return redirect()->route('reporteGrupo.asistencia', $reporte);
        }
      }





      $this->x = "guardando ";
    }

    public function render()
    {
        return view('livewire.reporte-grupos.modal-nuevo-reporte');
    }
}
