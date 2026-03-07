<?php

namespace App\Livewire\ReporteReuniones;

use App\Helpers\Helpers;
use App\Models\Configuracion;
use App\Models\Ingreso;
use App\Models\Moneda;
use App\Models\Ofrenda;
use App\Models\ReporteReunion;
use App\Models\Reunion;
use App\Models\TipoOfrenda;
use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\DB;

class ResumenFinanciero extends Component
{
  public $reporteReunion;
  public $reunion;
  public $ofrendasGenericas,
    $ofrendasNoGenericas;
  public $configuracion;
  public $sumatoria;
  public $x;
  public $y;
  public $prueba;
  public $total;
  public $data;

  public $tipoModal;
  public $valorModal;
  public $ofrendaIdSeleccionada;
  public $tiposOfrendas = [];
  public $listaTipoOfrendas;
  public $buscar = '';
  public $moneda;
  public $ofrendaModalGenerica;
  public $ofrendaModalEspecifica;

  public $noMasPersonas = false;
  public $cantidadPorCarga = 3; // Cuántas personas cargar cada vez
  public $paginaActual = 1; // Para rastrear la "página" actual para la carga manual
  public $personas;

  public $tipoOfrendaIdSeleccionada;
  public $usuarioSeleccionado;
  public $ofrendasPorPersona = []; // tiene toda la data de las ofrendas especificas por persona
  public $idsTiposOfrendasEspecificas;
  // ofrendas especificas
  public $inputsTemporalOfrendasEspecificasUsuario = [];
  public $ofrendasTemporalEspecificasUsuario = [];

  public function mount(): void
  {
    $reunion = Reunion::withTrashed()->find($this->reporteReunion->reunion_id);
    $this->listaTipoOfrendas = $reunion->tiposOfrendas()
      ->where('generica', false)
      ->orderby('nombre')
      ->get();
    $this->tiposOfrendas = $reunion->tiposOfrendas()->orderBy("nombre")->get();
    $this->configuracion = Configuracion::find(1);
    $this->moneda = Moneda::where('default', TRUE)->first();

    $this->total = 0;
    $this->data = collect();

    $this->buscar();
    $this->inicializarOfrendas();

    $this->idsTiposOfrendasEspecificas = $this->reporteReunion->reunion->tiposOfrendas()
      ->where('generica', false)->get()->pluck('id')->toArray();

    // Inicializo la carga de personas
    $this->cargarPersonas();
    $this->x = $this->reporteReunion->ofrendas()->get();
    $this->y = Ingreso::get();
  }

  public function inicializarOfrendas()
  {
    $ofrendas = $this->reporteReunion->ofrendas()->get();
    $this->total = 0;
    $this->data = collect();
    foreach ($this->tiposOfrendas as $tipoOfrenda) {
      $sumatoria = $ofrendas->where('tipo_ofrenda_id', $tipoOfrenda->id)->sum('valor') ?? 0;
      $this->total += $sumatoria;

      $this->data->push((object)[
        'id' => $tipoOfrenda->id,
        'nombre' => $tipoOfrenda->nombre,
        'sumatoria' => $sumatoria,
        'generica' => $tipoOfrenda->generica,
      ]);
    }
  }

  public function cargarPersonas()
  {
    $query = User::query();

    if ($this->buscar) {
      $buscar = htmlspecialchars($this->buscar);
      $buscar = Helpers::sanearStringConEspacios($buscar);
      $buscar = str_replace(["'"], '', $buscar);
      $buscar_array = explode(' ', $buscar);

      $query->where(function ($q) use ($buscar_array) {
        foreach ($buscar_array as $palabra) {
          $q->whereRaw("LOWER(CONCAT_WS(' ', primer_nombre, segundo_nombre, primer_apellido, segundo_apellido)) LIKE LOWER(?)", ['%' . $palabra . '%'])
            ->orWhereRaw("LOWER(email) LIKE LOWER(?)", [$palabra . '%'])
            ->orWhereRaw("LOWER(identificacion) LIKE LOWER(?)", [$palabra . '%']);
        }
      });
    }

    $query->select(
      'users.id',
      'users.foto',
      'users.primer_nombre',
      'users.segundo_nombre',
      'users.primer_apellido',
      'users.segundo_apellido',
      'users.identificacion',
      'users.tipo_usuario_id'
    )->orderBy('users.id', 'desc');

    // Lógica de paginación/carga
    if ($this->paginaActual == 1) {
      $this->personas = $query->take($this->cantidadPorCarga)->get();
      if ($this->personas->count() < $this->cantidadPorCarga) {
        $this->noMasPersonas = true;
      } else {
        $this->noMasPersonas = false;
      }
    } else {
      $personasNuevas = $query
        ->skip(($this->paginaActual - 1) * $this->cantidadPorCarga)
        ->take($this->cantidadPorCarga)
        ->get();

      $this->personas = $this->personas->concat($personasNuevas);

      if ($personasNuevas->count() < $this->cantidadPorCarga) {
        $this->noMasPersonas = true;
      }
    }
  }

  #[On('buscar')]
  public function buscar()
  {
    $this->cargarPersonas();
  }

  #[On('loadMore')]
  public function loadMore()
  {
    $this->paginaActual++;
    $this->cargarPersonas();
  }

  public function updatedBuscar($value)
  {
    $this->paginaActual = 1; // Reinicia la paginación para la nueva búsqueda
    $this->noMasPersonas = false; // Permite cargar más si hay resultados
    $this->personas = collect(); // Limpia los resultados anteriores
    $this->cargarPersonas(); // Llama al método que aplica el filtro y carga los datos
  }

  public function abrirModalEdicion($tipoOfrendaId)
  {
    // tengo que traer de este reporte la ofrenda que tenga el tipo de ofrenda id
    $this->ofrendaModalGenerica = $this->reporteReunion->ofrendas()->where('tipo_ofrenda_id', $tipoOfrendaId)->first();
    $tipoOfrendaSeleccionada = TipoOfrenda::find($tipoOfrendaId);
    $this->tipoOfrendaIdSeleccionada = $tipoOfrendaId;

    $this->ofrendaModalGenerica ? $this->ofrendaModalGenerica->id : null;
    $this->tipoModal = $this->ofrendaModalGenerica ? $tipoOfrendaSeleccionada->nombre : '';
    $this->valorModal = $this->ofrendaModalGenerica ? $this->ofrendaModalGenerica->valor : 0;
    $this->dispatch('abrirModal', nombreModal: 'modalEdicionOfrenda');
  }

  public function abrirModalOfrendaEspecifica($personaId)
  {
    $this->ofrendaModalEspecifica = $this->reporteReunion->ofrendas()->where('user_id', $personaId)->get();
    $this->x = $this->reporteReunion->ofrendas()->where('user_id', $personaId)->get();
    $this->usuarioSeleccionado = User::select('id', 'primer_nombre', 'segundo_nombre', 'primer_apellido', 'segundo_apellido', 'tipo_identificacion_id', 'telefono_movil', 'direccion', 'identificacion')
      ->find($personaId);
    // Tengo que consultar las ofrendas que la personaId que en ese reporte todas las ofrendas que esa persona ha dado devuelve los idsdelostiposdeofrendas
    // Tambien tengo que consultar lostiposdeofrendasespecificas que tiene la reunion y luego los hago unique y los mergeo
    // Dentro del foreach voy a consultar la ofrenda del reporte de reunion de esa persona donde el tipo de ofrenda sea $tipoOfrenda->id
    $this->ofrendasPorPersona = $this->reporteReunion->ofrendas()->where('user_id', $personaId)->get();
    $tipoOfrendasEspecificasUsuarioReporteIds = $this->ofrendasPorPersona->pluck('tipo_ofrenda_id')->toArray();
    $idsUnicos = array_values(array_unique(array_merge($this->idsTiposOfrendasEspecificas, $tipoOfrendasEspecificasUsuarioReporteIds)));
    $tiposOfrenda = TipoOfrenda::whereIn('id', $idsUnicos)->get();

    $ofrendasUsuario = collect();
    $this->inputsTemporalOfrendasEspecificasUsuario = []; // Limpiar antes de llenar

    foreach ($tiposOfrenda as $tipoOfrenda) {
      $ofrendaTemp = $this->ofrendasPorPersona->firstWhere('tipo_ofrenda_id', $tipoOfrenda->id);
      $valorActual = $ofrendaTemp ? $ofrendaTemp->valor : 0.00;
      $ofrendaIdActual = $ofrendaTemp ? $ofrendaTemp->id : null;

      $ofrendasUsuario->push((object) [
        'ofrenda_id' => $ofrendaIdActual,
        'tipo_ofrenda_id' => $tipoOfrenda->id,
        'nombre' => $tipoOfrenda->nombre,
        'valor' => $valorActual
      ]);

      $this->inputsTemporalOfrendasEspecificasUsuario[$tipoOfrenda->id]['valor'] =  $valorActual;
      $this->inputsTemporalOfrendasEspecificasUsuario[$tipoOfrenda->id]['id'] = $ofrendaIdActual;
    }
    $this->ofrendasTemporalEspecificasUsuario = $ofrendasUsuario;
    $this->dispatch('abrirModal', nombreModal: 'modalOfrendaEspecifica');
  }
  // cuando vaya a guardar elimino el registro de ofrendas
  // se edita siempre y cuando el valor del input sea diferente a el de la base de datos
  // cuando no exista el registro y el input es mayor a cero se guarda de lo contrario no hace nada

  public function guardarCambios()
  {
    $this->validate([
      'valorModal' => 'required|numeric|min:0',
    ]);

    if ($this->ofrendaModalGenerica) {
      // // Si el valor es 0 y estamos editando una ofrenda que ya existe...
      if ($this->valorModal == 0) {
        $this->reporteReunion->ofrendas()->detach($this->ofrendaModalGenerica->id);
        $this->ofrendaModalGenerica->delete();

        $this->dispatch(
          'msn',
          msnIcono: 'success',
          msnTitulo: '¡Muy bien!',
          timer: 1500,
          msnTexto: 'Las ofrenda fue eliminada correctamente'
        );

        $ingreso = Ingreso::where('ofrenda_id', $this->ofrendaModalGenerica->id)->first();
        $ingreso->delete();

        $this->dispatch('cerrarModal', nombreModal: 'modalEdicionOfrenda');
      }

      if ($this->valorModal > 0) {
        $this->ofrendaModalGenerica->valor = $this->valorModal;
        $this->ofrendaModalGenerica->valor_real = $this->valorModal;
        $this->ofrendaModalGenerica->save();

        $ingreso = Ingreso::where('ofrenda_id', $this->ofrendaModalGenerica->id)->first();

        $ingreso->valor_real = $this->valorModal;
        $ingreso->valor = $this->ofrendaModalGenerica->valor;
        $ingreso->save();

        $this->dispatch(
          'msn',
          msnIcono: 'success',
          msnTitulo: '¡Muy bien!',
          timer: 1500,
          msnTexto: 'Las ofrenda fue editada con exito'
        );

        $this->dispatch('cerrarModal', nombreModal: 'modalEdicionOfrenda');
      }
    } else {
      if ($this->valorModal > 0) {
        $ofrenda = new Ofrenda;
        $ofrenda->tipo_ofrenda_id = $this->tipoOfrendaIdSeleccionada;
        $ofrenda->valor = $this->valorModal;
        $ofrenda->valor_real = $this->valorModal;
        $ofrenda->fecha = $this->reporteReunion->fecha;
        $ofrenda->ingresada_por = 0; //0 reuniones - 1 grupos - 2 otros
        $ofrenda->save();

        $this->reporteReunion->ofrendas()->attach($ofrenda->id);

        $ingreso = new Ingreso;
        $ingreso->fecha = $this->reporteReunion->fecha;
        $ingreso->nombre = 'Ingreso como ' . $ofrenda->tipoOfrenda->nombre . ' reporte reunión';
        $ingreso->identificacion = 'Reporte reunión: ' . $this->reporteReunion->reunion->nombre; // o el nombre de la reunion
        $ingreso->tipo_identificacion = 'Reporte reunión:' . $this->reporteReunion->reunion->nombre;
        $ingreso->telefono = 'Reporte reunión:' . $this->reporteReunion->reunion->nombre;
        $ingreso->direccion = 'Reporte reunión:' . $this->reporteReunion->reunion->nombre;
        $ingreso->valor = $ofrenda->valor;

        $ingreso->descripcion = 'Registro ofrenda general reporte reunión ' . $this->reporteReunion->id;
        $ingreso->tipo_ofrenda_id = $ofrenda->tipo_ofrenda_id;
        $ingreso->caja_finanzas_id = 1;
        $ingreso->sede_id = $this->reporteReunion->reunion->sede_id;
        $ingreso->user_id = 0;
        $ingreso->ofrenda_id = $ofrenda->id;
        $ingreso->moneda_id = $this->moneda->id;
        $ingreso->ingreso_por_reunion = true;
        $ingreso->save();

        $this->dispatch(
          'msn',
          msnIcono: 'success',
          msnTitulo: '¡Muy bien!',
          timer: 1500,
          msnTexto: 'Las ofrenda fueron creadas con exito'
        );

        $this->dispatch('cerrarModal', nombreModal: 'modalEdicionOfrenda');
      }
    }
  }

  public function guardarCambiosEspecificos()
  {
    $this->validate([
      'inputsTemporalOfrendasEspecificasUsuario.*.valor' => 'numeric|min:0',
    ], [
      'inputsTemporalOfrendasEspecificasUsuario.*.valor.numeric' => 'El valor debe de ser un número',
      'inputsTemporalOfrendasEspecificasUsuario.*.valor.min' => 'El valor debe ser mayor o igual a :min.',
    ]);
    // que si es igual el valor que tiene el input a
    // el valor que tiene en la base datos no hace nada
    foreach ($this->inputsTemporalOfrendasEspecificasUsuario as $tipoId => $datos) {
      $valor = $datos['valor'] ?? 0;

      $ofrendaTemporal = $this->ofrendasPorPersona->firstWhere('tipo_ofrenda_id', $tipoId);
      if ($ofrendaTemporal) {
        if ($valor == 0) {
          $this->reporteReunion->ofrendas()->detach($ofrendaTemporal->id);
          $ofrendaTemporal->delete();
        } elseif ($valor > 0 && ($valor != $ofrendaTemporal->valor)) {
          $ofrendaTemporal->valor = $valor;
          $ofrendaTemporal->valor_real = $valor;
          $ofrendaTemporal->save();

          $this->dispatch(
            'msn',
            msnIcono: 'success',
            msnTitulo: '¡Muy bien!',
            timer: 1500,
            msnTexto: 'Las ofrendas de ' . $this->usuarioSeleccionado->nombre(3) . ' fueron editadas con exito'
          );
        }
      } else {
        if ($valor > 0) {
          $ofrenda = new Ofrenda;
          $ofrenda->tipo_ofrenda_id = $tipoId;
          $ofrenda->valor = $valor;
          $ofrenda->user_id = $this->usuarioSeleccionado->id;
          $ofrenda->valor_real = $valor;
          $ofrenda->fecha = $this->reporteReunion->fecha;
          $ofrenda->ingresada_por = 0; //0 reuniones - 1 grupos - 2 otros
          $ofrenda->save();

          $this->reporteReunion->ofrendas()->attach($ofrenda->id);

          $ingreso = new Ingreso;
          $ingreso->fecha = $this->reporteReunion->fecha;
          $ingreso->nombre =  $this->usuarioSeleccionado->nombre(3);
          $ingreso->identificacion = $this->usuarioSeleccionado->identificacion; // o el nombre de la reunion
          $ingreso->tipo_identificacion = $this->usuarioSeleccionado->tipo_identificacion_id ?? 0;
          $ingreso->telefono = $this->usuarioSeleccionado->telefono_movil;
          $ingreso->direccion = $this->usuarioSeleccionado->direccion;
          $ingreso->valor = $ofrenda->valor;

          $ingreso->descripcion = '';
          $ingreso->tipo_ofrenda_id = $ofrenda->tipo_ofrenda_id;
          $ingreso->caja_finanzas_id = 1;
          $ingreso->sede_id = $this->reporteReunion->reunion->sede_id;
          $ingreso->user_id = $this->usuarioSeleccionado->id;
          $ingreso->ofrenda_id = $ofrenda->id;
          $ingreso->moneda_id = $this->moneda->id;
          $ingreso->ingreso_por_reunion = true;
          $ingreso->save();
          $this->dispatch(
            'msn',
            msnIcono: 'success',
            msnTitulo: '¡Muy bien!',
            timer: 1500,
            msnTexto: 'Las ofrendas de ' . $this->usuarioSeleccionado->nombre(3) . ' fueron creadas con exito'
          );
        }
      }
    }

    if (!$this->getErrorBag()->any()) {
      $this->dispatch('cerrarModal', nombreModal: 'modalOfrendaEspecifica');
    }
  }

  public function asistenciaQr($decodedText)
  {
    // 1. Validar que el QR corresponde a la reunión actual.
    //    Esto previene que alguien escanee un QR de otro evento.
    $reunionIdDesdeQr = (int) $decodedText;
    if ($reunionIdDesdeQr !== $this->reporteReunion->reunion_id) {
      $this->dispatch('msn', msnIcono: 'warning', msnTitulo: 'QR Incorrecto', msnTexto: 'Este código QR no pertenece a la reunión actual.');
      return;
    }

    // 2. Verificar si la asistencia ya fue registrada para este usuario en este reporte.
    $asistenciaExistente = $this->reporteReunion->usuarios()
      ->where('user_id', $this->usuarioSeleccionado->id)
      ->exists();

    if ($asistenciaExistente) {
      $this->dispatch('msn', msnIcono: 'info', msnTitulo: 'Asistencia Previa', msnTexto: 'Tu asistencia para esta reunión ya ha sido registrada.');
      return;
    }

    // 3. Registrar la asistencia.
    //    Usamos syncWithoutDetaching para agregar la relación en la tabla pivote de manera segura.
    $this->reporteReunion->usuarios()->syncWithoutDetaching([
      $this->usuarioSeleccionado->id => ['asistio' => true, 'created_at' => now(), 'updated_at' => now()]
    ]);

    // 4. Enviar mensaje de éxito.
    $this->dispatch('msn', msnIcono: 'success', msnTitulo: '¡Éxito!', msnTexto: 'Hola ' . $this->usuarioSeleccionado->primer_nombre . ', tu asistencia ha sido registrada correctamente.');
  }

  public function render()
  {
    $this->inicializarOfrendas();
    return view('livewire.reporte-reuniones.resumen-financiero', ['total' => $this->total]);
  }
}
